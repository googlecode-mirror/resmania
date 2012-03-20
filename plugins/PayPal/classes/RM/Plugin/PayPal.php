<?php
/*
 * PayPal Payment Plugin Class
 */
class RM_Plugin_PayPal extends RM_Payments_Plugin
{    
    /**
     * Contains the POST values for IPN
     *
     * @deprecated @see RM_PayPal_Service
     * @var array
     */
    var $ipnData = array();

    /**
     * Holds the last error encountered
     *
     * @deprecated @see RM_PayPal_Service
     * @var string
     */
    private $_lastError = '';

    /**
     * The paypal URL to use
     *
     * @deprecated @see RM_PayPal_Service
     * @var stirng
     */
    private $_paypalURL;

    /**
     * Holds the IPN response from paypal
     *
     * @deprecated @see RM_PayPal_Service
     * @var stirng
     */
    private $_ipnResponse = '';

    /**
     * Filename of the IPN log
     *
     * @deprecated @see RM_PayPal_Service
     * @var string
     */
    private $_ipnLogFile;

    /**
     * Log IPN results to text file or not
     * todo:  this value need to have switch on/off on admin end
     *
     * @deprecated @see RM_PayPal_Service
     * @var bool
     */
    private $_ipnLog = true;

    /**
     * Holds the fields to submit to paypal
     *
     * @deprecated @see RM_PayPal_Service
     * @var array
     */
    private $_fields = array();

    /**
     * Real URL to Paypal for transactions
     *
     * @deprecated @see RM_PayPal_Service
     * @var string
     */
    private static $_realURL = 'https://www.paypal.com/cgi-bin/webscr';

    /**
     * Sandbox URL to Paypal for making test transactions
     *
     * @deprecated @see RM_PayPal_Service
     * @var string
     */
    private static $_sandboxURL ='https://www.sandbox.paypal.com/cgi-bin/webscr';

    /**
     * Public constructor
     */
    public function  __construct() {
        $this->name = 'PayPal';

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
     * Truncate all information from log file
     *
     * @throw RM_Exception
     * @return bool
     */
    public function clearLog()
    {
        if (is_file($this->_ipnLogFile) == false) {
            throw new RM_Exception('Log file:'.$this->_ipnLogFile.' does not exists.');
        }

        if (is_writeable($this->_ipnLogFile) == false) {
            throw new RM_Exception('Log file:'.$this->_ipnLogFile.' is not writable.');
        }

        $file = fopen($this->_ipnLogFile, 'w');
        if ($file == false){
            throw new RM_Exception('Log file:'.$this->_ipnLogFile.' could not be open by system using fopen(...,w).');
        }        
        fclose($file);
        return true;
    }

    /**
     * We don't need to present this plugins under admin tree menu 'Plugins', only on Config leaf
     * 
     * @return null
     */
//    this is commented out as we need to show the paypal option under the plugins
//    to show the log file, but I wanted to leave this example here how to show
//    hide the menu items.
    public function getNode(){
        $std = new stdClass;
        $std->id = $this->name.'_LogJson';
        $std->text = $this->getName();
        $std->leaf = 'true';
        $std->iconCls = 'RM_plugins_leaf_icon';
        return $std;
    }

    /**
     * Return all post fields information for HTML form
     *
     * @deprecated @see RM_PayPal_Service
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * Returns full url to Paypal service
     *
     * @deprecated @see RM_PayPal_Service
     * @return stirng Return 
     */
    public function getPaypalURL()
    {
        return $this->_paypalURL;
    }

    /**
     * Add the field data, this is what will be passed to PayPal
     *
     * @deprecated @see RM_PayPal_Service
     * @param  	string      $field      the feild name to be passed
     * @param  	string      $value      the value to be passed
     */
    private function _addField($field, $value) {
        $this->_fields["$field"] = $value;
    }    

    /**
     * This reads the data passed from PayPal the passes this data back to PayPal
     * for verification.
     *
     * @deprecated @see RM_PayPal_Service
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

    /*
     * This will return the contents of the PayPal Log File...
     * @deprecated @see RM_PayPal_Service
     */
    public function getLog(){
        if (!$this->_ipnLog) return;  // is logging turned off?
        $logdata = str_replace("\n","<br />", file_get_contents($this->_ipnLogFile));
        return $logdata;
    }

    /**
     * used to log results to the ipn log file so we can see whats happening
     *
     * @deprecated @see RM_PayPal_Service
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

    /**
     * Initialize internal state of the paypal class
     *
     * @param 	string      $description        the text description for the payment
     * @param   string      $bookingref         the ID or reference for this reservation
     * @param   string      $chargetotal        the total amount to be charged
     * @deprecated @see RM_PayPal_Service
     */
    function initialize($description, $bookingref, $chargetotal)
    {
        if (!$bookingref) return false; // fail if no booking reference
        if (!$chargetotal) return false; // fail if no total to charge.

        // make sure that a decimal . is passed not a comma or anything else.
        if (strpos($chargetotal, ",") > 0) {
            $chargetotal = str_replace(",", ".", $chargetotal);
        }

        $config = $this->_getConfig();
        if (!$config) return false; // fail if no config

        $configModel = new RM_config;
        $this->_addField('business', $config['account']);
        $this->_addField('return', RM_Environment::getInstance()->getRouter()->_('PayPal', 'success'));
        $this->_addField('cancel_return', RM_Environment::getInstance()->getRouter()->_('PayPal', 'cancel'));
        $this->_addField('notify_url', RM_Environment::getInstance()->getRouter()->_('PayPal', 'ipn'));
        $this->_addField('item_name', $description);
        $this->_addField('invoice', "RM-".$bookingref); // paypay does not accept a numeric value for this so the reference is preceeded with RM-
        $this->_addField('currency_code', $configModel->getValue('rm_config_currency_iso'));
        $this->_addField('amount', $chargetotal);
        
        return $this;
    }   

    public function beginTransaction($value, $reservationID, $description, $successUrl, $cancelUrl, $callbackClassName)
    {
        $payPalService = new RM_PayPal_Service();

        try {
            $payPalService->initialize(
                $payPalService->createInvoiceNumber($reservationID, true),
                $description,
                $value
            );
        } catch (RM_Exception $e) {
            RM_Log::toLog("PayPal transaction aborted: ".$e->getMessage());
            throw new RM_Transaction_Exception();
        }

        $fields = $payPalService->getFields();

        $request = RM_Environment::getConnector()->getRequestHTTP();
        $request->setControllerName('PayPal');
        $request->setActionName('form');
        $controller = new RM_User_PayPalController(
            $request,
            new Zend_Controller_Response_Http()
        );
        $controller->setFrontController(Zend_Controller_Front::getInstance());
        $controller->view->setScriptPath(
            RM_Environment::getConnector()->getRootPath().DIRECTORY_SEPARATOR.
            'RM'.DIRECTORY_SEPARATOR.
            'userdata'.DIRECTORY_SEPARATOR.
            'plugins'.DIRECTORY_SEPARATOR.
            'PayPal'.DIRECTORY_SEPARATOR.
            'views'.DIRECTORY_SEPARATOR.
            'user'.DIRECTORY_SEPARATOR.
            'scripts'.DIRECTORY_SEPARATOR.
            'PayPal'
        );

        $fields['custom'] = $callbackClassName;
        $fields['return'] = $successUrl;
        $fields['cancel_return'] = $cancelUrl;

        $controller->view->fields = $fields;
        $controller->view->paypal_url = $payPalService->getPaypalURL();
        echo $controller->view->render('form.phtml');
        return;
    }

    /**
     * get the paypal config
     *
     * @deprecated @see RM_PayPal_Service
     * @return   array
     */
    private function _getConfig()
    {
        $paypalObj = new RM_PayPal;
        $value = $paypalObj->getSettings()->toArray();
        return $value[0];
    }
}