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
 * Default tax system when there is no any other tax system in the application.
 * Doing nothing.
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Taxes_Default implements RM_Taxes_Interface
{        
    function calculateTotalTax(RM_Unit_Row $unit, $price){
        return 0;
    }

    function assign(RM_Reservation_Details $detail, RM_Reservation_Details_Row $detailRow){
        return true;
    }    

    function getTotalTaxes(RM_Reservation_Row $reservation){
        return 0;
    }
    
    function recalculate(RM_Reservation_Row $reservation){
        return true;
    }
       
    function getAllTaxes(RM_Unit_Row $unit){
        return array();
    }
}