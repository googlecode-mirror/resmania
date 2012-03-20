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
class RM_Module_Config_Parser {
    private $_config = null;

    /**
     * Parsed ini file into config object
     *
     * @param string $filepath Physical path to ini config file
     * @return RM_Module_Config
     * @throws RM_Exception If ini file have wrong format or validation fails
     */
    function getConfig($filepath)
    {
        $configArray = parse_ini_file($filepath, true);
        if ($configArray === false) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS);
            throw new RM_Exception($translate->_('RM.Module.Config.Parser', 'InvalidIniFileFormat'));
        }

        $this->_config = new RM_Module_Config();
        $methods = get_class_methods(get_class($this));
        foreach ($configArray as $section => $values) {
            //We use something like a reflection to invoke a needed method to parse current section
            $parseMethodName = '_parse'.ucfirst($section);

            if (in_array($parseMethodName, $methods) == false ) {
                throw new RM_Exception($translate->_('RM.Module.Config.Parser', 'WrongIniFileSectionName').':'.$section);
            } 
            
            $sectionArray = $this->$parseMethodName($values);            
            $this->_config->$section = $sectionArray;
        }

        $validator = new RM_Module_Config_Validator();
        try {
            $result = $validator->validate($this->_config);
        } catch (RM_Exception $e) {
            throw new RM_Exception($e->getMessage());
        }
        
        return $this->_config;
    }

    /**
     * Parse 'information' section
     *
     * @param array $values values from ini config file in 'information' section
     * @return array
     */
    private function _parseInformation($values){
        $sectionArray = $this->_config->information; //Some of our information variables have default values
        foreach ($values as $key => $value) {
            $sectionArray[$key] = $value;
        }
        return $sectionArray;
    }

    /**
     * Parse 'dependencies' section
     *
     * @param array $values values from ini config file in 'dependencies' section
     * @return array
     */
    private function _parseDependencies($values){
        if (isset($this->_config->information['name']) == false) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
            throw new RM_Exception($translate->_('Admin.Dependencies', 'InformationNameShouldGoFirst'));
        }

        $sectionArray = array();
        foreach ($values as $value) {
            $sectionArray[] = $this->_parseDependency($value);
        }
        return $sectionArray;
    }

    /**
     * Parsed dependency string from config ini file into array
     * string need to be in the format:
     * {parent_type}{parent_name};{parent_value}
     *
     * @param string $value
     * @return <type>
     */
    private function _parseDependency($value){
        $sections = explode(';',$value);               
        $dependency = array(            
            'child_type' => RM_Module_Row::DEPENDENCY_TYPE,
            'child_name' => $this->_config->information['name'],
            'parent_type' => $sections[0],
            'parent_name' => $sections[1],            
            'parent_value' => $sections[2]
        );

        return $dependency;
    }
}
