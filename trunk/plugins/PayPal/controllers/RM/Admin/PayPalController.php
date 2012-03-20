<?php
/**
* Admin PayPal Controller
*
* This handles all AJAX requests from the Admin GUI PayPal Configuration page.
* These methods will create an AJAX response containing JSON data. The JSON
* data is read by the JS code and rendered into interface.
*
* @access 	public
* @author 	Rob, Valentin
* @copyright	ResMania 2009 all rights reserved.
* @version	1.0
* @link		http://developer.resmania.com/api
* @since  	05-2009
*/
class RM_Admin_PayPalController extends RM_Controller
{
    /**
     * This loads the paypal configuration, the data is retrieved using the
     * RM_PayPal class
     *
     * @return 	json    json array information containing paypal information
     */
    public function configJsonAction(){

        $paypalObj = new RM_PayPal;
        $settings = $paypalObj->getSettings()->toArray();

        $json = new stdClass;
        $json->settings = $settings;

        return array(
            'data' => $json
        );
    }

    /**
     * This saves the paypal settings from the plugin->paypal setting page
     *
     * @return 	json    success value true/false
     */
    public function updateJsonAction(){

        // get the parameters passed from the form submit
        // look at edit.js->RM.Pages.Functions.plugins_paypal_save for the params
        // passed here.
        $id = $this->_getParam('id');
        $account = $this->_getParam('account');
        $sandbox = $this->_getParam('sandbox');
        $default = $this->_getParam('default');

        // Create the paypal object, RM_PayPal extends RM_Model so we can use
        // methods that this inherits such as find, save to update the DB.
        $paypalObj = new RM_PayPal;

        // find the current record from the DB
        $details = $paypalObj->find($id)->current();

        $status = 'true'; // set this true the if we have a problem we set it false

        // if our current record has not been found return false and don't carry
        // on with the update
        if (!$details) {
            $status = 'false';
        } else {

            $details->account = $account;
            $details->sandbox = $sandbox;
            $details->defaultplugin = $default;
            $result = $details->save();

            if ($result!='1') $status = 'false';
        }
        
        // return a state to the GUI so that the saving message is removed etc.
        // look edit.js->RM.Pages.Functions.plugins_paypal_save for the success,
        // failure handling
        return array(
            'data' => array('success' => $status)
        );

    }

    public function logJsonAction(){

        $paypalObj = new RM_Plugin_PayPal;
        return array(
            'data' => $paypalObj->getLog()
        );
    }

    public function clearlogJsonAction()
    {
        $module = new RM_Plugin_PayPal();
        try {
            $module->clearLog();
        } catch (RM_Exception $e) {
            return array(
                'data' => array('success' => false, 'message' => $e->getMessage())
            );
        }
        return array(
            'data' => array('success' => true)
        );
    }
}