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
 * Class for storing people assigned to a reservation
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Reservation_Persons {

    /**
     * Number of adults
     * @var int
     */
    private $_adults = 1;
    /**
     * Number of children
     * @var int
     */
    private $_children = 0;
    /**
     * Number of adults
     * @var int
     */
    private $_infants = 0;

    /**
     * Constructor
     *
     * @param RM_Unit_Row $unit unit what will be reserved
     * @param RM_Reservation_Period $period - period of reservation
     * @param int $persons [optional][default=1] - how many persons will be in the unit
     */
    public function __construct($persons = array("adults" => 1, "children" => 0, "infants" => 0)) {
        $this->_adults = $persons['adults'];
        $this->_children = $persons['children'];
        $this->_infants = $persons['infants'];
    }

    /**
     *
     * @return int number of adults, default returned is 1
     */
    public function getAdults() {
        $returnVal = (int) $this->_adults;
        if (!$returnVal)
            $returnVal = 1;
        return $returnVal;
    }

    /**
     *
     * @return int  number of children
     */
    public function getChildren() {
        return (int) $this->_children;
    }

    /**
     *
     * @return int  number of infants
     */
    public function getInfants() {
        return (int) $this->_infants;
    }

    /**
     *
     * @return array    containing all persons
     */
    public function getAll() {
        return array("adults" => $this->getAdults(), "children" => $this->getChildren(), "infants" => $this->getInfants());
    }

}