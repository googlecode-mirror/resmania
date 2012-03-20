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
 * Class for loading location names depended on selected locale
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Locations_Loader {
    private static $_locations = null;

    /**
     * Returns all countries and cities in this format
     * <ISO country code> => array(
     *  'iso' => 'country_ico'
     *  'name' => 'country_name'
     *  'cities' => array(
     *   'pk' => 'city_name', //'pk' = en cityname, 'cityname' = name of city in selected locale
     *   ...
     *  )
     * )
     * ...
     *
     * @param $locale string - iso name of the locale
     * @return array or null, null if there is no such language file.
     */
    public static function getAll($locale = null){
        if ($locale == null) {
            $locale = RM_Environment::getInstance()->getLocale();
        }

        if (self::$_locations[$locale] == null){
            $locations = self::_load($locale);
            if (!$locations) {
                return null;
            }
            self::$_locations[$locale] = $locations;
        }
        return self::$_locations[$locale];
    }

    private static function _load($locale){
        $filename = RM_Environment::getConnector()->getCorePath().DIRECTORY_SEPARATOR.
            'userdata'.DIRECTORY_SEPARATOR.
            'languages'.DIRECTORY_SEPARATOR.
            $locale.DIRECTORY_SEPARATOR.
            "locations.ini";

        if (!is_file($filename)) {
            return false;
        }

        $locations = parse_ini_file($filename, true);
        $result = array();
        foreach ($locations as $isoName => $cities) {
            list($iso, $name) = explode('.', $isoName);

            $result[$iso]['iso'] = $iso;
            $result[$iso]['name'] = $name;
            $result[$iso]['cities'] = array();
            $result[$iso]['cities'] = $cities;
        }
        return $result;
    }
}