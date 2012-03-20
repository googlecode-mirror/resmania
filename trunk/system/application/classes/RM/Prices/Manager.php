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
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Prices_Manager implements RM_Prices_Interface {
    private $_systems = array();

    private static $_instance = null;

    /**
     * Returns PHP dateformat to present reservation start and end date on the UI
     *
     * @param bool $php - internal PHP date format or UI date format, if true - internal PHP date format
     * @return string
     */
    public function getDateformat($php = false){
        throw new Exception('RM_Prices_Manager->getDateformat method should not be called directly');
    }

    /**
     * Returns PHP dateformat to present reservation start and end date on the UI
     *
     * @param bool $php - internal PHP date format or UI date format, if true - internal PHP date format
     * @return string
     */
    public function getOtherInfo(){
        throw new Exception('RM_Prices_Manager->getOtherInfo method should not be called directly');
    }
    
    /**
     * The only one method to return the instance of this class, 'cause constructor is private
     *
     * @return RM_Prices_Manager
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
          self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Get current price system for unit
     *
     * @param RM_Unit_Row $unit
     * @return RM_Prices_Interface
     */
    public function getRealPriceSystem(RM_Unit_Row $unit){
        return $this->_getPriceSystem($unit);
    }

    /**
     * Get current if the unit should show the time fields
     *
     * @param RM_Unit_Row $unit
     * @return boolean
     */
    public function getShowTime(RM_Unit_Row $unit){
        $priceSystem = $this->_getDefaultPriceSystem();
        if ($priceSystem == null) {
            return null;
        }

        $showtime = $priceSystem->getShowTime();
        return $showtime;
    }

    /**
     * Get current price system for unit
     *
     * @param RM_Unit_Row $unit
     * @return RM_Prices_Interface
     */
    private function _getPriceSystem(RM_Unit_Row $unit){
        //TODO: maybe we need to change this later
        if (class_exists('RM_Module_UnitTypeManager') == false) {
            return $this->_getDefaultPriceSystem();
        }

        $unitTypeModel = new RM_UnitTypes();
        $unitType = $unitTypeModel->find($unit->type_id)->current();
        if ($unitType == null) {
            return $this->_getDefaultPriceSystem();
        }

        $priceModuleName = $unitType->price;
        if ($priceModuleName == ""){
            return $this->_getDefaultPriceSystem();
        }

        $priceSystems = $this->_getAllPriceSystems();
        foreach ($priceSystems as $priceSystem) {
            if ($priceSystem->name == $priceModuleName) {
                return $priceSystem;
            }
        }
        
        return $this->_getDefaultPriceSystem();
    }

    /**
     * Get current price system for unit
     *
     * @return RM_Prices_Interface
     */
    private function _getDefaultPriceSystem(){
        if (isset($this->_systems[0])) {
            return $this->_systems[0];
        }
        return null;
    }

    /**
     * Get current price system for unit
     *
     * @return array with RM_Prices_Interface
     */
    public static function getAllPriceSystems(){
        return self::getInstance()->_getAllPriceSystems();
    }

    /**
     * Get current price system for unit
     *
     * @return array with RM_Prices_Interface
     */
    private function _getAllPriceSystems(){
        return $this->_systems;
    }

    private function  __construct() {
        $this->_initialize();
    }

    private function _initialize(){
        $model = new RM_Modules();
        $modules = $model->getAll();
        foreach ($modules as $moduleObject) {
            if ($moduleObject instanceof RM_Prices_Interface) {
                $this->_systems[] = $moduleObject;
            }
        }
    }

    /**
     * Calculate the total price for one unit
     *
     * @throw RM_Exception - with message that is going wrong while price calculation
     * @param $information RM_Prices_Information - information object,
     * contains all needed information about unit reservation:
     * unit,
     * start date,
     * end date,
     * number of persons
     * etc.
     *
     * @param bool $byStep - if true, the result will be in array (0 => array('step' => RM_Date, 'price' => float))
     * @return float
     */
    public function getTotalUnitPrice(RM_Prices_Information $information, $byStep = false){
        $priceSystem = $this->_getPriceSystem($information->getUnit());

        // TODO: this throws an exception if the reservation has a daily limit
        // set $priceSystem->getTotalUnitPrice. added a handler for this. However
        // we probably need to change this method so that this message doesn't
        // throw an exception.
        // Valentin: Rob, please do not change code so dramatically, this method is used a lot of times with
        // exception handling.
        $priceData = $priceSystem->getTotalUnitPrice($information, $byStep);
        return $priceData;
    }


    /**
     * Returns the lowest price of a unit over a given period
     *
     * @param $information RM_Prices_Information - information object,
     * contains all needed information about unit reservation:
     * unit,
     * start date,
     * end date,
     * number of persons
     * etc.
     *
     * @return float
     */
    public function getLowestUnitPrice(RM_Prices_Information $information){
        $priceSystem = $this->_getPriceSystem($information->getUnit());
        if ($priceSystem == null) {
            return null;
        }
        return $priceSystem->getLowestUnitPrice($information);
    }

    /**
     * Return max prices among all units for the one default period for price manager
     * @return float
     */
    public function getTotalHighestPrice(){
        $priceSystem = $this->_getDefaultPriceSystem();
        if ($priceSystem == null) {
            return null;
        }
        return $priceSystem->getTotalHighestPrice();
    }

    /**
     * Returns all unit ids that have price in price range in input period
     *
     * @param float $from
     * @param float $to
     * @param RM_Reservation_Period $period
     * @return array array with unit ids
     */
    public function getByPriceRange($from, $to, RM_Reservation_Period $period = null){
        $unitIDs = array();
        $systems = $this->_getAllPriceSystems();
        foreach ($systems as $system) {
            $unitIDs = array_merge($unitIDs, $system->getByPriceRange($from, $to, $period));
        }
        return array_unique($unitIDs);
    }

    /**
     * Returns unit that are not that have a min stay on this period more than this period
     * or don't have a prices at all for this period
     *
     * @param RM_Reservation_Period $period
     * @return array array with unit ids
     */
    public function getByMinPeriod(RM_Reservation_Period $period){
        $unitIDs = array();
        $systems = $this->_getAllPriceSystems();
        foreach ($systems as $system) {
            $systemUnitIDs = $system->getByMinPeriod($period);
            $unitIDs = array_merge($unitIDs, $systemUnitIDs);
        }
        return array_unique($unitIDs);
    }

    /**
     * Fetches units that have the price system setting of availability checking
     * disabled.
     *
     * @return array
     */
    public function getUnitWithAvailabiltyCheckDisabled(){
        $unitIDs = array();
        $systems = $this->_getAllPriceSystems();
        foreach ($systems as $system) {
            try {
                // wrapped in a try in case the price system doesn't have this
                // method
                $systemUnitIDs = $system->getUnitWithAvailabiltyCheckDisabled();
                $unitIDs = array_merge($unitIDs, $systemUnitIDs);
            } catch(Exception $e){}
        }
        return array_unique($unitIDs);
    }
}