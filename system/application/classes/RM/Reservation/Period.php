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
 * Class for holding reservation interval information
 * 
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Reservation_Period
{
    /**
     * var RM_Date
     */
    private $_start;

    /**
     * var RM_Date
     */
    private $_end;

    /**
     * @param RM_Date $start
     * @param RM_Date $end
     */
    function  __construct($start, $end) {
        $this->_start = $start;
        $this->_end = $end;
    }

    /**
     * Returns default reservation period, to present
     * period for system if user didn't select it yet
     *
     * @return RM_Reservation_Period
     */
    public static function getDefault() {
        return new RM_Reservation_Period(
            new RM_Date(strtotime(date("Y-m-d"))),
            new RM_Date(strtotime(date("Y-m-d", time() + 365 * 24 * 60 * 60)))
        );
    }

    /**
     * Checks if day is in period
     *
     * @param RM_Date $day
     * @return bool
     */
    function isInternal(RM_Date $day){
        if ($this->getEnd()->compare($day) < 0) return false;
        if ($this->getStart()->compare($day) > 0) return false;
        return true;
    }

    /**
     * Returns intersection with current period
     *
     * @param RM_Reservation_Period $period
     * @return void
     */
    function getIntersection(RM_Reservation_Period $period){
        if ($this->getEnd()->compare($period->getStart()) < 0) return null;
        if ($this->getStart()->compare($period->getEnd()) > 0) return null;

        $startDate = clone $this->getStart();
        if ($startDate->compare($period->getStart()) < 0){
            $startDate = clone $period->getStart();
        }

        $endDate = clone $this->getEnd();
        if ($endDate->compare($period->getEnd()) > 0) {
            $endDate = clone $period->getEnd();
        }

        return new RM_Reservation_Period($startDate, $endDate);
    }

    /**
     * @return RM_Date $start
     */
    function getStart(){
        return $this->_start;
    }

    /**
     * @return RM_Date $start
     */
    function getEnd(){
        return $this->_end;
    }

    /**
     * Calculate period length in days
     *
     * @param $days boolean - return in
     * @return int - length is days
     */
    public function getLength($days = true){
        $endTimestamp = $this->getEnd()->toString('U');
        $startTimestamp = $this->getStart()->toString('U');
        $hoursNumber = ($endTimestamp - $startTimestamp) / (60 * 60);
        if ($days) {
            return round($hoursNumber / 24);
        }
        return round($hoursNumber);
    }

    /**
     * @return int period length in seconds
     */
    public function getSeconds()
    {
        $endTimestamp = $this->getEnd()->toString('U');
        $startTimestamp = $this->getStart()->toString('U');
        return ($endTimestamp - $startTimestamp);
    }

    /**
     * Returns array of days between two dates. Including both first
     * and last days
     *
     * @param $excluding int -
     * 0: no exclude - including both first and last
     * 1: excluding last
     * 2: excluding first
     * 3: excluding both first and last
     * 
     * @return array Array with RM_Date objects
     */
    public function getDays($excluding = 1){
        $result = array();
        $startDate = clone $this->getStart();
        $numberOfDays = $this->getLength(); // we need to include one last day
        for ($i = 0; $i <= $numberOfDays; $i++) {
            if ($excluding == 2 || $excluding == 3) {
                if ($i == 0) continue;
            }
            if ($excluding == 1 || $excluding == 3) {
                if ($i == $numberOfDays) break;
            }
            $result[] = clone $startDate;
            $startDate->addDay(1);
        }
        return $result;
    }

    /**
     * Returns array of hours between two dates
     *
     * @param $excluding int -
     * 0: no exclude - including both first and last
     * 1: excluding last
     * 2: excluding first
     * 3: excluding both first and last
     *
     * @return array Array with RM_Date objects
     */
    public function getHours($excluding = 1){
        $result = array();
        $startDate = clone $this->getStart();
        $numberOfHours = $this->getLength(false);
        for ($i = 0; $i <= $numberOfHours; $i++) {
            if ($excluding == 2 || $excluding == 3) {
                if ($i == 0) continue;
            }
            if ($excluding == 1 || $excluding == 3) {
                if ($i == $numberOfHours) break;
            }
            $result[] = clone $startDate;
            $startDate->addHour(1);            
        }
        return $result;
    }

    public function toArray($time = false){
        $config = new RM_Config();

        $dateFormat = $config->getValue("rm_config_dateformat");
        if ($time){
            $dateFormat = $config->getValue("rm_config_dateformat")." H:i";
        }

        return array("start"=>$this->getStart()->toString($dateFormat),"end"=>$this->getEnd()->toString($dateFormat));
    }
}