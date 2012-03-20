<?php
class RM_Locations extends RM_Model_Flexible
{
    protected $_name = 'rm_locations';

    public function fetchByUnit($unitID)
    {
        $sql = "
            SELECT *
            FROM  `".$this->_name."` l
            JOIN  `rm_unit_locations` ul ON l.id = ul.location_id
            WHERE ul.unit_id='".$unitID."' LIMIT 1
        ";

        return $this->_getBySQL($sql);
    }

    public function getAll()
    {
        return;
    }

    public function fetchBy($fieldName, $value)
    {
        return $this->fetchAll(
            $this->select()->setIntegrityCheck(false)
                ->from(array('l' => 'rm_locations'))
                ->joinInner(
                    array(
                        'ul' => 'rm_unit_locations'
                    ), 
                    'l.id = ul.location_id',
                    array('unit_id')
                )
                ->where("l.$fieldName LIKE ?", "%$value%")
        );
    }

    public function fetchAllUnique($fieldName)
    {
        $sql = "
            SELECT
                DISTINCT l.$fieldName
            FROM
                `".$this->_name."` l
            WHERE
                l.$fieldName != ''
        ";

        return $this->_getBySQL($sql);
    }
}