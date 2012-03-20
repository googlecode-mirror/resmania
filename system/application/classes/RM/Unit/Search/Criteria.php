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
* Search Criteria Class
*
* @access       public
* @author       Rob/Valentin
* @copyright    2011 ResMania Ltd.
* @version      1.2
* @link         http://docs.resmania.com/api/
* @since        06-2011
*/

class RM_Unit_Search_Criteria {
    private $_data;
    private $_maxLimit;
    private $_isLimit;

    public function  __construct($data = array(), $isLimit = false){
        $this->_data = $data;
        $this->_isLimit = $isLimit;
        if ($this->_isLimit) {
            $this->_validateMaximum();
        }
    }

    /**
     * Validate and recalculate 'count' and 'offset' parameters according to
     * licence limitation for current installation.
     *
     * @return true
     */
    private function _validateMaximum(){
        if (isset($this->_data['offset']) == false) {
            $this->_data['offset'] = 0;
        }
        if (isset($this->_data['count']) == false) {
            $this->_data['count'] = $this->_maxLimit;
        }
        if (($this->count + $this->offset) > $this->_maxLimit) {
            if ($this->offset > $this->_maxLimit) {
                $this->_data['offset'] = 0;
                if ($this->count > $this->_maxLimit) {
                    $this->_data['count'] = $this->_maxLimit;
                }
            } else {
                $unitsToShow = $this->_maxLimit - $this->offset;
                if ($this->count > $unitsToShow) {
                    $this->_data['count'] = $unitsToShow;
                }
            }
        }
    }


    public function  __set($name,  $value) {
        $this->_data[$name] = $value;
        if (($name == 'count' || $name == 'offset') && $this->_isLimit) {
            $this->_validateMaximum();
        }
        return true;
    }

    public function  __get($name) {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        } else {
            return null;
        }
    }
}