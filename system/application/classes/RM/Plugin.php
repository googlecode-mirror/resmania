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
 * class to remove useless method from many other classes
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */

abstract class RM_Plugin extends RM_Extension implements RM_Plugin_Interface, RM_Admin_Menu_Interface
{
    /**
     * This method will return node object for main admin menu tree.
     * Every child classes could overload this method to return any of the node object.
     * If there is no need to present a plugin in the main admin tree overloaded method should return NULL
     *
     * @return stdClass | null
     */
    public function getNode()
    {
        $std = new stdClass;
        $std->id = $this->name.'_EditJson';
        $std->text = $this->getName();
        $std->leaf = 'true';
        $std->iconCls = 'RM_plugins_leaf_icon';
        return $std;
    }

    /**
     * This method will return node object for configuration admin menu tree.
     * Every child classes could overload this method to return any of the node object.
     * If there is no need to present a plugin in the configuration admin tree overloaded method should return NULL
     *
     * @return stdClass | null
     */
    public function getConfigNode()
    {
        $std = new stdClass;
        $std->id = $this->name.'_ConfigJson';
        $std->text = $this->getName();
        $std->leaf = 'true';
        $std->iconCls = 'RM_plugins_config_leaf_icon';
        return $std;
    }

    public function addLanguage($iso)
    {
        /*
        $languageModel = new RM_Languages();
        $defaultLocale = $languageModel->getDefaultLocale();

        $languageFolder = implode(DIRECTORY_SEPARATOR, array(
            RM_Environment::getConnector()->getRootPath(),
            'RM',
            'userdata',
            'plugins',
            $this->name,
            'languages'
        ));

        $defaultLanguageFolder = $languageFolder.DIRECTORY_SEPARATOR.$defaultLocale;
        $newLanguageFolder = $languageFolder.DIRECTORY_SEPARATOR.$iso;

        $fileSystem = new RM_Filesystem();
        return $fileSystem->recursivecopy($defaultLanguageFolder, $newLanguageFolder);         
         */
    }

    public function deleteLanguage($iso)
    {
        $languageFolder = implode(DIRECTORY_SEPARATOR, array(
            RM_Environment::getConnector()->getRootPath(),
            'RM',
            'userdata',
            'plugins',
            $this->name,
            'languages',
            $iso
        ));
        RM_Filesystem::deleteFolder($languageFolder);
    }
    
    public function getDatabaseRow()
    {
        $pluginModel = new RM_Plugins();
        return $pluginModel->fetchByName($this->name);
    }

    public function upgrade(){}

    public function install(){}

    public function uninstall() {
        $pluginsModel = new RM_Plugins();
        $plugin = $pluginsModel->find($this->name)->current();
        if ($plugin !== null) {
            $dependenciesModel = new RM_Dependencies();
            $dependencies = $dependenciesModel->getDependencies($plugin);
            foreach ($dependencies as $dependency){
                $dependency->delete();
            }
        }        
    }
}