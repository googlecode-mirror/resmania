<?php
class RM_User_GMapsController extends RM_Controller
{
    public function formAction(){
        
    }

    /**
     * This gathers all the data required for the user end google maps view
     */
    public function mapAction(){

        // get the unit information
        ob_clean();
        $unitID = $this->_getParam('unit_id');
        $locationsModel = new RM_Locations();
        $location = $locationsModel->fetchByUnit($unitID)->toArray();
        if (!empty($location)) {
            $location = $location[0];
        } else {
            $location['address1'] = "";
            $location['address2'] = "";
            $location['city'] = "";
            $location['state'] = "";
            $location['postcode'] = "";
            $location['country'] = "";
        }

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

        // gmaps settings
        $settingsObj = new RM_GMaps();
        $settings = $settingsObj->getSettings()->toArray();
        $this->view->settings = $settings[0];

        // call the view
        echo $this->view->render('GMaps/map.phtml');
        die();
    }

}