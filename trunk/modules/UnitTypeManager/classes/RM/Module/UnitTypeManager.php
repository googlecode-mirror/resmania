<?php
class RM_Module_UnitTypeManager extends RM_Module {
    private $_defaultUnitType = 1;

    public function  __construct() {
        $this->name = 'UnitTypeManager';
    }    

    public function getNode(){
        return null; // return null to not show the menu item
    }

    /**
     * Makes default unit type
     *
     * @param RM_UnitType_Row $unitType
     * @return void
     */
    public function makeDefault(RM_UnitType_Row $unitType)
    {
        $model = new RM_UnitTypes();
        $rows = $model->fetchAll();
        foreach ($rows as $row) {
            $row->default = 0;
            $row->save();
        }
        $unitType->default = 1;
        $unitType->save();
    }

    public function getDefaultUnitType()
    {
        $model = new RM_UnitTypes();
        $row = $model->fetchRow($model->select()->where("`default`=1"));
        if ($row == null) {
            //We have a situation when no unit type is default.
            //We need to assign Default unit type to default.
            $row = $model->find($this->_defaultUnitType)->current();
            if ($row !== null) {
                $this->makeDefault($row);
            }
        }
        return $row;
    }
}