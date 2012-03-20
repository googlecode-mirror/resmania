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
 * Interface for taxes systems. All other code need to collaborate to every tax
 * system in a standard way.
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
interface RM_Taxes_Interface
{
    /**
     * Calculate total amount of all taxes.
     *
     * @param RM_Unit_Row $unit
     * @param float $price total price for the unit before taxes
     * @return float total amount of all taxes that applied to current unit
     */
    function calculateTotalTax(RM_Unit_Row $unit, $price);

    /**
     * Assign all taxes to reservation
     * 
     * @param RM_Reservation_Details $detail
     * @param RM_Reservation_Details_Row $detailRow
     * @return bool
     */
    function assign(RM_Reservation_Details $detail, RM_Reservation_Details_Row $detailRow);

    /**
     * Return total value of saved taxes for reservation
     *
     * @param RM_Reservation_Row $reservation
     * @return float
     */
    function getTotalTaxes(RM_Reservation_Row $reservation);

    /**
     * Recalculate all for reservation, after some of the unit details has been changed
     *
     * @param RM_Reservation_Row $reservation
     * @return bool
     */
    function recalculate(RM_Reservation_Row $reservation);

    /**
     * Returns all enabled taxes for a unit or if unit is not specified, all taxes
     *
     * @param RM_Unit_Row $unit
     * @return Zend_Db_Table_Rowset
     */
    function getAllTaxes(RM_Unit_Row $unit);
}