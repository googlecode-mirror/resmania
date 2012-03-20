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
 * Upgrade Extensions controller
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_System_Upgrade_ExtensionsController extends RM_Controller {

    function disableJsonAction() {
        $extensions = RM_Environment::getInstance()->getOutOfDateExtensions();
        $pluginManager = new RM_Plugin_Manager($this->_translate);
        $pluginModel = new RM_Plugins();
        foreach ($extensions['plugins'] as $pluginName) {
            $plugin = $pluginModel->fetchByName($pluginName);
            try {
                $pluginManager->disable($plugin);
            } catch (Exception $e) {
                //TODO: add message to log that we could not disable this plugin
                continue;
            }
        }

        $moduleManager = new RM_Module_Manager($this->_translate);
        $moduleModel = new RM_Modules();
        foreach ($extensions['modules'] as $moduleName) {
            $module = $moduleModel->fetchByName($moduleName);
            try {
                $moduleManager->disable($module);
            } catch (Exception $e) {
                //TODO: add message to log that we could not disable this module
                continue;
            }
        }

        return array('data' => array('success' => true));
    }

    function renewJsonAction() {
        $extensions = RM_Environment::getInstance()->getOutOfDateExtensions();

        $pluginModel = new RM_Plugins();
        $pluginManager = new RM_Plugin_Manager($this->_translate);
        $failedPlugins = array();
        foreach ($extensions['plugins'] as $pluginName) {
            $plugin = $pluginModel->fetchByName($pluginName);
            try {
                $pluginManager->autoUpgrade($plugin);
            } catch (Exception $e) {
                $failedPlugins[] = $pluginName;
                continue;
            }
        }

        $moduleModel = new RM_Modules();
        $moduleManager = new RM_Module_Manager($this->_translate);
        $failedModules = array();
        foreach ($extensions['modules'] as $moduleName) {
            $module = $moduleModel->fetchByName($moduleName);
            try {
                $moduleManager->autoUpgrade($module);
            } catch (Exception $e) {
                $failedModules[] = $moduleName;
                continue;
            }
        }

        return array('data' => array('success' => true));
    }

    function checkJsonAction() {
        $extensions = RM_Environment::getInstance()->getOutOfDateExtensions();
        $json = new stdClass();
        $json->upgradeExtensionAvailable = (count($extensions['modules']) > 0 || count($extensions['plugins']) > 0);
        $json->upgradeExtensions = array_merge($extensions['modules'], $extensions['plugins']);
        return array(
            'data' => $json
        );
    }

}

