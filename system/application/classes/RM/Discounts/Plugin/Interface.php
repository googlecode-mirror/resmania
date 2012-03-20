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
 * Interface for taxes systems.
 *
 * All other code need to collaborate to every tax system in a standard way.
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
interface RM_Discounts_Plugin_Interface {

    /**
     * Save selected discounts to the database from user end reservation process
     *
     * @abstract    
     * @param RM_Reservation_Details $detail
     * @param RM_Reservation_Details_Row $detailRow
     * @return bool
     */
    public function save(RM_Reservation_Details $detail, RM_Reservation_Details_Row $detailRow);

    /**
     * Recalculate all for reservation, after some of the unit details has been changed
     *
     * @abstract
     * @param RM_Reservation_Row $reservation
     * @return bool
     */
    function recalculate(RM_Reservation_Row $reservation);

    /**
     * Returns extra HTML for a summary reservation page to present current discount plugin
     *
     * @abstract
     * @param RM_Reservation_Details $detail
     * @return void
     */
    public function getSummary(RM_Reservation_Details $detail);

    /**
     * Get all discount object that are applied to current unit in the current reservation
     *
     * @abstract
     * @param RM_Reservation_Details $details
     * @return array
     */
    public function get(RM_Reservation_Details $details);
}