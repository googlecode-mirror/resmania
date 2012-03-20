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
 * Class for handle unit perfomance chart
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Chart_Unit_Perfomance {

    private $_step = 30; //In days
    private $_stepsOffset = 12;
    private $_top = 10; //Count of top units that we need to show on the page    

    private function _getDatetimes() {
        $dateFunc = new RM_Date();
        $datetimes = $dateFunc->getDatesArray(
                        date("Y-m-d"),
                        -($this->_stepsOffset * $this->_step),
                        $this->_step
        );
        return $datetimes;
    }

    private function _getUnits() {
        $unitTypeDAO = new RM_UnitTypes();

        $unitsdao = new RM_Units;
        $units = $unitsdao->getAll(new RM_Unit_Search_Criteria);

        return $units;
    }

    /**
     * Return JSON data with series information
     * 
     * @return string
     */
    function getSeries() {
        $units = $this->_getUnits();

        $jsonSeries = array();
        foreach ($units as $unit) {
            $jsonSeries[] = '{
                displayName: "' . $this->_getDisplayName($unit) . '",
                yField: "' . $this->_getYField($unit) . '",
                style: {
                    color: ' . $this->_getColor($unit) . ',
                    size: 1,
                    lineSize: 1
                }
            }';
        }

        $jsonData = '[' . implode(',', $jsonSeries) . ']';
        return $jsonData;
    }

    private function _getDisplayName($unit) {
        return $unit->getId();
    }

    private function _getColor($unit) {
        return "0x" . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
    }

//    private function _getYField($unit){
//        return "unit_".$unit->getId();
//    }

    function getFields() {
        $units = $this->_getUnits();
        $fields = array();
        foreach ($units as $unit) {
            $fields[] = $this->_getYField($unit);
        }
        return "['" . implode("','", $fields) . "', 'date']";
    }

    /**
     * Return JSON data for the chart
     *
     * @return string
     */
    function getData() {
        $config = new RM_Config();

        $units = $this->_getUnits();
        $datetimes = $this->_getDatetimes();
        $reservationDetailsModel = new RM_ReservationDetails;

        // for fusion charts we need xml, so the data needs to be formatted
        // so that the json data can render the xml using an xtemplate.
        // we need:-
        // array dates containing all dates of the period we need to chart.
        // array units containing 'name', 'color', array: 'data' containing the unit reservation count for each period

        $jsonSeries = new stdClass;
        $jsonDates = new stdClass;
        $feildsArray = array();
        $jsonSeriesArray = array();
        $jsonChartDataArray = array();

        $feildsArray[] = 'date';

        // this loop, loops through the units once to get the series data
        foreach ($units as $unit) {

            $feildsArray[] = 'unit' . $unit->getId();
            $jsonSeries->yField = 'unit' . $unit->getId();
            $jsonSeries->displayName = $unit->name;
            $jsonSeries->type = 'line'; // the graph type
            $jsonSeries->style = '{color: 0x' . $unit->color . '}'; // the graph type

            $jsonSeriesArray[] = clone($jsonSeries);
        }

        // this loop, loops through all dates and gets the data for each period
        for ($i = 0; $i < count($datetimes) - 1; $i++) {

            // get the date
            $date = $config->convertDates(
                            $datetimes[$i],
                            RM_Config::PHP_DATEFORMAT,
                            RM_Config::HUMAN_MONTH_DATEFORMAT
            );

            $chartData = array();
            $chartData['date'] = $date;

            // loop through each unit
            foreach ($units as $unit) {

                $SdateParts = explode("-", $datetimes[$i]);
                $startDatetimes = mktime(0, 0, 0, (int) $SdateParts[1], (int) $SdateParts[2], (int) $SdateParts[0]);

                // set the endDatetimes to the last day of the month
                $EdateParts = explode("-", $datetimes[$i + 1]);
                $endDatetimes = mktime(0, 0, 0, (int) $EdateParts[1], (int) $SdateParts[2], (int) $EdateParts[0]);

                //mktime($hour, $minute, $second, $month, $day, $year)

                $period = new RM_Reservation_Period(
                                new RM_Date($datetimes[$i]),
                                new RM_Date($datetimes[$i + 1])
                );

                $count = $reservationDetailsModel->getReservationCount(
                                $unit,
                                $period
                );

                $chartData['unit' . $unit->getId()] = $count;
            }

            //$chartData->units = $dataArray;
            $jsonChartDataArray[] = $chartData;
        }

        $returnData = new stdClass;

        // fields: array containing all the fields ie: 'date', 'unit1', 'unit2', 'unit3'
        //
        // series: array containing objects for each series item:-
        // type: 'line',displayName: 'Unit 1',yField: 'unit1',style: {color:0x99BBE8}
        // note yField = the field name.
        //
        // data should contain the data:-
        // data: [
        //     {date:'Jul 07', unit1: 9, unit2: 11, unit3: 4},
        //     {date:'Aug 07', unit1: 5, unit2: 0, unit3: 3},
        //     {date:'Sep 07', unit1: 4, unit2: 0, unit3: 4},
        //     {date:'Oct 07', unit1: 8, unit2: 5, unit3: 5},
        //     {date:'Nov 07', unit1: 4, unit2: 6, unit3: 6},
        //     {date:'Dec 07', unit1: 0, unit2: 8, unit3: 7},
        //     {date:'Jan 08', unit1: 0, unit2: 11, unit3: 0},
        //     {date:'Feb 08', unit1: 8, unit2: 15, unit3: 7}
        // ]

        $returnData->fields = $feildsArray;
        $returnData->series = $jsonSeriesArray;
        $returnData->data = $jsonChartDataArray;

        return $returnData;
    }

}
