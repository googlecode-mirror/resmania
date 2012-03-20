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
 * Class for handle all manipulations with software config
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Config extends RM_Model {

    protected $_name = 'rm_config';
    //protected $_rowClass = 'RM_Config_Field';

    const PHP_DATEFORMAT = 'Y-m-d';
    const MYSQL_DATEFORMAT = 'Y-m-d H:i:s';
    const MYSQL_DATEFORMAT_SHORT = 'Y-m-d';
    const TIMESTAMP_DATEFORMAT = 'U';
    const JS_DATEFORMAT = '__JS__';
    const HUMAN_MONTH_DATEFORMAT = 'M y';

    function getResmaniaEmail() {
        return $this->getValue('rm_config_resmania_email');
    }

    function getResmaniaPassword() {
        return $this->getValue('rm_config_resmania_password');
    }

    /**
     * Convert date with one format to another format
     *
     * @param string $date
     * @param string $currentFormat current date format
     * @param string $neededFormat format we need to convert to
     * @return string date string in needed format
     */
    public function convertDates($date, $currentFormat, $neededFormat) {
        if ($currentFormat == RM_Config::JS_DATEFORMAT) {
            $currentFormat = $this->getJSDateformat();
        }

        if ($currentFormat == RM_Config::HUMAN_MONTH_DATEFORMAT) {
            $currentFormat = $this->getJSDateformat();
        }

        if ($neededFormat == RM_Config::JS_DATEFORMAT) {
            $neededFormat = $this->getJSDateformat();
        }

        if ($neededFormat == RM_Config::HUMAN_MONTH_DATEFORMAT) {
            $neededFormat = "M y";
        }

        return date($neededFormat, strtotime($date));
        /*
          $currentDate = new RM_Date($date, $currentFormat);
          return $currentDate->toString($neededFormat);
         */
    }

    /**
     * Return JS date format
     *
     * Returns a date format to use in the GUI for display.
     *
     *
     * @param   sting   return format, short (d/m/Y) or long (dd/mm/yyyy)
     * @return  string  JS dateformat
     */
    public function getJSDateformat($format = "short") {
        $value = $this->get('rm_config_dateformat');

        if ($format == "short") {
            return $value['rm_config_dateformat'];
        } else {
            if ($value['rm_config_dateformat'] == "d/m/Y") {
                return "dd/mm/yyyy";
            } else {
                return "mm/dd/yyyy";
            }
        }
    }

    /**
     * This gets and returns the image configuration settings.
     *
     * @deprecated use RM_Media_Image::get(<type>) instead
     * @return array    containing image settings
     */
    public function getImageConfig() {
        $configData = $this->fetchAll();

        $imagesettings = array();
        foreach ($configData as $row) {
            switch ($row->id) {
                case 'rm_config_image_thumb_settings_y_res': $imagesettings['thumbnail_x'] = $row->value;
                    break;
                case 'rm_config_image_thumb_settings_x_res': $imagesettings['thumbnail_y'] = $row->value;
                    break;
                case 'rm_config_image_thumb_settings_quality': $imagesettings['thumbnail_quality'] = $row->value;
                    break;
                case 'rm_config_image_settings_x_res': $imagesettings['largeimage_x'] = $row->value;
                    break;
                case 'rm_config_image_settings_y_res': $imagesettings['largeimage_y'] = $row->value;
                    break;
                case 'rm_config_image_settings_quality': $imagesettings['largeimage_quality'] = $row->value;
                    break;
            }
        }

        return $imagesettings;
    }

    /**
     * gets and returns configuration info as an array
     *
     * @todo $filter need to be an array with values with row ids. (valentin)
     * @param   string  $filter     string of config values to return
     * @return array    containing all configuration info
     */
    public function get($filter) {

        $configData = $this->fetchAll()->toArray();

        $settings = array();
        foreach ($configData as $row) {
            if (substr_count($filter, $row['id']) == 1) {
                $settings[$row['id']] = $row['value'];
            }
        }

        return $settings;
    }

    /**
     * Returns config value by it's name
     *
     * @param string $name
     * @return string
     */
    public function getValue($name) {
        $row = $this->find($name)->current();
        if ($row == null) {
            return null;
        }
        return $row->value;
    }

    /**
     * Returns current active currency symbol
     *
     * @return char
     */
    public function getCurrencySymbol() {
        return $this->getValue('rm_config_currency_symbol');
    }

}