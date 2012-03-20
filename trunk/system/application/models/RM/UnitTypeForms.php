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
class RM_UnitTypeForms extends RM_Model
{
    protected $_name = 'rm_unit_type_forms';
    protected $_rowClass = 'RM_UnitTypeForms_Row';

    /**
     * Check if form with input type is exists
     *
     * @param RM_Form_Row $form
     * @param RM_UnitType_Row $unitType
     * @return bool - true is form is already exists, false if not exists
     */
    function check(RM_Form_Row $form, $unitType = null){
	if ($unitType == null) {
            $unitTypeID = RM_UnitTypes::DEFAULT_TYPE;
        } else {
            $unitTypeID = $unitType->id;
        }
	$unitTypeForm = $this->fetchRow($this->select()->where('form_id=?', $form->id)->where('unit_type_id=?',$unitTypeID));
	return ($unitTypeForm !== null);
    }

    /**
     * Fetch row by form and unit type
     *
     * @param RM_Form_Row $form
     * @param RM_UnitType_Row $unitType
     * @return Zend_Db_Table_Rowset_Abstract
     */
    function fetchBy(RM_Form_Row $form, $unitType = null){
        if ($unitType == null) {
            $unitTypeID = RM_UnitTypes::DEFAULT_TYPE;
        } else {
            $unitTypeID = $unitType->id;
        }

        $unitTypeForm = $this->fetchRow($this->select()->where('form_id=?', $form->id)->where('unit_type_id=?',$unitTypeID));

	if ($unitTypeForm == null) {
	    //we don't have a row for this unit yet, we will use default values	    
	    $unitTypeForm = $this->fetchRow($this->select()->where('form_id=?', $form->id)->where('unit_type_id=?',RM_UnitTypes::DEFAULT_TYPE));
	}

	return $unitTypeForm;
    }
}