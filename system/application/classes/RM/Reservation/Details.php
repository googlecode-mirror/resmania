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
 * Class data holder for reservation informaion
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Reservation_Details {
    /**
     * @var RM_Unit_Row
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
     * @var array   otherInfo used to extend the data that can be saved to the reservation details.
     */
    private $_otherInfo; // could be an array k = name of feild / v = value

    /**
     * Total price without any extra options, without tax and etc.
     *
     * @var float
     */
    private $_total;

    /**
     * @var array with RM_Extras_Object_Interface
     */
    private $_extras = array();

    /**
     * @var array with RM_Others_Object_Interface
     */
    private $_others = array();

    /**
     * Constructor
     *
     * @param RM_Unit_Row $unit unit what will be reserved
     * @param RM_Reservation_Period $period - period of reservation
     * @param int $persons [optional][default=1] - how many persons will be in the unit
     */
    public function  __construct(RM_Unit_Row $unit, RM_Reservation_Period $period, RM_Reservation_Persons $persons, $otherInfo = array(), $total = false) {
        $this->_unit = $unit;
        $this->_period = $period;
        $this->_persons = $persons;
        $this->_otherInfo = $otherInfo;


        if (!$total || $total === 0){
            $this->_initTotalPrice();
        } else {
            $this->_total = $total;
        }
    }

    private function _initTotalPrice(){
        $priceSystem = RM_Environment::getInstance()->getPriceSystem();
        $information = new RM_Prices_Information(
            $this->_unit,
            $this->_period,
            $this->_persons,
            $this->_otherInfo
        );

        try {
            $this->_total = $priceSystem->getTotalUnitPrice($information);
        } catch (Exception $e){
           $this->_total = 0;
        }
    }

    public function getUnit(){
        return $this->_unit;
    }

    /**
     * @return RM_Reservation_Period
     */
    public function getPeriod(){
        return $this->_period;
    }

    public function getPersons(){
        return $this->_persons;
    }

    public function getTotal(){
        return $this->_total;
    }

    public function getOtherInfo(){
        return $this->_otherInfo;
    }

    public function addExtras(array $extras)
    {
        $this->_extras = array_merge($this->_extras, $extras);
    }

    public function addOthers(array $others)
    {
        $this->_others = array_merge($this->_others, $others);
    }

    /**
     * @return array
     */
    public function getExtras()
    {
        return $this->_extras;
    }

    /**
     * @return array
     */
    public function getOthers()
    {
        return $this->_others;
    }
}
