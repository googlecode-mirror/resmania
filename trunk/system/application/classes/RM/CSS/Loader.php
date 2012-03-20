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
 * ResMania Extension CSS loader.
 *
 * This class combines the module and plugin css
 * into one file. The main reason for this is MS IE support as IE has a 31 css include
 * limit. But this also optimises the load time.
 *
 * @access      public
 * @author      Rob/Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 * @todo        add compressing of the produced combined CSS files. Currently these are very small
 * so the priority is very low.
 */
class RM_CSS_Loader {

    /**
     * Get the modules CSS and combine these
     */
    public function getModuleCSS() {

        $cacheDir = RM_Environment::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'css';
        if (is_dir($cacheDir) == false) {
            $rmConfig = new RM_Config();
            $chmodOctal = intval($rmConfig->getValue('rm_config_chmod_value'), 8);
            mkdir($cacheDir, $chmodOctal);
        }

        $cachedCSS = file_exists($cacheDir . DIRECTORY_SEPARATOR . "modules.css");
        if (!$cachedCSS) {

            $newfileContents = "";

            $moduleDAO = new RM_Modules();
            $modules = $moduleDAO->fetchAllEnabled();
            $config = new RM_Module_Config();
            foreach ($modules as $module) {
                $files = RM_Module_Manager::getCssFiles($module->name);
                foreach ($files as $file) {

                    // read in the css and change the relative paths then add to a cached css file that we will load

                    $fileContents = file_get_contents(RM_Environment::getConnector()->getRootURL() . "RM/userdata/modules/" . $module->name . "/" . RM_Module_Config::CSS . '/' . $file);

                    $search = "/url\(([^\)]*)\)/";
                    $replace = "url(../../../userdata/modules/" . $module->name . "/images/$1)";

                    $newfileContents .= preg_replace($search, $replace, $fileContents);
                }
            }

            $this->_saveCachedCSS($newfileContents, $cacheDir . DIRECTORY_SEPARATOR . "modules.css");
        }

        // return the cached CSS url so it can be included
        return RM_Environment::getConnector()->getRootURL() . "RM/userdata/temp/css/modules.css";
    }

    /**
     * Get the plugins CSS and combine these
     */
    public function getPluginCSS() {



        $cacheDir = RM_Environment::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'css';
        if (is_dir($cacheDir) == false) {
            $rmConfig = new RM_Config();
            $chmodOctal = intval($rmConfig->getValue('rm_config_chmod_value'), 8);
            mkdir($cacheDir, $chmodOctal);
        }

        $cachedCSS = file_exists($cacheDir . DIRECTORY_SEPARATOR . "plugins.css");
        if (!$cachedCSS) {

            $newfileContents = "";

            $pluginsDAO = new RM_Plugins();
            $plugins = $pluginsDAO->fetchAllEnabled();
            $config = new RM_plugin_Config();
            foreach ($plugins as $plugin) {
                $files = RM_plugin_Manager::getCssFiles($plugin->name);
                foreach ($files as $file) {

                    // read in the css and change the relative paths then add to a cached css file that we will load

                    $fileContents = file_get_contents(RM_Environment::getConnector()->getRootURL() . "RM/userdata/plugins/" . $plugin->name . "/" . RM_Module_Config::CSS . '/' . $file);

                    $search = "/url\(([^\)]*)\)/";
                    $replace = "url(../../../userdata/plugins/" . $plugin->name . "/images/$1)";

                    $newfileContents .= preg_replace($search, $replace, $fileContents);
                }
            }

            $this->_saveCachedCSS($newfileContents, $cacheDir . DIRECTORY_SEPARATOR . "plugins.css");
        }

        // return the cached CSS url so it can be included
        return RM_Environment::getConnector()->getRootURL() . "RM/userdata/temp/css/plugins.css";
    }

    /*
     * Save the Module CSS cache
     *
     * @param   $newfileContents  string  file to write contents
     * @param   $cacheName  string  file to write
     */

    private function _saveCachedCSS($newfileContents, $cacheName) {

        $compressedCSS = $this->_compressCSS($newfileContents);

        $f = fopen($cacheName, 'w');
        fwrite($f, $compressedCSS, strlen($compressedCSS));
        fclose($f);
    }

    /**
     * compress the CSS
     *
     * @params  $buffer string  file contents
     * @return  string  compressed css
     */
    private function _compressCSS($buffer) {
        // remove comments
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        // remove tabs, spaces, newlines, etc.
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
        return $buffer;
    }

    /**
     * Creates the unit CSS for the calendar rendering
     * This is just generated at boot
     */
    public static function createUnitColorCSS() {

        $unitModel = new RM_Units();
        $units = $unitModel->getAll(new RM_Unit_Search_Criteria());

        $html = "";
        foreach ($units as $unit) {
            if (isset($unit->color) && $unit->color !== "") {
                $color = $unit->color;
            } else {
                $color = '0033FF';
            }
            $html .= "
                .x-cal-$unit->id,
                .x-cal-$unit->id-x .ext-cal-evb,
                .ext-ie .x-cal-$unit->id-ad,
                .ext-opera .x-cal-$unit->id-ad {
                    color: #$color;
                }
                .ext-cal-day-col .x-cal-$unit->id,
                .ext-dd-drag-proxy .x-cal-$unit->id,
                .x-cal-$unit->id-ad,
                .x-cal-$unit->id-ad .ext-cal-evm,
                .x-cal-$unit->id .ext-cal-picker-icon,
                .x-cal-$unit->id-x dl,
                .x-calendar-list-menu li em .x-cal-$unit->id {
                    background: #$color;
                }
            ";
        }
        return $html;
    }

}