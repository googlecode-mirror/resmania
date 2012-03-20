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
class RM_Form_Validator_Unitdetails extends RM_Form_Validator {
    /**
     * @var Zend_Request_Interface
     */
    private $_request;

    /**
     * @param RM_Form_Row $form
     */
    function  __construct($form) {
        parent::__construct($form);
    }

    /**
     * Validate information from request
     *
     * @param Zend_Request_Interface $request
     * @return bool
     */
    function validate($request){
        $this->_request = $request;

        $valid = true;
        $valid &= $this->_validateUnit();
        $valid &= $this->_validatePeriod();
        return $valid;
    }

    function _validatePeriod(){               
        if ($this->_request->getParam('rm_calendar_dates', null) != null){            
            // get the dates from the calendar selection
            $datesString = $this->_request->getParam('rm_calendar_dates');

            $dates = explode(',', $datesString);
            if (is_array($dates) == false || count($dates) == 0){
                $this->_errors[] = 'PeriodSelectionIsWrong';
                return false;
            }
            $startDateMySQL = $dates[0];
            $endDateMySQL = $dates[count($dates) - 1];
        } else {
            // get the saved criteria this is from the search module etc.
            $criteria = RM_Reservation_Manager::getInstance()->getCriteria();
            if ($criteria == null) {
                $this->_errors[] = 'DatesNotSelected';
                return false;
            }
            $startDateMySQL = $criteria->start_datetime;
            $endDateMySQL = $criteria->end_datetime;
        }
        
               
        if ($startDateMySQL == null){
            $this->_errors[] = 'StartDateNotSelected';
            return false;
        }
        if ($endDateMySQL == null) {
            $this->_errors[] = 'EndDateNotSelected';
            return false;
        }
        
        $period = new RM_Reservation_Period(
            new RM_Date(strtotime($startDateMySQL)),
            new RM_Date(strtotime($endDateMySQL))
        );

        $unitModel = new RM_Units();
        $unit = $unitModel->find($this->_request->getParam('unit_id', null))->current();
        if ($unit == null) {
            $this->_errors[] = 'SelectedUnitDoesNotExists';
            return false;
        }

        return $unitModel->isAvailableUnitbyDate($unit, $period);        
    }

    function _validateUnit(){
        $unitModel = new RM_Units();
        $unit = $unitModel->find($this->_request->getParam('unit_id', null))->current();
        if ($unit == null) {
            $this->_errors[] = 'SelectedUnitDoesNotExists';
            return false;
        }
        return true;
    }
}