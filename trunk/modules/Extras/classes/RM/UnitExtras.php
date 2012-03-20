<?php
class RM_UnitExtras extends RM_Model
{
    protected $_name = 'rm_unit_extras';

    function insertRows(RM_Extras_Row $extra, array $unitIDs)
    {
        $this->delete('extra_id='.$extra->id); //remove old values
        if ($unitIDs[0] == 0) {
            //all units
            $extra->global = 1;
            $extra->save();
            return true;
        } elseif ($extra->global == 1) {
            $extra->global = 0;
            $extra->save();
        }

        foreach ($unitIDs as $unitID) {
            $this->createRow(array(
                'extra_id' => $extra->id,
                'unit_id' => $unitID,
            ))->save();
        }
    }

    function getByExtra(RM_Extras_Row $extra)
    {
        return $this->fetchAll($this->select()->where('extra_id=?', $extra->id));
    }

    function getByUnit(RM_Unit_Row $unit)
    {
        return $this->fetchAll($this->select()->where('unit_id=?', $unit->id));
    }
}