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
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Dependency_Module extends RM_Dependency_Abstract {
    /**
     * @var string
     */
    private $_version = null;

    /**     
     * @param Zend_Db_Table_Row $row
     */
    public function  __construct($row) {
        parent::_init($row);
        $this->_setVersion($row->parent_value);
    }

    /**
     * @see RM_Dependency_Abstract::validate method
     * @return bool
     */
    public function validate() {        
        //1. check if module is installed
        $moduleClassName = 'RM_Module_'.$this->_getName();
        if (class_exists($moduleClassName) == false){
            return false;
        }

        //2. check if module is enabled
        $moduleModel = new RM_Modules();
        $moduleRow = $moduleModel->fetchByName($this->_getName());
        if ($moduleModel->isEnabled($moduleRow) == false) {
            return false;
        }

        //3. check it's min version
        $minModuleVersion = $this->_getVersion();
        $moduleVersion = $moduleRow->version;
        if (version_compare($moduleVersion, $minModuleVersion, '<')) {
            return false;
        }

        return true;
    }

    /**
     * @param string $version
     */
    private function _setVersion($version) {
        $this->_version = $version;        
    }

    /**
     * @return string
     */
    private function _getVersion() {
        return $this->_version;
    }

    /**
     * @return string
     */
    public function __toString() {
        $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
        return sprintf($translate->_('Admin.Dependency', 'ModuleDependency'), $this->_getName(), $this->_getVersion());
    }
}