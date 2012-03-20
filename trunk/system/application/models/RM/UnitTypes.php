<?php
/**
 * ResMania - Reservation System Framework http://resmania.com
 * Copyright (C) 2011  ResMania Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_UnitTypes extends RM_Model_Multilingual {
    const DEFAULT_TYPE = 1;

    protected $_name = 'rm_unit_types';
    protected $_rowClass = 'RM_UnitType_Row';
	protected $_referenceMap    = array(       
        'Units' => array(
            'columns'           => 'unit_id',
            'refTableClass'     => 'RM_Units',
            'refColumns'        => 'id'
        )
    );

    /*
     * @param Zend_Db_Table_Row_Abstract $unit
     * @return Zend_Db_Table_Row_Abstract
     */
    function getByUnit($unit){        
        return $this->find($unit->type_id)->current();
    }

    function findByName($name, $locale = null){
        if ($locale == null) {
            $locale = RM_Environment::getInstance()->getLocale();
        }

        $list = $this->fetchAll($this->select()->where("$locale=?", $name));
        return $list->current();
    }

    /*
     * this will return all unit types but also the assignments from rm_units.
     * and the results are language dependant.
     */
    function getAllAssigned($language = null){
        if ($language == null) {
            $language = RM_Environment::getInstance()->getLocale();
        }

        $sql = "
        SELECT u.id, u.".$language.", ut.type_id
        FROM rm_unit_types u
        JOIN rm_units ut
        ";

        return $this->_getBySQL($sql);
    }

    /*
     * this will return all types
     */
    function getAll(){
        return $this->fetchAll();
    }
}