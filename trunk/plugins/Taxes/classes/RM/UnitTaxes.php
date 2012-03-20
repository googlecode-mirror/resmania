<?php
class RM_UnitTaxes extends RM_Model
{
    protected $_name = 'rm_unit_taxes';    

    function insertRows(RM_Taxes_Row $coupon, array $unitIDs)
    {
        $this->delete('tax_id='.$coupon->id); //remove old values
        if ($unitIDs[0] == 0) {
            //all units
            $coupon->global = 1;
            $coupon->save();
            return true;
        } elseif ($coupon->global == 1) {
            $coupon->global = 0;
            $coupon->save();
        }

        foreach ($unitIDs as $unitID) {
            $this->createRow(array(
                'tax_id' => $coupon->id,
                'unit_id' => $unitID,
            ))->save();
        }
    }

    function getByTax(RM_Taxes_Row $tax)
    {
        return $this->fetchAll($this->select()->where('tax_id=?', $tax->id));
    }

    function getByUnit(RM_Unit_Row $unit)
    {
        return $this->fetchAll($this->select()->where('unit_id=?', $unit->id));
    }
}