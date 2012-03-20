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
 * @access      public
 * @author      Rob/Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Unit_Price_Period {
	const flexible = 'flexible';    
    const night_1 = 'night_1';
    const nights_2 = 'nights_2';
	const nights_3 = 'nights_3';
	const nights_4 = 'nights_4';
    const nights_5 = 'nights_5';
    const nights_6 = 'nights_6';
    const week_1 = 'week_1';
    const weeks_2 = 'weeks_2';
    const weeks_3 = 'weeks_3';    	
    const month_1 = 'month_1';    	
    const months_2 = 'months_2'; 
    const months_3 = 'months_3';
    
    private $_periods = array(
    	RM_UnitPrices_Period::flexible => 'Flexible',
    	RM_UnitPrices_Period::night_1 => '1 Night',
    	RM_UnitPrices_Period::nights_2 => '2 Nights',
    	RM_UnitPrices_Period::nights_3 => '3 Nights',
    	RM_UnitPrices_Period::nights_4 => '4 Nights',
    	RM_UnitPrices_Period::nights_5 => '5 Nights',
    	RM_UnitPrices_Period::nights_6 => '6 Nights',
    	RM_UnitPrices_Period::week_1 => '1 Week',
    	RM_UnitPrices_Period::weeks_2 => '2 Weeks',
    	RM_UnitPrices_Period::weeks_3 => '3 Weeks',    	
    	RM_UnitPrices_Period::month_1 => '1 Month',    	
    	RM_UnitPrices_Period::months_2 => '2 Months', 
    	RM_UnitPrices_Period::months_3 => '3 Months' 
    ); 
    
    public function getAll(){
   		return $this->_periods;
    } 
    
  	public function getPeriodName($key){
    	return $this->_periods[$key];
    }
}
?>