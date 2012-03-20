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
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_ReservationSummary extends RM_Model {
    protected $_name = 'rm_reservation_summary';
    protected $_rowClass = 'RM_Reservation_Summary_Row';

    /**
     * Returns all summary row s that linked to reservation itself
     * Important! not to reservation details 
     *
     * @param RM_Reservation_Row $reservation
     * @return Zend_Db_Table_Rowset_Abstract
     */
    function fetchByReservation(RM_Reservation_Row $reservation)
    {
        return $this->fetchAll($this->select()->where('reservation_id=?', $reservation->id));
    }

    /**
     * Returns all summary rows that linked to reservation detail
     *
     * @param RM_Reservation_Details_Row $reservationDetail
     * @param string    $type   optional param for specifying the type
     * @return Zend_Db_Table_Rowset_Abstract
     */
    function fetchByReservationDetail(RM_Reservation_Details_Row $reservationDetail, $type = false)
    {        
        if ($type){
            $data = $this->fetchAll(
                    $this->select()
                    ->where('reservation_detail_id=?',$reservationDetail->id)
                    ->where('type=?',$type)
                );
        } else {
            $data = $this->fetchAll($this->select()->where('reservation_detail_id=?',$reservationDetail->id));
        }
        return $data;
    }

    /**
     * Returns row by parameters
     *
     * @param RM_Reservation_Details_Row $detail
     * @param string $type
     * @param int $rowID
     * @return RM_Reservation_Summary_Row
     */
    function getByDetail(RM_Reservation_Details_Row $detail, $type, $rowID)
    {
        return $this->fetchRow(
            $this->select()
                ->where('reservation_detail_id=?', $detail->id)
                ->where('type=?', $type)
                ->where('row_id=?', $rowID)
        );
    }

    /**
     * Returns row by parameters
     *
     * @param RM_Reservation_Row $reservation
     * @param string $type
     * @param int $rowID
     * @return RM_Reservation_Summary_Row
     */
    function getBy(RM_Reservation_Row $reservation, $type, $rowID){
        return $this->fetchRow(
            $this->select()
                ->where('reservation_id=?', $reservation->id)
                ->where('type=?', $type)
                ->where('row_id=?', $rowID)
        );
    }
}