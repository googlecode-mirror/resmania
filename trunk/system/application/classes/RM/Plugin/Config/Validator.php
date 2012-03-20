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
class RM_Plugin_Config_Validator {
    function validate(RM_Plugin_Config $config)
    {
        if (isset ($config->information['name']) === false) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS);
            throw new RM_Exception($translate->_('RM.Plugin.Config.Validator', 'NoPluginName'));
        }

        //TODO: checkk all files that needed in zip folder
        //SQL install
        //SQL uninstall
        //Main class

        return true;
    }
}
