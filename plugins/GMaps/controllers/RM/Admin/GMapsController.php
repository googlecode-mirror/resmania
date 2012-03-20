<?php
/**
* Admin GMaps Controller
*
* This handles all AJAX requests from the Admin GUI GMaps Configuration page.
* These methods will create an AJAX response containing JSON data. The JSON
* data is read by the JS code and rendered into interface.
*
* @access 	public
* @author 	Rob
* @copyright	ResMania 2009 all rights reserved.
* @version	1.0
* @link		http://developer.resmania.com/api
* @since  	05-2009
*/
class RM_Admin_GMapsController extends RM_Controller
{
    public function configJsonAction(){
        $GMapsObj = new RM_GMaps;
        $settings = $GMapsObj->getSettings()->toArray();

        $json = new stdClass;
        $json->settings = $settings;

        return array(
            'data' => $json
        );
    }

    public function editJsonAction(){

    }

    /**
     * This saves the paypal settings from the plugin->paypal setting page
     *
     * @return 	json    success value true/false
     */
    public function updateJsonAction(){

        // get the parameters passed from the form submit
        $id = $this->_getParam('id');
        $zoomlevel = $this->_getParam('zoomlevel');
        $maptype = $this->_getParam('maptype');

        $gmapsObj = new RM_GMaps;

        // find the current record from the DB
        $details = $gmapsObj->find($id)->current();

        $status = 'true'; // set this true the if we have a problem we set it false

        // if our current record has not been found return false and don't carry
        // on with the update
        if (!$details) {
            $status = 'false';
        } else {

            $details->zoomlevel = $zoomlevel;
            $details->maptype = $maptype;
            $result = $details->save();

            if ($result!='1') $status = 'false';
        }

        return array(
            'data' => array('success' => $status)
        );

    }

    public function getadminmapAction(){

        // do a quick net check
        $parsed = parse_url('http://maps.google.com/maps');
        $host = $parsed["host"];
        $fp = @fsockopen($host, 80, $errno, $errstr, 20);
        if(!$fp) {
            $this->view->error = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.GMaps.Errors', 'ConnectionError');
        } else {

            $this->view->error = 'false';

            // get the unit information
            ob_clean();
            $unitID = $this->_getParam('unit_id');
            $locationsModel = new RM_Locations();
            $location = $locationsModel->fetchByUnit($unitID)->toArray();
            
            if (!empty($location)){
                $location = $location[0];

                // get the coordinates
                $coordinates = new stdClass;

                // set default map center points...
                if(empty($location['longitude']) || empty($location['latitude'])){
                    $location['longitude'] = 150.644;
                    $location['latitude'] = -34.397;
                }

                $coordinates->longitude = $location['longitude'];
                $coordinates->latitude = $location['latitude'];
                $this->view->coordinates = $coordinates;

                // construct the address for GMAPs to use
                $address = "";
                if ($location['address1']) $address .= $location['address1'];
                if ($location['address2']) $address .= ", ".$location['address2'];
                if ($location['city']) $address .= ", ".$location['city'];
                if ($location['address1']) $address .= ", ".$location['state'];
                if ($location['address1']) $address .= ", ".$location['postcode'];
                if ($location['address1']) $address .= ", ".$location['country'];
                $this->view->address = $address;

            }
            
            // gmaps settings
            $settingsObj = new RM_GMaps();
            $settings = $settingsObj->getSettings()->toArray();
            $this->view->settings = $settings[0];

        }

        // call the view
        echo $this->view->render('GMaps/adminmap.phtml');
        die();
    }
}