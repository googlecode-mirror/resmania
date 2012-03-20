<?php
class RM_Categories extends RM_Model_Multilingual
{
     protected $_name = 'rm_categories';

    const DEFAULT_NAME = 'NEW CATEGORY';
    const TRASH = -2;
    const DISABLED = -1;
    const ROOT = 0;
    const ROOT_NAME = 'root';

    public function reorder($parentID, $childID)
    {
        $child = $this->find($childID)->current();
        if ($parentID == self::ROOT_NAME) {
            $parentID = self::ROOT;
        }
        $child->parent_id = $parentID;
        return $child->save();
    }

    public function fetchByParent($parentID)
    {
        if ($parentID == self::ROOT_NAME){
            $parentID = self::ROOT;
        }
        return $this->fetchAll($this->select()->where('parent_id=?', $parentID));
    }

    public function fetchByUnit($unitID)
    {
        $sql = "
            SELECT
                *
            FROM
                ".$this->_name."
            INNER JOIN
                rm_unit_categories uc ON uc.unit_id = $unitID AND uc.category_id=".$this->_name.".id ";
        
        return $this->_getBySQL($sql);
    }
}