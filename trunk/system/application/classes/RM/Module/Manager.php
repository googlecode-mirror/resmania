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
 * Class for module management
 *
 * This handles all language related actions
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2.1
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Module_Manager extends RM_Extension_Manager implements RM_Multilingual_Interface {

    /**
     * Translage object
     *
     * @var Zend_Translate
     */
    protected $_translate;
    /**
     * INI configuration file name
     *
     * @var string
     */
    protected static $_iniFilename = 'config.ini';

    /**
     * @param Zend_Translate $translate
     */
    function __construct($translate = null) {
        if ($translate == null) {
            $translate = RM_Environment::getInstance()->getTranslation();
        }
        $this->_translate = $translate;
    }

    /**
     * Add language
     *
     * @param string $iso ISO language code
     */
    public function addLanguage($iso) {
        $modules = RM_Module_Manager::getModules();
        foreach ($modules as $module) {
            $module->addLanguage($iso);
        }
    }

    /**
     * Delete languages
     *
     * @param string $iso ISO language code     
     */
    public function deleteLanguage($iso) {
        $modules = RM_Module_Manager::getModules();
        foreach ($modules as $module) {
            $module->deleteLanguage($iso);
        }
    }

    /**
     * Returns module object by module name
     *
     * @param string $name
     * @return RM_Module
     */
    public static function getModule($name) {
        $moduleClassName = self::_getClassname($name);
        return new $moduleClassName();
    }

    /**
     * Return all module objects
     *
     * @todo RM_Modules has the very similar method: getAll. "In the end, there can be only one."
     * @return array - array with modules
     */
    public static function getModules() {
        $model = new RM_Modules;

        $modules = $model->fetchAll();

        $moduleList = array();
        foreach ($modules as $module) {
            $moduleClassName = self::_getClassname($module->name);
            $moduleList[] = new $moduleClassName();
        }

        return $moduleList;
    }

    /**
     * Add a message to JSON object
     *
     * @param object $json information in stdclass
     * @param string $message message text
     * @param bool $error is error or not
     * @return object
     */
    protected function _addMessageToJson($json, $message, $error = 0) {
        $returnMessage = new stdClass;
        $returnMessage->error = $error;
        $returnMessage->text = $message;

        if ($error == 1) {
            $json->success = 0;
        }
        $json->msg[] = $returnMessage;
        return $json;
    }

    /**
     * Return class name by module name
     *
     * @param string $moduleName module name
     * @return string
     */
    protected static function _getClassname($moduleName) {
        return 'RM_Module_' . $moduleName;
    }

    /**
     * Returns array with css full file URLs
     *
     * @param string $moduleName module name
     * @return array
     */
    public static function getCssFiles($moduleName) {
        $rootPath = RM_Environment::getConnector()->getRootPath();
        $folder = $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . RM_Module_Config::CSS;

        return RM_Filesystem::getFiles($folder, array("css"));
    }

    /**
     * Returns array with js full file URLs
     *
     * @param string $moduleName module name
     * @return array
     */
    public static function getAdminJSFiles($moduleName) {
        $folder = self::getModuleFolderpath($moduleName);
        $folder.= DIRECTORY_SEPARATOR . RM_Module_Config::JS;
        return RM_Filesystem::getFiles($folder, array("js"));
    }

    /**
     * Return full module folder path
     *
     * @param string $moduleName module name
     * @param string $rootPath root path 
     * @return string 
     */
    public static function getModuleFolderpath($moduleName, $rootPath = null) {
        if ($rootPath === null) {
            $rootPath = RM_Environment::getConnector()->getRootPath();
        }

        return $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName;
    }

    /**
     * Return full module user folder path
     *
     * @param string $moduleName module name
     * @param string $rootPath root path
     * @return string
     */
    public static function getModuleUserFolderpath($moduleName, $rootPath = null) {
        if ($rootPath === null) {
            $rootPath = RM_Environment::getConnector()->getRootPath();
        }
        return $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName;
    }

    /**
     * Install database information for the module
     *
     * @param string $moduleFolderPath 
     * @return bool
     */
    public static function installDatabase($moduleFolderPath) {
        return RM_SQL_Manager::executeSQLFile($moduleFolderPath . DIRECTORY_SEPARATOR . RM_Module_Config::SQL . DIRECTORY_SEPARATOR . RM_Module_Config::INSTALL_SQL);
    }

    public static function upgradeDatabase($moduleName, $previousVersion, $latestVersion) {
        $sqlFolderPath = implode(DIRECTORY_SEPARATOR, array(
                    RM_Environment::getConnector()->getRootPath(),
                    'RM',
                    'modules',
                    $moduleName,
                    'sql'
                ));
        return RM_Extension_Manager::_upgradeDatabase($sqlFolderPath, $previousVersion, $latestVersion);
    }

    /**
     * Uninstall database information for the module
     *
     * @param string $moduleFolderPath path to module folder
     * @return bool
     */
    public static function uninstallDatabase($moduleFolderPath) {
        return RM_SQL_Manager::executeSQLFile($moduleFolderPath . DIRECTORY_SEPARATOR . RM_Module_Config::SQL . DIRECTORY_SEPARATOR . RM_Module_Config::UNINSTALL_SQL);
    }

    /**
     * Try to disable plugin. This method should be only invoked by any other core
     *
     * @throws RM_Exception
     * @param RM_Module_Row $module
     * @return bool
     */
    public function disable(RM_Module_Row $module) {
        $model = new RM_Modules();
        $moduleObject = $model->get($module->name);

        //Try/catch just for visualisation and better code understanding
        //we don't want to handle this exception here and just throw it to higher level
        try {
            $moduleObject->disable();
        } catch (RM_Exception $e) {
            throw $e;
        }

        $model->disable($module);
        return true;
    }

    /**
     * Uninstall module
     *
     * @param RM_Module_Row $module
     * @throws RM_Exception is some error occures
     */
    function uninstall($module) {
        //1. get module path
        $moduleFolderPath = self::getModuleFolderpath($module->name);

        //3. invoke uninstall from module main class
        $className = self::_getClassname($module->name);
        $moduleObject = new $className;
        $moduleObject->uninstall();

        //5. invoke SQL uninstall file
        self::uninstallDatabase($moduleFolderPath);

        //6. remove userdata module directories
        $result = RM_Filesystem::deleteFolder($moduleFolderPath);
        $result = RM_Filesystem::deleteFolder(self::getModuleUserFolderpath($module->name));

        //7. disable all modules that are connected to this module if this is possible
        $pluginsManager = new RM_Plugin_Manager(RM_Environment::getInstance()->getTranslation());
        $pluginsModel = new RM_Plugins();
        $plugins = $pluginsModel->fetchByModuleName($module->name, true);
        foreach ($plugins as $plugin) {
            try {
                $pluginsManager->disable($plugin);
            } catch (Exception $e) {
                //TODO: add some log message that we could not disable this plugin
                continue;
            }
        }

        //8. remove module row from database
        $module->delete();
        return true;
    }

    /**
     * @throws RM_Exception
     * @param RM_Module_Row $row
     * @return bool|object
     */
    function autoUpgrade(RM_Module_Row $row) {
        $sourceFile = $this->_getUpgradeURL($row->name);
        if (!$sourceFile) {
            throw new RM_Exception($this->_translate->_('Admin.Modules.AutoUpgradeMsg', 'CannotUpgrade'));
        }

        $fileName = $row->name.".zip";
        $destinationFile = RM_Environment::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $fileName;

        $system = new RM_Filesystem();
        $result = $system->fileDownload($sourceFile, $destinationFile);
        if ($result == false) {
            throw new RM_Exception($this->_translate->_('Admin.Modules.AutoUpgradeMsg', 'DownloadFailed'));
        }

        $json = new stdClass();
        $json->success = 1;
        $json->msg = array();

        return $this->upgrade($fileName, $destinationFile, $json);
    }

    function upgrade($tempFileName, $tempFilePath, $json) {

        RM_Log::toLog("Module Upgrade Called, Temp File: ".$tempFileName." Temp Path: ".$tempFilePath);

        $rmConfig = new RM_Config();
        $chmodOctal = intval($rmConfig->getValue('rm_config_chmod_value'), 8);

        $rootPath = RM_Environment::getConnector()->getRootPath();

        $chunks = explode('.', $tempFileName);
        $moduleName = $chunks[0]; //Module name will be always the first chunk

        $moduleFolderPath = $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName;
        if (is_dir($moduleFolderPath) == false) {
            throw new RM_Exception($this->_translate->_('Admin.Modules.UpgradeMsg', 'NoModule'));
        }

        if (!extension_loaded('zlib')) {
            unlink($tempFilePath);
            throw new RM_Exception($this->_translate->_('Admin.Modules.UpgradeMsg', 'ZlibNotSupported'));
        }

        $zip = new PclZip($tempFilePath);
        $result = $zip->extract(
                        PCLZIP_OPT_PATH, $moduleFolderPath,
                        PCLZIP_OPT_REPLACE_NEWER
        );
        if (!$result) {
            unlink($tempFilePath);
            throw new RM_Exception($this->_translate->_('Admin.Modules.UpgradeMsg', 'UnzipFailed'));
        } else {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Modules.UpgradeMsg', 'UnzipSuccessfully'));
        }
        $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Modules.UpgradeMsg', 'UnzipSuccess'));

        unlink($tempFilePath);
        chmod($moduleFolderPath, $chmodOctal);

        $userDataFolderPath = $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName;

        $userDataFolders = array(
            'views',
            'languages',
            'css',
            'images'
        );
        foreach ($userDataFolders as $folder) {
            if (is_dir($moduleFolderPath . DIRECTORY_SEPARATOR . $folder)) {
                $this->_moveUserDataFolders(
                        $moduleFolderPath . DIRECTORY_SEPARATOR . $folder,
                        $userDataFolderPath . DIRECTORY_SEPARATOR . $folder,
                        $chmodOctal
                );
            }
        }

        $iniFilePath = $moduleFolderPath . DIRECTORY_SEPARATOR . self::$_iniFilename;
        if (is_file($iniFilePath) == false) {
            throw new RM_Exception($this->_translate->_('Admin.Modules.UpgradeMsg', 'NoIniFile'));
        }

        $parser = new RM_Module_Config_Parser();
        try {
            $config = $parser->getConfig($iniFilePath);
        } catch (RM_Exception $e) {
            throw new RM_Exception($e->getMessage());
        }

        $moduleModel = new RM_Modules;
        $moduleObject = $moduleModel->fetchByName($moduleName);
        $previousVersion = $moduleObject->version;
        $currentVersion = $config->information['version'];

        $result = self::upgradeDatabase($moduleName, $previousVersion, $currentVersion);
        if ($result) {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Modules.UpgradeMsg', 'DatabaseSuccess'));
        }

        // call the module upgrade method (in a try
        // incase the module does nothave this method
        // or incase the method fails)
        $moduleClassName = 'RM_Module_' . $moduleObject->name;
        $actualModuleObject = new $moduleClassName;
        try {
            $actualModuleObject->upgrade();
        } catch (Exception $e){}

        $moduleObject->version = $config->information['version'];
        $moduleObject->save();

        return $json;
    }

    function uploadUpgrade($formName, $json) {
        try {
            list($tempFileName, $tempFilePath, $json) = $this->upload($formName, $json);
        } catch (Exception $e) {
            $json = $this->_addMessageToJson($json, $e->getMessage(), 1);
            return $json;
        }

        try {
            $json = $this->upgrade($tempFileName, $tempFilePath, $json);
        } catch (Exception $e) {
            $json = $this->_addMessageToJson($json, $e->getMessage(), 1);
        }
        return $json;
    }

    /**
     * Upload module zip file, extract it and install
     *
     * @param string $formName html form name
     * @param object $json stdclass object with json response information
     * @return stdclass
     */
    function uploadInstall($formName, $json) {
        try {
            list($tempFileName, $tempFilePath, $json) = $this->upload($formName, $json);
        } catch (Exception $e) {
            $json = $this->_addMessageToJson($json, $e->getMessage(), 1);
            return $json;
        }

        try {
            $json = $this->install($tempFileName, $tempFilePath, $json);
        } catch (Exception $e) {
            $json = $this->_addMessageToJson($json, $e->getMessage(), 1);
        }
        return $json;
    }

    /**
     * Upload module zip file.
     *
     * @param string $formName name of the form on the user interface
     * @param stdclass $json
     * @return array
     * 
     * $tempFileName,
     * $tempFilePath,
     * $json
     */
    function upload($formName, $json) {
        $rootPath = RM_Environment::getConnector()->getRootPath();

        //1. upload zip - check if this file name a .zip extension
        //2. move zip to temp directory
        $tempFolderName = 'temp';
        $tempFolderPath = $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . $tempFolderName;

        $validators = array();
        $validators[] = new Zend_Validate_File_Extension('zip');

        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setValidators($validators);

        try {
            $adapter->setDestination($tempFolderPath);
        } catch (Zend_File_Transfer_Exception $exception) {
            throw new RM_Exception($exception->getMessage());
        }

        if (!$adapter->receive()) {
            $message = $this->_translate->_('Admin.Modules.InstallMsg', 'UploadFailed');
            $message.= '. ' . implode("; ", $adapter->getMessages());
            throw new RM_Exception($message);
        } else {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Modules.InstallMsg', 'UploadSuccess'));
        }

        //3. create new directory for a module
        $files = $adapter->getFileInfo();
        $tempFileName = $files[$formName]['name'];
        $tempFilePath = $tempFolderPath . DIRECTORY_SEPARATOR . $tempFileName;

        return array($tempFileName, $tempFilePath, $json);
    }

    /**
     * Full installation process of module
     *
     * @param string $tempFileName
     * @param string $tempFilePath
     * @param stdclass $json
     * @return stdclass
     */
    function install($tempFileName, $tempFilePath, $json) {
        // get config values
        $rmConfig = new RM_Config();
        $chmodOctal = intval($rmConfig->getValue('rm_config_chmod_value'), 8);

        $chunks = explode('.', $tempFileName);
        $moduleName = $chunks[0]; //Module name will be always the first chunk, example: price.0.1.1.zip

        $moduleModel = new RM_Modules;
        $existingModule = $moduleModel->fetchByName($moduleName);
        if ($existingModule !== null) {
            unlink($tempFilePath);
            throw new RM_Exception($this->_translate->_('Admin.Modules.InstallMsg', 'ModuleAlreadyInstalled'));
        }

        $moduleFolderPath = self::getModuleFolderpath($moduleName);
        if (is_dir($moduleFolderPath)) {
            $result = RM_Filesystem::deleteFolder($moduleFolderPath);
            if ($result == false) {
                unlink($tempFilePath);
                throw new RM_Exception($this->_translate->_('Admin.Modules.InstallMsg', 'ModuleFolderAlreadyExists') . ': ' . $moduleFolderPath);
            }
        }

        $result = mkdir($moduleFolderPath, $chmodOctal);
        if ($result == false) {
            unlink($tempFilePath);
            throw new RM_Exception($this->_translate->_('Admin.Modules.InstallMsg', 'CreateModuleFolderFailer'));
        } else {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Modules.InstallMsg', 'ModuleFolderCreatedSuccessfully'));
        }

        //4. unzip module into new directory
        if (!extension_loaded('zlib')) {
            unlink($tempFilePath);
            RM_Filesystem::deleteFolder($moduleFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Modules.InstallMsg', 'ZlibNotSupported'));
        }

        $zip = new PclZip($tempFilePath);
        $result = $zip->extract(PCLZIP_OPT_PATH, $moduleFolderPath);

        if (!$result) {
            unlink($tempFilePath);
            RM_Filesystem::deleteFolder($moduleFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Modules.InstallMsg', 'UnzipFailed'));
        } else {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Modules.InstallMsg', 'UnzipSuccessfully'));
        }

        unlink($tempFilePath);
        chmod($moduleFolderPath, $chmodOctal);

        //4.0. create separate folder in 'userdata/modules' for a new module, if it's don't exists
        $userdataFolderPath = self::getModuleUserFolderpath($moduleName) . DIRECTORY_SEPARATOR;
        if (is_dir($userdataFolderPath)) {
            $result = RM_Filesystem::deleteFolder($userdataFolderPath);
            if ($result == false) {
                throw new RM_Exception($this->_translate->_('Admin.Modules.InstallMsg', 'ModuleFolderAlreadyExists') . ': ' . $userdataFolderPath);
            }
        }

        if (is_dir($userdataFolderPath) == false) {
            $result = mkdir($userdataFolderPath, $chmodOctal);
            if ($result == false) {
                RM_Filesystem::deleteFolder($moduleFolderPath);
                throw new RM_Exception($this->_translate->_('Admin.Modules.InstallMsg', 'CreateModuleUserdataFolderFailer'));
            } else {
                $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Modules.InstallMsg', 'ModuleFolderCreatedSuccessfully'));
            }

            @rename($moduleFolderPath . DIRECTORY_SEPARATOR . 'views', $userdataFolderPath . 'views');
            @chmod($userdataFolderPath . 'views', $chmodOctal);

            @rename($moduleFolderPath . DIRECTORY_SEPARATOR . 'languages', $userdataFolderPath . 'languages');
            @chmod($userdataFolderPath . 'languages', $chmodOctal);

            @rename($moduleFolderPath . DIRECTORY_SEPARATOR . 'css', $userdataFolderPath . 'css');
            @chmod($userdataFolderPath . 'css', $chmodOctal);

            @rename($moduleFolderPath . DIRECTORY_SEPARATOR . 'images', $userdataFolderPath . 'images');
            @chmod($userdataFolderPath . 'images', $chmodOctal);

            @chmod($moduleFolderPath, $chmodOctal);
        }

        //5. get INI file
        $iniFilePath = $moduleFolderPath . DIRECTORY_SEPARATOR . self::$_iniFilename;
        if (is_file($iniFilePath) == false) {
            RM_Filesystem::deleteFolder($moduleFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Modules.InstallMsg', 'NoIniFile'));
        }

        //6. parse INI file
        $parser = new RM_Module_Config_Parser();
        try {
            $config = $parser->getConfig($iniFilePath);
        } catch (RM_Exception $e) {
            //Error in ini file parsing
            RM_Filesystem::deleteFolder($moduleFolderPath);
            throw new RM_Exception($e->getMessage());
        }
        if (isset($config->information['module'])) {
            RM_Filesystem::deleteFolder($moduleFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Modules.InstallMsg', 'WrongTryToInstallPlugin'));
        }

        $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Modules.InstallMsg', 'UnzipSuccess'));

        //7. invoke SQL install file
        try {
            $result = self::installDatabase($moduleFolderPath);
        } catch (Exception $e) {
            self::uninstallDatabase($moduleFolderPath);
            RM_Filesystem::deleteFolder($moduleFolderPath);
            RM_Filesystem::deleteFolder($userdataFolderPath);
            throw new RM_Exception($e->getMessage());
        }

        if ($result == false) {
            self::uninstallDatabase($moduleFolderPath);
            RM_Filesystem::deleteFolder($moduleFolderPath);
            RM_Filesystem::deleteFolder($userdataFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Modules.InstallMsg', 'WrongInstallSQLQueries'));
        } else {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Modules.InstallMsg', 'InstallSQLSuccess'));
        }

        //8. create a new record in Module database
        $moduleRow = array();
        $moduleRow = $config->information;

        //TODO:
        //1.check the insertions        
        $pkey = $moduleModel->insert($moduleRow);

        //Create a new records in dependencies database table
        $model = new RM_Dependencies();
        if (is_array($config->dependencies)) {
            foreach ($config->dependencies as $dependency) {
                $model->insert($dependency);
            }
        }

        //9. get the main class of the module
        $moduleClassFilepath = $moduleFolderPath . DIRECTORY_SEPARATOR . RM_Module_Config::CLASSES . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . $config->information['name'] . '.php';
        if (is_file($moduleClassFilepath) == false) {
            self::uninstallDatabase($moduleFolderPath);
            RM_Filesystem::deleteFolder($moduleFolderPath);
            RM_Filesystem::deleteFolder($userdataFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Modules.InstallMsg', 'DoesntExists'));
        }

        require_once($moduleClassFilepath);
        $moduleClassName = 'RM_Module_' . $config->information['name'];
        if (class_exists($moduleClassName) == false) {
            self::uninstallDatabase($moduleFolderPath);
            RM_Filesystem::deleteFolder($moduleFolderPath);
            RM_Filesystem::deleteFolder($userdataFolderPath);
            throw new RM_Exception($moduleClassName . $this->_translate->_('Admin.Modules.InstallMsg', 'DoesntExists'));
        }
        $moduleObject = new $moduleClassName();

        if (!$moduleObject instanceof RM_Module_Interface) {
            self::uninstallDatabase($moduleFolderPath);
            RM_Filesystem::deleteFolder($moduleFolderPath);
            RM_Filesystem::deleteFolder($userdataFolderPath);
            throw new RM_Exception($moduleClassName . $this->_translate->_('Admin.Modules.InstallMsg', 'WrongInterface'));
        }

        //10. invoke install method of that class (if we need something extra)
        $result = $moduleObject->install();

        $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Modules.InstallMsg', 'InstallSuccess'));

        //Check dependencies and if some of them is validate==false disable module
        $moduleManager = new RM_Module_Manager(RM_Environment::getInstance()->getTranslation());
        $dependencyManager = new RM_Dependency_Manager();
        $module = $moduleModel->find($pkey)->current();
        $dependencies = $dependencyManager->getDependencies($module);
        foreach ($dependencies as $dependency) {
            if ($dependency->validate() == false) {
                try {
                    $moduleModel->disable($module);
                    break;
                } catch (Exception $e) {
                    //TODO: add log message that we could not disable this module.
                }
            }
        }

        return $json;
    }

}
