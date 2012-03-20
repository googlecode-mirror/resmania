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
interface RM_Prices_Interface {
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
    public function getTotalUnitPrice(RM_Prices_Information $information, $byStep = false);

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
    public function getLowestUnitPrice(RM_Prices_Information $information);
   
    /**
     * Return max prices among all units for the one default period for price manager
     * @return float
     */
    public function getTotalHighestPrice();

    /**
     * Returns all unit ids that have price in price range in input period
     *
     * @param float $from
     * @param float $to
     * @param RM_Reservation_Period $period
     * @return array array with unit ids
     */    
    public function getByPriceRange($from, $to, RM_Reservation_Period $period = null);

    /**
     * Returns all unit that have a min stay on this period less than this period
     *
     * @param RM_Reservation_Period $period
     * @return array array with unit ids
     */
    public function getByMinPeriod(RM_Reservation_Period $period);

    /**
     * Returns PHP dateformat to present reservation start and end date on the UI
     *
     * @param bool $php - internal PHP date format or UI date format, if true - internal PHP date format
     * @return string
     */
    public function getDateformat($php = false);

    /**
     * Returns other information associate to the price module
     *
     * @return array
     */
    public function getOtherInfo();
}