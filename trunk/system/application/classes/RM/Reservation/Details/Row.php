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
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Reservation_Details_Row extends RM_Row
{
    private static $_dateFormat = 'Y-m-d H:i:s';    

    /**
     * Return total price for reservation details including all
     * module/plugin addons
     *
     * @return float
     */
    function getTotalPrice()
    {
        $total = $this->total_price;
        $summaryModel = new RM_ReservationSummary();
        $summaryRows = $summaryModel->fetchByReservationDetail($this);
        foreach ($summaryRows as $row) {
            $total += $row->total_amount;
        }
        return $total;
    }

    function getPersons(){
       return array("adults"=>$this->adults,"children"=>$this->children,"infants"=>$this->infants);
    }

    /**
     * @todo Oh, we really need to remove this method later, so in system should be only RM_Reservation_Details_Row class
     * @return RM_Reservation_Details
     */
    function transform(){
        $period = new RM_Reservation_Period(
            new RM_Date(strtotime($this->start_datetime)),
            new RM_Date(strtotime($this->end_datetime))
        );
        $unitModel = new RM_Units();
        $unit = $unitModel->find($this->unit_id)->current();
        return new RM_Reservation_Details($unit, $period, new RM_Reservation_Persons());        
    }

    function getTotalSeconds()
    {
        return (int)$this->getEndDatetime('U') - (int)$this->getStartDatetime('U');
    }

    /**
     * Return total number of days for a reservation rounded up.     
     */
    function getTotalDays()
    {
        return ceil(((int)$this->getEndDatetime('U') - (int)$this->getStartDatetime('U')) / (60 * 60 * 24));
    }

    function getStartDatetime($format = null){
        if ($format == null) {
            return $this->start_datetime;
        }
        $date = new RM_Date($this->start_datetime, self::$_dateFormat);
        return $date->toString($format);
    }

    function getEndDatetime($format = null){
        if ($format == null) {
            return $this->end_datetime;
        }
        $date = new RM_Date($this->end_datetime, self::$_dateFormat);
        return $date->toString($format);
    }

    public function findUnit($locale = null){
        if ($locale == null) {
            $locale = RM_Environment::getInstance()->getLocale();
        }

        $model = new RM_Units();
        return $model->get($this->unit_id, $locale);
    }

    public function delete(){
        //Delete all reservation summary that are assigned to it
        $model = new RM_ReservationSummary();
        $rows = $model->fetchByReservationDetail($this);
        foreach ($rows as $row) {
            $row->delete();
        }
        
        return parent::delete();
    }
}