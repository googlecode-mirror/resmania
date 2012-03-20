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
* Admin Menu Controller
*
* This provides the data behind the admin tree menu.
*
* @access       public
* @author       Rob
* @copyright    2011 ResMania Ltd.
* @version      1.2
* @link         http://docs.resmania.com/api/
* @since        06-2011
*/

class RM_Admin_MenuController extends RM_Controller {
    public function treeJsonAction() {
        return $this->_default();
    }

    /*
     * Builds the Data used to construct the menu tree
     *
     * the id property is used to call the JS function. This represents the 
     * controller and action ie: 
     * Reservations_ListJson is controller=Reservations / action = ListJson 
     * This is handled by the treePanel.on event in layout.js and chucks the
     * id by '_' then assembles the JS function name ie:
     * Reservations_ListJson would be changed to represent
     * RM.Pages.Functions.Reservations_ListJson
     *
     * @return 	array   an array used to build the menu tree
     */
    private function _default() {
        $model = new RM_Reservations();
        $UnReadCount = $model->getUnreadCount();
        if ($UnReadCount>0) {
            $UnReadCount = "&nbsp;<span class='RM_Menu_Badge_Count'><div class='RM_Menu_Badge_Count_text'>".$UnReadCount."</div></span><span class='RM_Menu_Badge_Count_right'></span>";
        } else {
            $UnReadCount = "";
        }

        $home = array();

        $console = new stdClass;
        $console->text = $this->_translate->_('Home');
        $console->id = 'Index_HomepageJson';
        $console->leaf = 'false';
        $console->iconCls = 'RM_home_icon';
        $home[] = $console;

        $reservations = new stdClass;
        $reservations->text = $this->_translate->_('Reservations').$UnReadCount;
        $reservations->id = 'Reservations_ListJson_NoAjax';
        $reservations->leaf = 'true';
        $reservations->iconCls = 'RM_reservations_root_icon';
        $home[] = $reservations;

        $customers = new stdClass;
        $customers->text = $this->_translate->_('Users');
        $customers->id = 'Users_ListJson_NoAjax';
        $customers->leaf = 'true';
        $customers->iconCls = "RM_users_root_icon";
        $home[] = $customers;

        // units
        $config = new RM_Config();
        $showUnitsOnMenu = $config->getValue("rm_config_enable_units_on_treemenu");


        $units = new stdClass;
        $units->text = $this->_translate->_('Units');
        $units->id = 'Units_ListJson_NoAjax';

        $unitRows = $this->_getUnits();
        if (count($unitRows) > 0 && $showUnitsOnMenu === "1") {
            $units->expanded = false;
            $units->children = $unitRows;
        } else {
            $units->leaf = true;
        }
        $units->iconCls = "RM_units_default_root_icon";
        $home[] = $units;

        $system = new stdClass;
        $system->text = $this->_translate->_('System');
        $system->id = 'system';
        $system->disabled = true;
        $system->expanded = 'true';
        $system->iconCls = "RM_system_root_icon";
        $system->children = array();
        $home[] = $system;

        $mediaManager = new stdClass;
        $mediaManager->text = $this->_translate->_('MediaManager');
        $mediaManager->id = 'System_MediaManagerJson';
        $mediaManager->leaf = 'true';
        $mediaManager->iconCls = "RM_system_mediamanager_icon";
        $system->children[] = $mediaManager;

        $modules = new stdClass;
        $modules->text = $this->_translate->_('Modules');
        $modules->disabled = true;
        $modules->id = 'Modules_InfoJson';
        $modulesEnabled = $this->_getModules();
        if (count($modulesEnabled) > 0) {
            $modules->expanded = 'true';
            $modules->children = $modulesEnabled;
        } else {
            $modules->leaf = 'true';
        }
        $modules->iconCls = "RM_config_modules_icon";
        $home[] = $modules;

        $plugins = new stdClass;
        $plugins->text = $this->_translate->_('Plugins');
        $plugins->disabled = true;
        $plugins->id = 'Plugins_InfoJson';
        $pluginsEnabled = $this->_getPlugins();
        if (count($pluginsEnabled) > 0) {
            $plugins->expanded = 'true';
            $plugins->children = $pluginsEnabled;
        } else {
            $plugins->leaf = 'true';
        }
        $plugins->iconCls = "RM_config_plugins_icon";
        $home[] = $plugins;

        $misc = new stdClass;
        $misc->text = $this->_translate->_('Misc');
        $misc->id = 'misc';
        $misc->iconCls = "RM_misc_root_icon";
        $misc->expanded = 'true';
        $misc->children = array();

        $configuration = new stdClass;
        $configuration->text = $this->_translate->_('Configuration');
        $configuration->id = 'Config_EditJson';
        $configuration->expanded = 'true';
        $configuration->iconCls = "RM_config_root_icon";
        $configuration->children = array();

        $languages = new stdClass;
        $languages->text = $this->_translate->_('Languages');
        $languages->id = 'Language_ListJson_NoAjax';
        $languages->leaf = 'true';
        $languages->iconCls = "RM_config_languages_icon";
        $configuration->children[] = $languages;

        $pagesList = array();
        $page = new stdClass;
        $page->text = $this->_translate->_('Common.Invoice', 'Invoice');
        $page->id = 'Pages_EditJson_admin.scripts.templates.invoice';
        $page->leaf = 'true';
        $page->iconCls = "RM_config_pages_leaf_icon";
        $pagesList[] = $page;                   

        $pages = new stdClass;
        $pages->text = $this->_translate->_('Pages');
        $pages->disabled = true;
        $pages->expanded = true;
        $pages->children = $pagesList;
        $pages->iconCls = "RM_config_pages_icon";
        $configuration->children[] = $pages;

        $templatesList = $this->_getTemplates();
        $templates = new stdClass;
        $templates->text = $this->_translate->_('Templates');
        $templates->disabled = true;
        if (count($pluginsEnabled) > 0) {
            $templates->expanded = true;
            $templates->children = $templatesList;
        } else {
            $plugins->leaf = true;
        }                
        $templates->iconCls = "RM_config_templates_icon";
        $configuration->children[] = $templates;        

        $modules = new stdClass;
        $modules->text = $this->_translate->_('Modules');
        $modules->id = 'Modules_ListJson_NoAjax';
        $modulesEnabled = $this->_getModulesConfig();
        if (count($modulesEnabled) > 0) {
            $modules->expanded = 'true';
            $modules->children = $modulesEnabled;
        } else {
            $modules->leaf = 'true';
        }
        $modules->iconCls = "RM_config_modules_icon";
        $configuration->children[] = $modules;

        $plugins = new stdClass;
        $plugins->text = $this->_translate->_('Plugins');
        $plugins->id = 'Plugins_ListJson_NoAjax';
        $pluginsEnabled = $this->_getPluginsConfig();
        if (count($pluginsEnabled) > 0) {
            $plugins->expanded = 'true';
            $plugins->children = $pluginsEnabled;
        } else {
            $plugins->leaf = 'true';
        }
        $plugins->iconCls = "RM_config_plugins_icon";
        $configuration->children[] = $plugins;
        $home[] = $configuration;

        $upgrade = new stdClass;
        $upgrade->text = $this->_translate->_('Upgrade');
        $upgrade->id = 'System_UpgradeJson_NoAjax';
        $upgrade->leaf = 'true';
        $upgrade->iconCls = "RM_config_upgrade_icon";
        $configuration->children[] = $upgrade;

        $help = new stdClass;
        $help->text = $this->_translate->_('Help');
        $help->id = 'help';
        $help->disabled = true;
        $help->expanded = 'true';
        $help->iconCls = "RM_help_root_icon";
        $help->children = array();

        $documentation = new stdClass;
        $documentation->text = $this->_translate->_('Documentation');
        $documentation->id = 'documenatation';
        $documentation->external = true;
        $documentation->leaf = 'true';        
        $documentation->url = 'http://docs.resmania.com/';
        $documentation->iconCls = "RM_help_docs_icon";
        $help->children[] = $documentation;

        $systemLog = new stdClass;
        $systemLog->text = $this->_translate->_('SystemLog');
        $systemLog->id = 'system_log';
        $systemLog->external = true;
        $systemLog->leaf = 'true';
        $systemLog->url = '../RM/userdata/logs/system_log.txt';
        $systemLog->iconCls = "RM_help_docs_icon";
        $help->children[] = $systemLog;

        $licensing = new stdClass;
        $licensing->text = $this->_translate->_('License');
        $licensing->id = 'license';
        $licensing->external = true;
        $licensing->leaf = 'true';
        $licensing->url = 'http://resmania.com/gnu-gpl-license';
        $licensing->iconCls = 'RM_help_license_icon';
        $help->children[] = $licensing;

        $home[] = $help;

        return array(
        'data' => $home
        );
    }

    private function _getModulesConfig() {
        $model = new RM_Modules();
        $modules = $model->getAllEnabled();

        $result = array();
        foreach ($modules as $module) {
            $node = $module->getConfigNode();
            if ($node !== null) {
                $result[] = $node;
            }
        }

        return $result;
    }

    private function _getModules() {
        $model = new RM_Modules();
        $modules = $model->getAllEnabled();

        $result = array();
        foreach ($modules as $module) {
            $node = $module->getNode();
            if ($node !== null) {
                $result[] = $node;
            }
        }

        return $result;
    }

    private function _getPluginsConfig() {
        $model = new RM_Plugins();
        $plugins = $model->getAllEnabled();

        $result = array();
        foreach ($plugins as $plugin) {
            $node = $plugin->getConfigNode();
            if ($node !== null) {
                $result[] = $node;
            }
        }

        return $result;
    }

    private function _getPlugins() {
        $model = new RM_Plugins();
        $plugins = $model->getAllEnabled();

        $result = array();
        foreach ($plugins as $plugin) {
            $node = $plugin->getNode();
            if ($node !== null) {
                $result[] = $node;
            }
        }

        return $result;
    }

    private function _getTemplates()
    {
        $templateModel = new RM_Templates();
        $templates = $templateModel->fetchAll();

        $templateList = array();
        foreach ($templates as $template) {
            $row = new stdClass;
            $row->text = $this->_translate->_('Admin.Templates', $template->id);
            $row->id = 'Templates_EditJson_'.$template->id;
            $row->leaf = true;
            $row->iconCls = "RM_config_templates_leaf_icon";
            $templateList[] = $row;
        }

        return $templateList;
    }

    private function _getUnits() {
        $dao = new RM_Units;
        $criteria = new RM_Unit_Search_Criteria(
            array('language' => RM_Environment::getInstance()->getLocale()),
            true
        );
        $units = $dao->getAll($criteria);
        $result = array();

        if (count($units->toArray()) === 0) { return null; }

        // get all the master group units
        $count = 1;  // the menu id
        $groupTracker = array();
        foreach ($units as $unitRow) {
            
            $unit = $unitRow->toArray();

            if (isset($unit['group_id'])){
                if ($unitRow->isTemplateUnit() === (int)$unit['unit_id']){

                    // template units (root units)
                    $std = new stdClass;
                    $std->id = 'Units_EditJson_'.$unit['id'];
                    $std->text = $unit['name'];
                    $std->expanded = 'true';
                    $std->iconCls =  'RM_units_'.$unit['type_id'].'_leaf_icon';
                    $std->children = array();
                    $result[$count] = $std;

                    $groupTracker[$count]  = $unit['group_id'];
                    $count +=1;
               }
            } else {

                // this is not a grouped unit - this is the standard unit (not grouped)
                $std = new stdClass;
                $std->id = 'Units_EditJson_'.$unit['id'];
                $std->text = $unit['name'];
                $std->leaf = 'true';
                $std->iconCls = 'RM_units_'.$unit['type_id'].'_leaf_icon';
                $result[$count] = $std;

                $groupTracker[$count]  = 0;
                $count +=1;
            }
        }

        // now loop through all units and assign sub units to the template units
        // but only if they are a sub unit.
        foreach ($units as $unitRow) {
            $unit = $unitRow->toArray();

            // handle groups
            if (isset($unit['group_id'])){
                if ($unitRow->isTemplateUnit() !== (int)$unit['unit_id']){
                    // sub unit
                    $childUnit = new stdClass;
                    $childUnit->id = 'Units_EditJson_'.$unit['id'];
                    $childUnit->text = $unit['name'];
                    $childUnit->leaf = 'true';
                    $childUnit->iconCls = 'RM_units_'.$unit['type_id'].'_leaf_icon';

                    // add it to the parent leaf
                    foreach ($groupTracker as $key=>$value){
                        if ($unit['group_id'] == $value){
                            $result[$key]->children[] = $childUnit;
                        }
                    }
                }
            } 
        }

        // we have to resort here as the menu needs consecutive numbering
        foreach($result as $value){
            $final[] = $value;
        }

        return $final;
    }

    public function unitmenuJsonAction(){
        return array(
        'data' => $this->_getUnits()
        );
    }
}