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
 * Admin Language Contoller
 *
 * This handles all AJAX requests from the Admin GUI Language Section.
 * These methods will create an AJAX response containing JSON data. The JSON
 * data is read by the JS code and rendered into interface.
 * 
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_LanguageController extends RM_Language_Controller {

    public function listJsAction() {
        $languageModel = new RM_Languages();
        $languages = $languageModel->fetchAllEnabled();
        foreach ($languages as $language) {
            $jsonLanguages[] = array($language->iso, $language->name);
        }
        return "RM.Languages = " . Zend_Json::encode($jsonLanguages) . ";";
    }

    /**
     * Returns data for the Language List.
     *
     * This method returns the list data in JSON format. This is implicated in
     * lists.js
     *    
     * @return   json    the json data for the list. //It's empty for the moment.
     */
    public function listJsonAction() {
        return array('data' => array('success' => true));
    }

    /**
     * Returns data for the main grid on the language page.
     *
     * This method returns the list data in JSON format. This is implicated in
     * lists.js
     *     
     * @return   json    the json data for the list.
     */
    public function maingridJsonAction() {
        $model = new RM_Languages;

        $languages = $model->fetchAll()->toArray();

        $json = new stdClass;
        $json->total = count($languages);
        $json->data = $languages;

        return array(
            'data' => $json
        );
    }

    /**
     * Returns data for the info grid (grid with language files) on the language page.
     *
     * This method returns the list data in JSON format. This is implicated in
     * lists.js
     *
     * @param    request iso   
     * @return   json    the json data for the list.
     */
    public function infogridJsonAction() {
        $iso = $this->_getParam('iso');
        $files = RM_Language_Manager::$files;

        $jsonFiles = array();
        foreach ($files as $file) {
            $jsonFiles[] = array(
                $file,
                RM_Language_Manager::TYPE_MAIN . ',' . RM_Language_Manager::TYPE_MAIN,
                $iso . ',' . $file . ',' . RM_Language_Manager::TYPE_MAIN . ',' . RM_Language_Manager::TYPE_MAIN
            );
        }

        $extensionFiles = RM_Language_Manager::$extensionFiles;
        $moduleModel = new RM_Modules();
        $modules = $moduleModel->fetchAll();
        foreach ($modules as $module) {
            foreach ($extensionFiles as $file) {
                $jsonFiles[] = array(
                    $file,
                    RM_Language_Manager::TYPE_MODULE . ',' . $moduleModel->get($module->name)->getName(),
                    $iso . ',' . $file . ',' . RM_Language_Manager::TYPE_MODULE . ',' . $module->name
                );
            }
        }

        $pluginModel = new RM_Plugins();
        $plugins = $pluginModel->fetchAll();
        foreach ($plugins as $plugin) {
            foreach ($extensionFiles as $file) {
                $jsonFiles[] = array(
                    $file,
                    RM_Language_Manager::TYPE_PLUGIN . ',' . $pluginModel->get($plugin->name)->getName(),
                    $iso . ',' . $file . ',' . RM_Language_Manager::TYPE_PLUGIN . ',' . $plugin->name
                );
            }
        }

        $json = new stdClass;
        $json->total = count($jsonFiles);
        $json->data = $jsonFiles;

        return array(
            'data' => $jsonFiles
        );
    }

    /**
     * Delete language with all information
     *
     * @param    request iso     
     */
    public function deleteJsonAction() {
        $iso = $this->_getParam('iso');
        $model = new RM_Languages();

        $language = $model->find($iso)->current();
        if ($language == null)
            continue;

        // set back to default language
        $model = new RM_Config;
        $configData = $model->fetchAll();
        foreach($configData as $data){
            if ($data->id =='rm_config_language_default_back'){
                $data->value = "cms";
                $data->save();
            }
            if ($data->id =='rm_config_language_default_front'){
                $data->value = "cms";
                $data->save();
            }
        }

        $manager = new RM_Language_Manager();
        $manager->deleteLanguage($iso);

        return array('data' => array('success' => true));
    }

    /**
     * Enable language
     *
     * @param    request iso     
     */
    public function enableJsonAction() {
        $iso = $this->_getParam('iso');
        $model = new RM_Languages();

        $language = $model->find($iso)->current();
        if ($language == null)
            continue;

        $model->enable($language);

        return array('data' => array('success' => true));
    }

    /**
     * Diable language
     *
     * @param    request iso
     */
    public function disableJsonAction() {
        $iso = $this->_getParam('iso', null);
        $model = new RM_Languages();

        $language = $model->find($iso)->current();
        if ($language == null)
            continue;

        $model->disable($language);

        return array('data' => array('success' => true));
    }

    /**
     * Upload zip archive with all language files and config.ini file and install
     * new language into the system.
     *
     * @param    request file
     */
    public function uploadJsonAction() {
        $manager = new RM_Language_Manager();
        try {
            list($iso, $name) = $manager->upload();
            $manager->installLanguage($iso, $name);
            return array('data' => array('success' => true));
        } catch (RM_Exception $exception) {
            return array('data' => array('success' => false, 'msg' => $exception->getMessage()));
        }
    }

    /**
     * Returns language file content.
     *
     * @param    request iso
     * @param    request filename
     */
    public function editfileJsonAction() {
        $type = $this->_getParam('type');
        $iso = $this->_getParam('iso');
        $filename = $this->_getParam('filename');
        $name = $this->_getParam('name');

        $manager = new RM_Language_Manager();
        $content = $manager->getLanguageFileContent($type, $iso, $filename, $name);

        if ($content === false) {
            return array('data' => '');
        } else {
            return array(
                'data' => $content,
                'encoded' => true
            );
        }
    }

    /**
     * Saving file content that had been edited
     *
     * @param    request iso
     * @param    request filename
     * @param    request content
     */
    public function savefileJsonAction() {
        $type = $this->_getParam('type');
        $iso = $this->_getParam('iso');
        $filename = $this->_getParam('filename');
        $content = $this->_getParam('content');
        $name = $this->_getParam('name');

        $manager = new RM_Language_Manager();
        $result = $manager->saveLanguageFileContent($type, $iso, $filename, $content, $name);
        if ($result === false) {
            return array('data' => array('success' => false));
        } else {
            return array('data' => array('success' => true));
        }
    }

    /**
     * Set language as a default language for the back end.
     *
     * @param    request iso     
     */
    public function setdefaultbacktJsonAction() {
        $iso = $this->_getParam('iso');
        if ($iso == null) {
            return array('data' => array('success' => false));
        }

        $model = new RM_Languages;
        $language = $model->find($iso)->current();
        if ($language == null) {
            return array('data' => array('success' => false));
        }

        $model->setDefaultBack($language);
        return array('data' => array('success' => true));
    }

    /**
     * Set language as a default language for the front end.
     *
     * @param    request iso
     */
    public function setdefaultfrontJsonAction() {
        $iso = $this->_getParam('iso');
        if ($iso == null) {
            return array('data' => array('success' => false));
        }

        $model = new RM_Languages;
        $language = $model->find($iso)->current();
        if ($language == null) {
            return array('data' => array('success' => false));
        }

        $model->setDefaultFront($language);
        return array('data' => array('success' => true));
    }

    /**
     * This clears the language cache
     */
    public function clearcacheJsonAction() {
        $languageManager = new RM_Language_Manager();
        $languageManager->clearCache();

        return array(
            'data' => array('success' => true)
        );
    }

    public function getconstantsAction() {
        ob_clean();
        echo RM_JavaScript_Loader::getConstants();
        die;
    }

}