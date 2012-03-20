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
 * Parent class for store all price information
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 * @todo        this class is very similar to RM_Reservation_Details class, we need to remove one of them
 */
class RM_Prices_Information {

    /**
     * @var Zend_Db_Table_Row
     */
    private $_unit;
    /**
     * @var RM_Reservation_Period
     */
    private $_period;
    /**
     * @var RM_Reservation_Persons
     */
    private $_persons;
    /**
     * @var Quantity
     */
    private $_quantity;
    /**
     * @var array   otherInfo used to extend the data that can be saved to the reservation details.
     */
    private $_otherInfo; // could be an array k = name of feild / v = value

    /**
     * @param RM_Unit_Row $unit - unit that we reserve
     * @param RM_Reservation_Period $period - period of reservation with start and end date
     * @param int $persons - number of persons that will arrived
     */
    function __construct($unit, $period, $persons, $otherInfo = array(), $quantity = 1) {
        $this->_unit = $unit;
        $this->_period = $period;
        $this->_persons = $persons;
        $this->_otherInfo = $otherInfo;
        $this->_quantity = $quantity;
    }

    /**
     * Returns unit object that we reserve
     *
     * @return Zend_Db_Table_Row
     */
    public function getUnit() {
        return $this->_unit;
    }

    /**
     * Returns reservation period with start and end dates
     *
     * @return RM_Reservation_Period
     */
    public function getPeriod() {
        return $this->_period;
    }

    /**
     * Returns number of persons
     *
     * @return int
     */
    public function getPersons() {
        return $this->_persons;
    }

    /**
     *
     */
    public function getOtherInfo() {
        return $this->_otherInfo;
    }

    /**
     *
     */
    public function getQuantity() {
        return $this->_quantity;
    }

}
