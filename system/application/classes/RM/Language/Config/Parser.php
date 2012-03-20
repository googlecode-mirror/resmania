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
class RM_Language_Config_Parser {
    /**
     * Parsed ini file into config object
     *
     * @param string $filepath Physical path to ini config file
     * @return RM_Language_Config
     * @throws RM_Exception If ini file have wrong format or validation fails
     */
    function getConfig($filepath)
    {
        $configArray = parse_ini_file($filepath);
        if ($configArray === false) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS);
            throw new RM_Exception($translate->_('RM.Language.Config.Parser', 'InvalidIniFileFormat'));
        }

        $config = new RM_Language_Config();
        foreach ($configArray as $key => $value) {
            $config->$key = $value;
        }        
        
        if ($config->validate() == false){
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS);
            throw new RM_Exception($translate->_('RM.Language.Config.Parser', 'InvalidIniFileFormat'));
        }

        return $config;
    }
}
