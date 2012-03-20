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
class RM_Form_Validator_Advancedsearch extends RM_Form_Validator {
    /**
     * @param RM_Form_Row $form
     */
    function __construct($form) {
        parent::__construct($form);
    }

    /**
     * Validate information from reservation manager object, 'cause we are on the final step
     * before payment process.
     *
     * @param Zend_Request_Interface $request
     * @return bool
     */
    function validate($request){        
        $result = $this->_validatePrice($request);
        $result &= $this->_validateDates($request);
        return $result;
    }

    /**
     * @param Zend_Request_Interface $request
     * @return bool
     */
    private function _validatePrice($request){
        $values = $request->getParam('search');
        $from = $values['prices_from'];
        $to = $values['prices_to'];

        if ($from > $to && $to !== 0) {
            $this->_errors[] = 'FromShouldBeLowerThanTo';
            return false;
        }

        return true;        
    }

    /**
     * @param Zend_Request_Interface $request
     * @return bool
     */
    private function _validateDates($request){
        $values = $request->getParam('search');
        $start = $values['start_datetime'];
        $end = $values['end_datetime'];        

        if ($start !== null && $end !== null && strtotime($start) > strtotime($end)) {
            $this->_errors[] = 'StartDateShouldBeBeforeEndDate';
            return false;
        }        

        return true;
    }
}