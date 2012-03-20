<?php

class RM_UnitCategories extends RM_Model {

    protected $_name = 'rm_unit_categories';
    protected $_primary = array('unit_id', 'category_id');

    public function getByUnit(RM_Unit_Row $unit) {
        return $this->fetchAll($this->select()->where('unit_id=?', $unit->id));
    }

    public function getByCategories($categoryIDs) {

        if (!is_array($categoryIDs)) {
            $ids = $categoryIDs;
        } else {
            $ids = implode(',', $categoryIDs);
        }
        if (isset($categoryIDs)) {
            $sql = "
                SELECT
                    COUNT(1) as total,
                    unit_id
                FROM
                    " . $this->_name . "
                WHERE
                    category_id IN (" . $ids . ")
                GROUP BY
                    unit_id
                ";
            return $this->_getBySQL($sql);
        } else {
            return false;
        }
    }

}