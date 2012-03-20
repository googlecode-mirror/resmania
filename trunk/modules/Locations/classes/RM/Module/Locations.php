<?php
class RM_Module_Locations extends RM_Module implements RM_Unit_Copy_Interface, RM_Search_Advanced_Interface
{
    /**
     * Returns all unit ids that are passed criteria option
     *
     * @param RM_Unit_Search_Criteria $criteria
     * @return array, false. If FALSE then all unit are passed, if array - array with unit IDs, if empty array - none of the units are passed
     */
    function getAdvancedSearchUnitIDs(RM_Unit_Search_Criteria $criteria)
    {
        $fieldNames = array('city', 'country', 'state');
        $unitIDs = false;
        foreach ($fieldNames as $fieldName) {
            if ($criteria->$fieldName == null || $criteria->$fieldName == ''){
                continue;
            }
            $fieldNameUnitIDs = $this->_getUnitIDs($fieldName, $criteria->$fieldName);
            if (count($fieldNameUnitIDs) == 0) {
                return array();
            }
            if ($unitIDs === false) {
                $unitIDs = $fieldNameUnitIDs;
            } else {
                $unitIDs = array_intersect($unitIDs, $fieldNameUnitIDs);
            }            
        }
        return $unitIDs;
    }

    /**
     * Returns all unit IDs that have field name like field value in locations
     *
     * @param string $fieldName
     * @param string $fieldValue
     * @return Zend_Db_Table_Rowset
     */
    protected function _getUnitIDs($fieldName, $fieldValue)
    {
        $model = new RM_Locations();
        $rows = $model->fetchBy($fieldName, $fieldValue);
        $unitsIDs = array();
        foreach ($rows as $row) {
            $unitsIDs[$row->unit_id] = $row->unit_id;
        }
        return $unitsIDs;
    }

    function copyInformation(RM_Unit_Row $original, RM_Unit_Row $copy)
    {
        $locationsModel = new RM_Locations();
        $location = $locationsModel->fetchByUnit($original->id)->current();
        if ($location == null) {
            return;
        }

        $copyLocationData = $location->toArray();
        unset($copyLocationData['id']);
        $copyLocation = $locationsModel->createRow($copyLocationData);
        $copyLocationID = $copyLocation->save();

        $unitLocationsModel = new RM_UnitLocations();
        $unitLocation = $unitLocationsModel->createRow(array(
            'unit_id' => $copy->id,
            'location_id' => $copyLocationID
        ));
        $unitLocation->save();
    }

    public function  __construct() {
        $this->name = 'Locations';        
    }          

    public function getConfigNode()
    {
        return null; // return null to not show the menu item
    }

    public function getNode(){
        return null; // return null to not show the menu item
    }
}
