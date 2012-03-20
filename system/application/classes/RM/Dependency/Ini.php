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
 * PHP ini dependency.
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Dependency_Ini extends RM_Dependency_Abstract {
    private $_value = null;

    /**
     * Constructor     
     * @param RM_Dependency_Row $row
     */
    private function  __construct($row) {
        parent::_init($row);
        
        $value = explode(',', $databaseRow->value);
        $this->_setValue($value);
    }    

    /**
     * @see RM_Dependency_Abstract::validate method
     * @return bool
     */
    public function validate() {
        $iniValue = ini_get($this->_getName());
        $value = $this->_getValue();
        return is_array($value) ? in_array($iniValue, $value) : ($iniValue == $value);
    }

    /**     
     * @param string|array $value
     */
    private function _setValue($value) {
        $this->_value = $value;        
    }

    /**
     * @return string|array
     */
    private function _getValue() {
        return $this->_value;
    }

    /**
     * @return string
     */
    public function __toString() {        
        $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
        $value = $this->_getValue();
        if (is_array($value)){
            return sprintf($translate->_('Admin.Dependency', 'INIDependencyMultiple'), $this->_getName(), implode(", ", $value));
        } else {
            return sprintf($translate->_('Admin.Dependency', 'INIDependency'), $this->_getName(), $value);
        }               
    }
}