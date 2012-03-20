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
 * Class for plugin management
 *
 * This handles all language related actions
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Plugin_Manager extends RM_Extension_Manager implements RM_Multilingual_Interface {

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
        $plugins = self::getPlugins();
        foreach ($plugins as $plugin) {
            $plugin->addLanguage($iso);
        }
    }

    /**
     * Delete languages
     *
     * @param string $iso ISO language code
     */
    public function deleteLanguage($iso) {
        $plugins = self::getPlugins();
        foreach ($plugins as $plugin) {
            $plugin->deleteLanguage($iso);
        }
    }

    /**
     * Returns plugin object by plugin name
     *
     * @param string $name
     * @return RM_Plugin
     */
    public static function getPlugin($name) {
        $pluginClassName = self::_getClassname($name);
        return new $pluginClassName();
    }

    /**
     * Return all plugin objects
     *
     * @todo RM_Plugins has the very similar method: getAll. "In the end, there can be only one."
     * @return array - array with plugins
     */
    public static function getPlugins() {
        $model = new RM_Plugins;

        $plugins = $model->fetchAll();

        $pluginList = array();
        foreach ($plugins as $plugin) {
            $pluginClassName = self::_getClassname($plugin->name);
            $pluginList[] = new $pluginClassName();
        }

        return $pluginList;
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
     * Return class name by plugin name
     *
     * @param string $pluginName plugin name
     * @return string
     */
    protected static function _getClassname($pluginName) {
        return 'RM_Plugin_' . $pluginName;
    }

    /**
     * Returns array with css full file URLs
     *
     * @param string $pluginName plugin name
     * @return array
     */
    public static function getCssFiles($pluginName) {
        $rootPath = RM_Environment::getConnector()->getRootPath();
        $folder = $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . RM_Plugin_Config::CSS;

        return RM_Filesystem::getFiles($folder);
    }

    /**
     * Returns array with js full file URLs
     *
     * @param string $pluginName plugin name
     * @return array
     */
    public static function getAdminJSFiles($pluginName) {
        $folder = self::getPluginFolderpath($pluginName);
        $folder.= DIRECTORY_SEPARATOR . RM_Plugin_Config::JS;
        return RM_Filesystem::getFiles($folder, array("js"));
    }

    /**
     * Return full plugin folder path
     *
     * @param string $pluginName plugin name
     * @param string $rootPath root path
     * @return string
     */
    public static function getPluginFolderpath($pluginName, $rootPath = null) {
        if ($rootPath === null) {
            $rootPath = RM_Environment::getConnector()->getRootPath();
        }

        return $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pluginName;
    }

    /**
     * Return full plugin user folder path
     *
     * @param string $pluginName plugin name
     * @param string $rootPath root path
     * @return string
     */
    public static function getPluginUserFolderpath($pluginName, $rootPath = null) {
        if ($rootPath === null) {
            $rootPath = RM_Environment::getConnector()->getRootPath();
        }
        return $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pluginName;
    }

    /**
     * Install database information for the plugin
     *
     * @param string $pluginFolderPath
     * @return bool
     */
    public static function installDatabase($pluginFolderPath) {
        return RM_SQL_Manager::executeSQLFile($pluginFolderPath . DIRECTORY_SEPARATOR . RM_Plugin_Config::SQL . DIRECTORY_SEPARATOR . RM_Plugin_Config::INSTALL_SQL);
    }

    public static function upgradeDatabase($pluginName, $previousVersion, $latestVersion) {
        $sqlFolderPath = implode(DIRECTORY_SEPARATOR, array(
                    RM_Environment::getConnector()->getRootPath(),
                    'RM',
                    'plugins',
                    $pluginName,
                    'sql'
                ));
        return RM_Extension_Manager::_upgradeDatabase($sqlFolderPath, $previousVersion, $latestVersion);
    }

    /**
     * Uninstall database information for the plugin
     *
     * @param string $pluginFolderPath path to plugin folder
     * @return bool
     */
    public static function uninstallDatabase($pluginFolderPath) {
        $filename = $pluginFolderPath . DIRECTORY_SEPARATOR . RM_Plugin_Config::SQL . DIRECTORY_SEPARATOR . RM_Plugin_Config::UNINSTALL_SQL;
        return RM_SQL_Manager::executeSQLFile($filename);
    }

    /**
     * Try to disable plugin. This method should be only invoked by any other core
     *
     * @throws RM_Exception
     * @param RM_Plugin_Row $plugin
     * @return bool
     */
    public function disable(RM_Plugin_Row $plugin) {
        $model = new RM_Plugins();
        $moduleObject = $model->get($plugin->name);

        //Try/catch just for visualisation and better code understanding,
        //we don't want to handle this exception here and just throw it to higher level
        try {
            $moduleObject->disable();
        } catch (RM_Exception $e) {
            throw $e;
        }

        $paymentSystem = RM_Environment::getInstance()->getPaymentSystem();
        //isRestricted throw RM_Exception instead of returning 'false', cause we need an error text message to understand
        //what is going wrong on the higher level
        if ($paymentSystem->isRestricted($plugin)) {
            return false;
        }

        $model->disable($plugin);
        return true;
    }

    /**
     * Uninstall plugin
     *
     * @param RM_Plugin_Row $plugin
     * @throws RM_Exception is some error occures
     */
    function uninstall($plugin) {
        $paymentSystem = RM_Environment::getInstance()->getPaymentSystem();
        if ($paymentSystem->isRestricted($plugin)) {
            return false;
        }

        //1. get plugin path
        $pluginFolderPath = self::getPluginFolderpath($plugin->name);

        //3. invoke uninstall from plugin main class
        $className = self::_getClassname($plugin->name);
        $pluginObject = new $className;
        $pluginObject->uninstall();

        //5. invoke SQL uninstall file
        self::uninstallDatabase($pluginFolderPath);

        //6. remove plugin directories
        $result = RM_Filesystem::deleteFolder($pluginFolderPath);
        $result = RM_Filesystem::deleteFolder(self::getPluginUserFolderpath($plugin->name));

        //7. remove plugin row from database
        $plugin->delete();
        return true;
    }

    /**
     * @throws RM_Exception
     * @param RM_Plugin_Row $row
     * @return bool
     */
    function autoUpgrade(RM_Plugin_Row $row) {
        $sourceFile = $this->_getUpgradeURL($row->name);
        if (!$sourceFile) {
            throw new RM_Exception($this->_translate->_('Admin.Plugins.AutoUpgradeMsg', 'CannotUpgrade'));
        }

        $fileName = $row->name.".zip";
        $destinationFile = RM_Environment::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $fileName;

        $system = new RM_Filesystem();
        $result = $system->fileDownload($sourceFile, $destinationFile);
        if ($result == false) {
            throw new RM_Exception($this->_translate->_('Admin.Plugins.AutoUpgradeMsg', 'DownloadFailed'));
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
        $pluginName = $chunks[0]; //Plugin name will be always the first chunk

        $pluginFolderPath = $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pluginName;
        if (is_dir($pluginFolderPath) == false) {
            throw new RM_Exception($this->_translate->_('Admin.Plugins.UpgradeMsg', 'NoPlugin'));
        }

        if (!extension_loaded('zlib')) {
            unlink($tempFilePath);
            throw new RM_Exception($this->_translate->_('Admin.Plugins.UpgradeMsg', 'ZlibNotSupported'));
        }

        $zip = new PclZip($tempFilePath);
        $result = $zip->extract(
                        PCLZIP_OPT_PATH, $pluginFolderPath,
                        PCLZIP_OPT_REPLACE_NEWER
        );
        if (!$result) {
            unlink($tempFilePath);
            throw new RM_Exception($this->_translate->_('Admin.Plugins.UpgradeMsg', 'UnzipFailed'));
        } else {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Plugins.UpgradeMsg', 'UnzipSuccessfully'));
        }
        $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Plugins.UpgradeMsg', 'UnzipSuccess'));

        unlink($tempFilePath);
        chmod($pluginFolderPath, $chmodOctal);

        $userDataFolderPath = $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pluginName;

        $userDataFolders = array(
            'views',
            'languages',
            'css',
            'images'
        );
        foreach ($userDataFolders as $folder) {
            if (is_dir($pluginFolderPath . DIRECTORY_SEPARATOR . $folder)) {
                $this->_moveUserDataFolders(
                        $pluginFolderPath . DIRECTORY_SEPARATOR . $folder,
                        $userDataFolderPath . DIRECTORY_SEPARATOR . $folder,
                        $chmodOctal
                );
            }
        }

        $iniFilePath = $pluginFolderPath . DIRECTORY_SEPARATOR . self::$_iniFilename;
        if (is_file($iniFilePath) == false) {
            throw new RM_Exception($this->_translate->_('Admin.Plugins.UpgradeMsg', 'NoIniFile'));
        }

        $parser = new RM_Plugin_Config_Parser();
        try {
            $config = $parser->getConfig($iniFilePath);
        } catch (RM_Exception $e) {
            throw new RM_Exception($e->getMessage());
        }

        $pluginModel = new RM_Plugins;
        $pluginObject = $pluginModel->fetchByName($pluginName);
        $previousVersion = $pluginObject->version;
        $currentVersion = $config->information['version'];

        $result = self::upgradeDatabase($pluginName, $previousVersion, $currentVersion);
        if ($result) {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Plugins.UpgradeMsg', 'DatabaseSuccess'));
        }

        // call the module upgrade method (in a try
        // incase the module does nothave this method
        // or incase the method fails)
        $pluginClassName = 'RM_Plugin_' . $pluginObject->name;
        $actualPluginObject = new $pluginClassName;
        try {
            $actualPluginObject->upgrade();
        } catch (Exception $e){}

        $pluginObject->version = $config->information['version'];
        $pluginObject->save();

        return true;
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
     * Upload plugin zip file, extract it and install
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
     * Upload plugin zip file.
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
            $message = $this->_translate->_('Admin.Plugins.InstallMsg', 'UploadFailed');
            $message.= '. ' . implode("; ", $adapter->getMessages());
            throw new RM_Exception($message);
        } else {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Plugins.InstallMsg', 'UploadSuccess'));
        }

        //3. create new directory for a plugin
        $files = $adapter->getFileInfo();
        $tempFileName = $files[$formName]['name'];
        $tempFilePath = $tempFolderPath . DIRECTORY_SEPARATOR . $tempFileName;

        return array($tempFileName, $tempFilePath, $json);
    }

    /**
     * Full installation process of plugin
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

        $rootPath = RM_Environment::getConnector()->getRootPath();

        $chunks = explode('.', $tempFileName);
        $pluginName = $chunks[0]; //Plugin name will be always the first chunk, example: price.0.1.1.zip

        $pluginModel = new RM_Plugins;
        $existingPlugin = $pluginModel->fetchByName($pluginName);
        if ($existingPlugin !== null) {
            unlink($tempFilePath);
            throw new RM_Exception($this->_translate->_('Admin.Plugins.InstallMsg', 'PluginAlreadyInstalled'));
        }

        $pluginFolderPath = $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pluginName;
        if (is_dir($pluginFolderPath)) {
            $result = RM_Filesystem::deleteFolder($pluginFolderPath);
            if ($result == false) {
                unlink($tempFilePath);
                throw new RM_Exception($this->_translate->_('Admin.Plugins.InstallMsg', 'PluginFolderAlreadyExists') . ': ' . $pluginFolderPath);
            }
        }

        $result = mkdir($pluginFolderPath, $chmodOctal);
        if ($result == false) {
            unlink($tempFilePath);
            throw new RM_Exception($this->_translate->_('Admin.Plugins.InstallMsg', 'CreatePluginFolderFailer'));
        } else {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Plugins.InstallMsg', 'PluginFolderCreatedSuccessfully'));
        }

        //4. unzip plugin into new directory
        if (!extension_loaded('zlib')) {
            unlink($tempFilePath);
            RM_Filesystem::deleteFolder($pluginFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Plugins.InstallMsg', 'ZlibNotSupported'));
        }

        $zip = new PclZip($tempFilePath);
        $result = $zip->extract(PCLZIP_OPT_PATH, $pluginFolderPath);

        if (!$result) {
            unlink($tempFilePath);
            RM_Filesystem::deleteFolder($pluginFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Plugins.InstallMsg', 'UnzipFailed'));
        } else {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Plugins.InstallMsg', 'UnzipSuccessfully'));
        }

        unlink($tempFilePath);
        chmod($pluginFolderPath, $chmodOctal);

        //4.0. create separate folder in 'userdata/plugins' for a new plugin
        $userdataFolderPath = $rootPath . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pluginName;
        if (is_dir($userdataFolderPath)) {
            $result = RM_Filesystem::deleteFolder($userdataFolderPath);
            if ($result == false) {
                throw new RM_Exception($this->_translate->_('Admin.Plugins.InstallMsg', 'PluginFolderAlreadyExists') . ': ' . $userdataFolderPath);
            }
        }

        $result = mkdir($userdataFolderPath . DIRECTORY_SEPARATOR, $chmodOctal);
        if ($result == false) {
            RM_Filesystem::deleteFolder($pluginFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Plugins.InstallMsg', 'CreatePluginUserdataFolderFailer'));
        } else {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Plugins.InstallMsg', 'PluginFolderCreatedSuccessfully'));
        }

        @rename($pluginFolderPath . DIRECTORY_SEPARATOR . 'views', $userdataFolderPath . DIRECTORY_SEPARATOR . 'views');
        @chmod($pluginFolderPath . DIRECTORY_SEPARATOR . 'views', $chmodOctal);

        @rename($pluginFolderPath . DIRECTORY_SEPARATOR . 'languages', $userdataFolderPath . DIRECTORY_SEPARATOR . 'languages');
        @chmod($pluginFolderPath . DIRECTORY_SEPARATOR . 'languages', $chmodOctal);

        @rename($pluginFolderPath . DIRECTORY_SEPARATOR . 'css', $userdataFolderPath . DIRECTORY_SEPARATOR . 'css');
        @chmod($pluginFolderPath . DIRECTORY_SEPARATOR . 'css', $chmodOctal);

        @rename($pluginFolderPath . DIRECTORY_SEPARATOR . 'images', $userdataFolderPath . DIRECTORY_SEPARATOR . 'images');
        @chmod($pluginFolderPath . DIRECTORY_SEPARATOR . 'images', $chmodOctal);

        @chmod($userdataFolderPath, $chmodOctal);

        //5. get INI file
        $iniFilePath = $pluginFolderPath . DIRECTORY_SEPARATOR . self::$_iniFilename;
        if (is_file($iniFilePath) == false) {
            RM_Filesystem::deleteFolder($pluginFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Plugins.InstallMsg', 'NoIniFile'));
        }

        //6. parse INI file
        $parser = new RM_Plugin_Config_Parser();
        try {
            $config = $parser->getConfig($iniFilePath);
        } catch (RM_Exception $e) {
            //Error in ini file parsing
            RM_Filesystem::deleteFolder($pluginFolderPath);
            throw new RM_Exception($e->getMessage());
        }
        //Check: could be a module if no 'module' value is in config
        if ($config->information['module'] == "") {
            RM_Filesystem::deleteFolder($pluginFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Plugins.InstallMsg', 'WrongTryToInstallModule'));
        }

        $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Plugins.InstallMsg', 'UnzipSuccess'));

        //7. invoke SQL install file
        try {
            $result = self::installDatabase($pluginFolderPath);
        } catch (Exception $e) {
            self::uninstallDatabase($pluginFolderPath);
            RM_Filesystem::deleteFolder($pluginFolderPath);
            throw new RM_Exception($e->getMessage());
        }

        if ($result == false) {
            self::uninstallDatabase($pluginFolderPath);
            RM_Filesystem::deleteFolder($pluginFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Plugins.InstallMsg', 'WrongInstallSQLQueries'));
        } else {
            $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Plugins.InstallMsg', 'InstallSQLSuccess'));
        }

        //8. create a new record in Plugin database
        $pluginRow = array();
        $pluginRow = $config->information;
        $pluginRow['module_name'] = $pluginRow['module'];
        unset($pluginRow['module']);

        //convert creation date into MySQL format
        $rmConfig = new RM_Config;
        $pluginRow['creation_date'] = $rmConfig->convertDates(
                        strtotime($pluginRow['creation_date']),
                        RM_Config::TIMESTAMP_DATEFORMAT,
                        RM_Config::MYSQL_DATEFORMAT
        );

        //TODO:
        //1.check the insertions        
        $pkey = $pluginModel->insert($pluginRow);

        //Create a new records in dependencies database table
        $model = new RM_Dependencies();
        if (is_array($config->dependencies)) {
            foreach ($config->dependencies as $dependency) {
                $model->insert($dependency);
            }
        }

        //9. get the main class of the plugin
        $pluginClassFilepath = $pluginFolderPath . DIRECTORY_SEPARATOR . RM_Plugin_Config::CLASSES . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'Plugin' . DIRECTORY_SEPARATOR . $config->information['name'] . '.php';
        if (is_file($pluginClassFilepath) == false) {
            self::uninstallDatabase($pluginFolderPath);
            RM_Filesystem::deleteFolder($pluginFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.Plugins.InstallMsg', 'DoesntExists'));
        }

        require_once($pluginClassFilepath);
        $pluginClassName = 'RM_Plugin_' . $config->information['name'];
        if (class_exists($pluginClassName) == false) {
            self::uninstallDatabase($pluginFolderPath);
            RM_Filesystem::deleteFolder($pluginFolderPath);
            throw new RM_Exception($pluginClassName . $this->_translate->_('Admin.Plugins.InstallMsg', 'DoesntExists'));
        }
        $pluginObject = new $pluginClassName();

        if (!$pluginObject instanceof RM_Plugin_Interface) {
            self::uninstallDatabase($pluginFolderPath);
            RM_Filesystem::deleteFolder($pluginFolderPath);
            throw new RM_Exception($pluginClassName . $this->_translate->_('Admin.Plugins.InstallMsg', 'WrongInterface'));
        }

        //10. invoke install method of that class (if we need something extra)
        $result = $pluginObject->install();

        $json = $this->_addMessageToJson($json, $this->_translate->_('Admin.Plugins.InstallMsg', 'InstallSuccess'));

        //Check dependencies and if some of them is validate==false disable plugin
        $dependencyManager = new RM_Dependency_Manager();
        $plugin = $pluginModel->find($pkey)->current();
        $dependencies = $dependencyManager->getDependencies($plugin);
        foreach ($dependencies as $dependency) {
            if ($dependency->validate() == false) {
                try {
                    $pluginModel->disable($plugin);
                    break;
                } catch (Exception $e) {
                    //TODO: add log message that we could not disable this module.
                }
            }
        }

        return $json;
    }

}