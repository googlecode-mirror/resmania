<?php
class RM_Admin_UnitTypeManagerController extends RM_Controller {

    /*
     * Load the values from the DB
     */
    public function loadeditJsonAction(){
        $unit_id = $this->_getParam('id');

        $unitDAO = new RM_Units();
        $unit = $unitDAO->get($unit_id);

        $rmUnitTypes = new RM_UnitTypes();
        $typeInfo = $rmUnitTypes->getByUnit($unit)->toArray();
        $unittypes = $rmUnitTypes->getAll()->toArray();

        $json = new stdClass;
        $json->currentUnitType = $typeInfo;
        $json->allUnitTypes = $unittypes;

        return array(
            'data' => $json,
            'encoded' => false
        );
    }
    
    /*
     * Update the unit type selection
     */
    public function updateunitJsonAction()
    {
        $unitID = $this->_getParam('unit_id');
        if ($unitID == null) {
            return array('data' => array('success' => false));
        }

        $typeID = $this->_getParam('rm_pages_module_unittype_selection_hidden');
        if ($typeID == null) {
            return array('data' => array('success' => false));
        }

        $unitModel = new RM_Units();
        $unit = $unitModel->find($unitID)->current();
        if ($unit == null) {
            return array('data' => array('success' => false));
        }

        $unit->type_id = (int)$typeID;
        $unitModel->update($unit->toArray(), "id=".$unit->id);

        return array(
            'data' => array('success' => true)
        );        
    }

    public function configJsonAction(){

        $rmUnitTypes = new RM_UnitTypes();
        
        $language = RM_Environment::getInstance()->getLocale();
        $unittypes = $rmUnitTypes->getAll();
               
        $type = array();
        foreach($unittypes as $unittype){
            $type[] = array(
                'id' => $unittype->id,
                'name' => $unittype->$language,
                'price' => $unittype->price
            );            
        }

        $json = new stdClass;
        $json->unitTypes = $type;
                
        $systems = RM_Prices_Manager::getAllPriceSystems();
        $json->systems = array();
        foreach ($systems as $system) {
            $json->systems[] = array(
                'value' => $system->name,
                'text' => $system->getName(RM_Environment::getInstance()->getLocale())
            );
        }

        $module = new RM_Module_UnitTypeManager();
        $defaultUnitType = $module->getDefaultUnitType();
        $json->defaultUnitType = $defaultUnitType->id;

        return array(
            'data' => $json,
            'encoded' => false
        );
    }

    /*
     * Update the Unit Type Config
     */
    public function configupdateJsonAction(){
        $unitTypesModel = new RM_UnitTypes();

        $prices = $this->_getParam("prices", array());
        foreach ($prices as $unitID => $priceModuleName) {
            $unitType = $unitTypesModel->find($unitID)->current();
            if ($unitType !== null) {
                $unitType->price = $priceModuleName;
                $unitType->save();
            }
        }

        $defaultTypeID = $this->_getParam('rm_default_system_unit_type');
        if ($defaultTypeID !== null) {            
            $unitType = $unitTypesModel->find($defaultTypeID)->current();
            if ($unitType !== null) {
                $module = new RM_Module_UnitTypeManager();
                $module->makeDefault($unitType);
            }
        }

        return array(
            'data' => array('success' => true)
        );
    }
}