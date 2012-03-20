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
 *
 * Discounts row class
 *
 * This row class uses in the model to represent a tax.
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
abstract class RM_Discounts_Row_Abstract extends RM_Row implements RM_Discounts_Object_Interface {

    /**
     * @var RM_Discounts_Type
     */
    protected $_type;

    public function init() {
        $typeClassName = 'RM_Discounts_Type_' . ucfirst(strtolower($this->type));
        $this->_type = new $typeClassName($this);
    }

    /**
     * Calculate
     *
     * @param RM_Reservation_Details $detail
     * @return float total amount of discount OR false if discount doesn't match
     */
    public function calculate(RM_Reservation_Details $detail) {
        if ($this->isMatched($detail)) {
            return $this->_type->calculate($detail);
        }
        return false;
    }

    /**
     * IMPORTANT! This method only checked internal rule matched,
     * to get all discount for a unit (and global) @see RM_Discounts::getByUnit
     *
     * @param RM_Reservation_Details $detail
     * @return void
     */
    public function isMatched(RM_Reservation_Details $detail) {
        //if type is amount we need to check that reservation is in dates
        if ($this->type == 'amount') {
            $discountPeriod = $this->getPeriod();
            $startDate = $detail->getPeriod()->getStart();
            if (($startDate->compare($discountPeriod->getStart()) >= 0) &&
                    ($startDate->compare($discountPeriod->getEnd()) <= 0)
            ) {
                return true;
            }
        }
        if ($this->type == 'percentage') {
            if ($this->getPeriod()->getIntersection($detail->getPeriod()) !== null) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return RM_Reservation_Period
     */
    public function getPeriod() {
        return new RM_Reservation_Period(
                new RM_Date(strtotime($this->start_date)),
                new RM_Date(strtotime($this->end_date))
        );
    }

    /**
     * Return name of the extra
     *
     * @param string $iso ISO code
     * @return string
     */
    public function getName($iso = null) {
        if ($iso == null) {
            $iso = RM_Environment::getInstance()->getLocale();
        }
        return $this->$iso;
    }

    /**
     * We need to delete all unit discounts as weel.
     *
     * @return void
     */
    public function delete() {
        return parent::delete();
    }

    /**
     * Returns the column/value data as an array and parse all assigned units
     * in csv format in 'units' field.
     *
     * @return array
     */
    public function toArray() {
        return parent::toArray();
    }

    public function toString() {
        return $this->_type->toString();
    }

}