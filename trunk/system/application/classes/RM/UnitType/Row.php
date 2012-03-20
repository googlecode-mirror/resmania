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
 * @access      public
 * @author      Rob/Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */

/**
 * ResMania uses unit types to link price modules, groups, and forms to units
 * each Unit type has it's own unique type id. If a new plugin is created it is
 * important to use a new unique type id value or it will conflict with an existing
 * unit type.
 * 
 * These are stored in the rm_unit_types table
 * 
 * Type IDs List:-
 * Unit Plugin Name     ID      Notes
 * Default              1       This is the core base default unit type
 * Property             2       Built into the core this plugin provides a property type
 * Vehicle              3       
 * Boat                 4
 * Hospitality          5
 * 
 * Some plugins also use their own form panels and feilds, these also must use unique
 * id's and the following lists the form id's assigned to the above Type ID's. These
 * connect the Unit type id to the unit config (GUI configuration).
 * 
 * These are stored in the rm_unit_config table
 * 
 * Type ID          Config ID       Notes
 * 1                1 to 10   
 * 2                1 to 10
 * 3                30 to 50
 * 4                40 to 60
 * 5                70 to 90
 *
 * @example For examples of the type id's and config iD's in used look at the
 * Vehicle and Boat Plugins, these are defined in the install.sql
 */
class RM_UnitType_Row extends RM_Row {
    public function getName($locale = null){
        if ($locale == null) {
            $locale = RM_Environment::getInstance()->getLocale();
        }

        return $this->$locale;
    }
}