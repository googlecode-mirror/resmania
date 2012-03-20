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
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
interface RM_Extras_Interface
{
    /**
     * Recalculate all for reservation, after some of the unit details has been changed
     *
     * @abstract
     * @param RM_Reservation_Row $reservation
     * @return bool
     */
    function recalculate(RM_Reservation_Row $reservation);

    /**
     * Returns extra HTML for a summary reservation page to present current system
     *
     * @abstract
     * @param RM_Reservation_Details $detail
     * @return string HTML code to paste
     */
    public function getSummary(RM_Reservation_Details $detail);

    /**
     * Validate selected by user selection
     *
     * @abstract
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function applySelection(Zend_Controller_Request_Abstract $request, RM_Reservation_Details $detail);

    /**
     * Assign extras to reservation
     *
     * @abstract
     * @param RM_Reservation_Details $detail
     * @param RM_Reservation_Details_Row $detailRow
     * @return bool
     */
    public function assign(RM_Reservation_Details $detail, RM_Reservation_Details_Row $detailRow);
}