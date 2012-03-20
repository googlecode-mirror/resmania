<?php
class RM_Extras extends RM_Model_Multilingual
{
    protected $_name = 'rm_extras';
    protected $_rowClass = 'RM_Extras_Row';

    /**
     * Returns all extras that are assigned to unit directly + all global extras
     *
     * @param RM_Unit_Row $unit
     * @return Zend_Db_Table_Rowset_Abstract
     */
    function getByUnit(RM_Unit_Row $unit)
    {
        return $this->fetchAll(
            $this->select()
                ->union(array(
                    $this->select()->setIntegrityCheck(false)
                        ->from(array('c' => 'rm_extras'))
                        ->where('enabled=1')
                        ->joinInner(array('uc' => 'rm_unit_extras'), 'uc.extra_id = c.id AND uc.unit_id = '.$unit->id, array()),
                    $this->select()
                        ->from(array('d' => 'rm_extras'))
                        ->where('global=1')
                        ->where('enabled=1')
                ))
        );
    }
}