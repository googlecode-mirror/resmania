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
 * Class for represent date in Resmania
 *
 * This handles all date related issues in the whole application
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */

class RM_Date extends Zend_Date {

    /**
     * This uses the zend framwork date sub function.
     * Good for subtracting a number of days from a date.
     *
     * @param  	string	$datestring MySQL formatted Date String Y-m-d.
     * @param  	string	number of days to be subtracted.
     * @return 	string	result date formatted as a MySQL Date Y-m-d.
     */
    public function dateSub($datestring, $numberofdays = 1) {

        $dateEl = explode("-", $datestring);
        $datearray = array('year' => date($dateEl[0]), 'month' => date($dateEl[1]), 'day' => date($dateEl[2]));
        $rmdate = new Zend_Date($datearray);
        $rmdate->sub($numberofdays, Zend_Date::DAY);
        $dateVal = $rmdate->toString('Y-m-d');

        return $dateVal;
    }

    /**
     * This uses the zend framwork date add function.
     * Good for adding a number of days to a date.
     *
     * @param  	string	$datestring MySQL formatted Date String Y-m-d.
     * @param  	string	number of days to be added.
     * @return 	string	result date formatted as a MySQL Date Y-m-d.
     */
    public function dateAdd($datestring, $numberofdays = 1) {

        $dateEl = explode("-", $datestring);
        $datearray = array('year' => date($dateEl[0]), 'month' => date($dateEl[1]), 'day' => date($dateEl[2]));
        $zenddate = new Zend_Date($datearray);
        $zenddate->add($numberofdays, Zend_Date::DAY_SHORT);
        $dateVal = $zenddate->toString('Y-m-d');

        return $dateVal;
    }

    /**
     * Calculate the number of days between to dates
     *
     * @param   string  date1 in mysql format (yyyy-mm-dd)
     * @param   string  date2 in mysql format (yyyy-mm-dd)
     * @return  int
     */
    public function dateDiffCount($date1, $date2) {
        if (!$date1 || !$date2)
            return false;

        $date1El = explode("-", $date1);
        $date1array = array('year' => date($date1El[0]), 'month' => date($date1El[1]), 'day' => date($date1El[2]));
        $date2El = explode("-", $date2);
        $date2array = array('year' => date($date2El[0]), 'month' => date($date2El[1]), 'day' => date($date2El[2]));

        $dateObj1 = new Zend_Date($date1array);
        $dateObj2 = new Zend_Date($date2array);

        $date1U = (int) $dateObj1->get(Zend_Date::TIMESTAMP);
        $date2U = (int) $dateObj2->get(Zend_Date::TIMESTAMP);

        return (int) ($date2U - $date1U / (60 * 60 * 24));
    }

    /**
     * This function will return an array containg all dates from
     * the DateStart to the Number of Days offset.
     *
     * @param  	string	$datestart MySQL formatted Date String Y-m-d.
     * @param  	int	number of days to be offset (-365 will subtract a year).
     * @return 	array	array of all dates in MySQL format.
     */
    public function getDatesArray($datestart, $offsetindays, $step = 1, $order="ASC") {

        if ((int) $offsetindays < 0) {
            // sub dates
            $offsetindays = abs($offsetindays);
            $method = "sub";
        } else {
            $method = "add";
        }

        for ($counter = 0; $counter <= $offsetindays; $counter += $step) {
            if ($method == "sub") {
                $dateArray[] = $this->dateSub($datestart, $counter);
            } else {
                $dateArray[] = $this->dateAdd($datestart, $counter);
            }
        }

        if ($order == "ASC") {
            $dateArray = array_reverse($dateArray);
        }
        return $dateArray;
    }

    /**
     * Convert current date into string in MySQL Datetime format: Y-m-d H:i:s
     *
     * @return string
     */
    public function toMySQL() {
        return $this->toString('Y-m-d H:i:s');
    }

    /**
     * Convert current date into string in GUI Datetime format
     *
     * @return string
     */
    public function toGUI() {

        $RM_Config = new RM_config;
        $dateformat = $RM_Config->getValue('rm_config_dateformat');

        return $this->toString($dateformat);
    }

    /**
     * Rounds down a date string to the nearest month
     *
     * @param   $date   string  mysql date format
     * @param   $precision  string  the precision of the return.
     * @return  string   date string in mysql format
     */
    public function monthRoundDown($date = false) {
        if (!$date) {
            $date = date("Y-m-d H:i:s");
        }
        $parts = explode(" ", $date); // splits date and time
        $dateparts = explode("-", $parts[0]); // date parts
        $timeparts = explode(":", $parts[1]); // time parts

        return date("Y-m-d H:i:s", mktime((int) $timeparts[0], (int) $timeparts[1], (int) $timeparts[2], (int) $dateparts[1], 1, (int) $dateparts[0]));
    }

}