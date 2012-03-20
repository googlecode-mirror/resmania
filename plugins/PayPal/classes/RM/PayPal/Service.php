<?php
class RM_PayPal_Service
{
    /**
     * Contains the POST values for IPN
     * @var array
     */
    var $ipnData = array();

    /**
     * Holds the last error encountered
     * @var string
     */
    private $_lastError = '';

    /**
     * The paypal URL to use
     * @var stirng
     */
    private $_paypalURL;

    /**
     * Holds the IPN response from paypal
     * @var stirng
     */
    private $_ipnResponse = '';

    /**
     * Filename of the IPN log
     * @var string
     */
    private $_ipnLogFile;

    /**
     * Log IPN results to text file or not
     * todo:  this value need to have switch on/off on admin end
     * @var bool
     */
    private $_ipnLog = true;

    /**
     * Holds the fields to submit to paypal
     * @var array
     */
    private $_fields = array();

    /**
     * Real URL to Paypal for transactions
     * @var string
     */
    private static $_realURL = 'https://www.paypal.com/cgi-bin/webscr';

    /**
     * Sandbox URL to Paypal for making test transactions
     * @var string
     */
    private static $_sandboxURL ='https://www.sandbox.paypal.com/cgi-bin/webscr';

    /**
     * Public constructor
     */
    public function  __construct() {
        $this->_ipnLogFile = RM_Environment::getConnector()->getCorePath().DIRECTORY_SEPARATOR.'userdata'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'paypal_ipn_log.txt';
        $this->_addField('rm','2'); // Return method = POST
        $this->_addField('cmd','_xclick');

        $config = $this->_getConfig();
        if (!$config) return false; // fail if no config

        if ((int)$config['sandbox'] == 1) {
            $this->_paypalURL = self::$_sandboxURL;
        } else {
            $this->_paypalURL = self::$_realURL;
        }
    }

    /**
     * Creates Paypal invoice number from booking ref.
     *
     * @param  $bookingRef
     * @param bool $randomSalt - [optional][default=false] if true we will add some random value to voice number,
     * to allow multiple payment transaction for one reservation
     * @return string
     */
    public function createInvoiceNumber($bookingRef, $randomSalt = false)
    {
        if ($randomSalt) {
            $bookingRef .= '_'.time();
        }
        return 'RM-'.$bookingRef;
    }

    /**
     * Gets booking ref from the invoice number
     *
     * @param  $invoiceNumber
     * @return string
     */
    public function getBookingRef($invoiceNumber)
    {
        $bookingRefPotentiallyWithSalt = str_replace('RM-', '', $invoiceNumber);
        list($bookingRef, ) = explode('_', $bookingRefPotentiallyWithSalt);
        return $bookingRef;
    }

    /**
     * This reads the data passed from PayPal the passes this data back to PayPal
     * for verification.
     *
     * @return 	boolean success/falues = true/false
     */
    public function validateIPN() {
        // parse the paypal URL
        $urlparsed = parse_url($this->_paypalURL);

        // generate the post string from the _POST vars aswell as load the
        //_POST vars into an arry so we can play with them from the calling script.
        $postString = '';
        foreach ($_POST as $field => $value) {
            $this->ipnData[$field] = $value;
            $postString .= $field.'='.urlencode($value).'&';
        }
        $postString.="cmd=_notify-validate"; // append ipn command

        // open the connection to paypal
        $fp = fsockopen($urlparsed['host'], "80", $errnum, $errstr, 30);
        if(!$fp) {
            // Could not open the connection.  If loggin is on, the error message will be in the log.
            $this->_lastError = "fsockopen error no. $errnum: $errstr";
            $this->_logIPNResults(false);
            return false;
        }

        // Post the data back to paypal
        fputs($fp, "POST ".$urlparsed['path']." HTTP/1.1\r\n");
        fputs($fp, "Host: ".$urlparsed['host']."\r\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-length: ".strlen($postString)."\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $postString . "\r\n\r\n");

        // loop through the response from the server and append to variable
        while(!feof($fp)) {
            $this->_ipnResponse .= fgets($fp, 1024);
        }

        fclose($fp); // close connection

        if (eregi("VERIFIED", $this->_ipnResponse)) {
            // Valid IPN transaction.
            $this->_logIPNResults(true);
            return true;
        }

        // Invalid IPN transaction.  Check the log for details.
        $this->_lastError = 'IPN Validation Failed.';
        $this->_logIPNResults(false);
        return false;
    }

    /**
     * Return all post fields information for HTML form
     *
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * Returns full url to Paypal service
     *
     * @return stirng Return
     */
    public function getPaypalURL()
    {
        return $this->_paypalURL;
    }

    /**
     * get the paypal config
     *
     * @return   array
     */
    private function _getConfig()
    {
        $paypalObj = new RM_PayPal;
        $value = $paypalObj->getSettings()->toArray();
        return $value[0];
    }

    /**
     * Initialize internal state of the paypal class
     *
     * @param string $invoiceNumber
     * @param string $description - the text description for the payment
     * @param string $total - the total amount to be charged
     * @throw RM_Exception
     * @return void
     */
    public function initialize($invoiceNumber, $description, $total)
    {
        $config = $this->_getConfig();
        if (!$config) {
            //TODO: add language constant
            throw new RM_Exception('No PayPal config defined.');
        }

        $configModel = new RM_Config;
        $currencyISO = $configModel->getValue('rm_config_currency_iso');

        $this->_addField('business', $config['account']);        
        $this->_addField('notify_url', RM_Environment::getInstance()->getRouter()->_('PayPal_External', 'ipn'));
        $this->_addField('item_name', $description);
        $this->_addField('invoice', $invoiceNumber);
        $this->_addField('currency_code', $currencyISO);
        $this->_addField('amount', $total);
    }

    /**
     * Add the field data, this is what will be passed to PayPal
     *
     * @param  	string      $field      the feild name to be passed
     * @param  	string      $value      the value to be passed
     */
    private function _addField($field, $value) {
        $this->_fields["$field"] = $value;
    }

    /**
     * used to log results to the ipn log file so we can see whats happening
     *
     * @param boolean $success if this is a successful or fail log
     */
    private function _logIPNResults($success)
    {
        if (!$this->_ipnLog) return;  // is logging turned off?

        // Timestamp
        $text = '['.date('m/d/Y g:i A').'] - ';

        if ($logmessage != null){
             $text .= "\nResMania Plugin Response:\n ".$logmessage;
        } else {

            // Success or failure being logged?
            if ($success) {
                $text .= "SUCCESS!\n";
            } else {
                $text .= 'FAIL: '.$this->_lastError."\n";
            }

            // Log the POST variables
            $text .= "PayPal IPN Post Variables:\n";
            $IPNData = array();
            foreach ($this->ipnData as $key => $value) {
                $IPNData[] = "$key=$value";
            }
            $text .= implode(', ', $IPNData);

            // Log the response from the paypal server
            $text .= "\nPayPal IPN Response:\n ".$this->_ipnResponse;
        }
        // Write to log
        $fp = fopen($this->_ipnLogFile, 'a');
        fwrite($fp, $text . "\n\n");
        fclose($fp);  // close file
    }

    /*
     * This will return the contents of the PayPal Log File...
     */
    public function getLog(){
        if (!$this->_ipnLog) return;  // is logging turned off?
        $logdata = str_replace("\n","<br />", file_get_contents($this->_ipnLogFile));
        return $logdata;
    }
}
