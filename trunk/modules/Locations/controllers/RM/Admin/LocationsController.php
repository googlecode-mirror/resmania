<?php
class RM_Admin_LocationsController extends RM_Controller {
    
    public function loadJsonAction(){
        $unit_id = $this->_getParam('id');

        $locationsDAO = new RM_Locations();
        $locationInfo = $locationsDAO->fetchByUnit($unit_id)->toArray();

        return array(
            'data' => $locationInfo,
            'encoded' => false
        );
    }

    public function updateJsonAction(){

        $locationData = $this->_getParam('rm_locations_edit');

        $unitLocationsData['unit_id'] = $locationData['unit_id']; //$unitLocationsData['unit_id'] is used by the insert
        
        unset($locationData['unit_id']); // we don't need this
        
        $daoLocations = new RM_Locations;

        if ($locationData['id']=="" || $locationData['id']==null){
            // insert new
            $locationData['id'] = null;
            $unitLocationsData['location_id'] = $daoLocations->insert($locationData);

            $daoUnitLocations = new RM_UnitLocations;
            $daoUnitLocations->insert($unitLocationsData);

        } else {
            // update

            $daoLocations->update($locationData, "id='".$locationData['id']."'");
        }
        
        return array(
        'data' => array('success' => 'true', 'msg' => '')
        );

    }
}