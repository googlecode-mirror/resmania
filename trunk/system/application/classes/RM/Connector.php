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
 * ResMania Connector Class
 *
 * This class is a factory class for every connector.
 * Example of use:
 * 1. we need to setup it with real connector class name and module name (admin/user)
 * RM_Connector::setup($connectorClassName, $moduleName);
 * 2. get the connector object
 * $connector = RM_Connector::getInstance();
 *
 * @access      public
 * @author      Valentin/Rob
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */

abstract class RM_Connector {

    /**
     * Current name of a core config mode. For Zend_Config_Ini instantiate.
     * Could be:
     * [production] (for customer use)
     * OR
     * any section name that present in core.config.ini in format [<section name> : production]
     *
     * @var string
     */
    protected static $_mode;
    protected static $_instance;
    protected $_safeMode;
    protected $_rootPath;
    protected $_module;
    /**
     * @var Zend_Controller_Router_Interface
     */
    protected $_router;
    protected $_locale;
    /**
     * Array with parameters
     * 
     * @var array
     */
    protected $_config;

    /**
     * Setup
     *
     * @param string $connectorClassName - class name of child class of RM_Connector
     * @param string $moduleName - admin or user end
     */
    public static function setup($connectorClassName, $moduleName) {
        self::$_instance = new $connectorClassName($moduleName);
    }

    /**
     * Return instance of RM_Connector
     *
     * @return RM_Connector
     */
    public static function getInstance() {
        return self::$_instance;
    }

    /**
     * Connect Zend Framework to CMS
     *
     * @param $rootPath - physical path to root directory
     * @return null
     */
    public function connect() {
        $this->initialize();
        $this->dispatch();
    }

    public function initialize() {
        $this->initMode();
        $this->initAutoload();
        $this->initDatabaseTablesMetadataCache();
        $this->initDB();
        $this->initConfig();
        $this->initAuloadMP();

        //TODO: we will remove comments when zend fully implement localization for all exception messages.
        //Zend_Exception::localizeMessages(true);
        //Change Date format to PHP formats
        Zend_Date::setOptions(array('format_type' => 'php'));

        $this->initUser();
    }

    /**
     * Perform front end user CMS authentication. Should be overloaded by child classes.
     * This method make non abstract to prevent fatal error situations.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    function authenticate($username, $password) {
        return false;
    }

    /**
     * This method should check if user is already loginned into CMS and if
     * we have linked user in the Resmania we need to set this user object to RM_Reservation_Manager class.
     *
     * @return bool
     */
    public function initUser() {

        $config = new RM_Config();

        if ($this->_module == 'admin' && (int) $config->getValue('rm_config_enable_user_groups') === 0) {
            return;
        }

        $user = RM_Reservation_Manager::getInstance()->getUser();
        if ($user !== null)
            return;


        if ($config->getValue('rm_config_enable_cms_integration')) {
            $cmsUser = RM_Environment::getConnector()->getUser();
            if ($cmsUser->isGuest()) {
                return;
            }
            $user = $cmsUser->findResmaniaUser();
            if ($user == null) {
                $user = $cmsUser->convertToResmaniaUser();
            }
            RM_Reservation_Manager::getInstance()->setUser($user);
        }
    }

    public function dispatch() {
        $rootPath = $this->_rootPath;

        $modulesDAO = new RM_Modules();
        $modules = $modulesDAO->fetchAll();

        $pluginsDAO = new RM_Plugins();
        $plugins = $pluginsDAO->fetchAll();

        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setDispatcher($this->getDispatcher());

        $frontController->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
                    'module' => $this->getModule(),
                    'controller' => 'Error',
                    'action' => 'error'
                )));
        $frontController->setDefaultModule($this->_module);
        $frontController->throwExceptions(false);

        $controllerPathes = array(
            'admin' => array(),
            'user' => array()
        );
        $coreControllers = implode(DIRECTORY_SEPARATOR, array($rootPath, 'RM', 'system', 'application', 'controllers')) . DIRECTORY_SEPARATOR;
        $controllerPathes['admin'][] = $coreControllers;
        $controllerPathes['user'][] = $coreControllers;
        foreach ($modules as $module) {
            $controllerPathes['admin'][] = $controllerPathes['user'][] = RM_Module_Manager::getModuleFolderpath($module->name, $this->_rootPath) . DIRECTORY_SEPARATOR . RM_Module_Config::CONTROLLERS;
        }

        foreach ($plugins as $plugin) {
            $controllerPathes['admin'][] = $controllerPathes['user'][] = RM_Plugin_Manager::getPluginFolderpath($plugin->name, $this->_rootPath) . DIRECTORY_SEPARATOR . RM_Plugin_Config::CONTROLLERS;
        }

        $frontController->setControllerDirectory($controllerPathes);

        $scriptPathes = array();
        foreach ($modules as $module) {
            $scriptPathes[] = implode(DIRECTORY_SEPARATOR, array(
                        $rootPath, 'RM', 'userdata', 'modules', $module->name, RM_Module_Config::VIEWS, 'admin', 'scripts'
                    )) . DIRECTORY_SEPARATOR;

            $scriptPathes[] = implode(DIRECTORY_SEPARATOR, array(
                        $rootPath, 'RM', 'userdata', 'modules', $module->name, RM_Module_Config::VIEWS, 'user', 'scripts'
                    )) . DIRECTORY_SEPARATOR;
        }
        foreach ($plugins as $plugin) {
            $scriptPathes[] = implode(DIRECTORY_SEPARATOR, array(
                        $rootPath, 'RM', 'userdata', 'plugins', $plugin->name, RM_Plugin_Config::VIEWS, 'admin', 'scripts'
                    )) . DIRECTORY_SEPARATOR;

            $scriptPathes[] = implode(DIRECTORY_SEPARATOR, array(
                        $rootPath, 'RM', 'userdata', 'plugins', $plugin->name, RM_Plugin_Config::VIEWS, 'user', 'scripts'
                    )) . DIRECTORY_SEPARATOR;
        }
        $scriptPathes[] = implode(DIRECTORY_SEPARATOR, array($rootPath, 'RM', 'userdata', 'views', 'admin', 'scripts')) . DIRECTORY_SEPARATOR;
        $scriptPathes[] = implode(DIRECTORY_SEPARATOR, array($rootPath, 'RM', 'userdata', 'views', 'user', 'scripts')) . DIRECTORY_SEPARATOR;

        $view = new RM_View;
        $view->addScriptPath($scriptPathes);

        $viewRenderer = new RM_Controller_Action_Helper_ViewRenderer($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        $router = RM_Environment::getInstance()->getRouter();
        $frontController->setRouter($router);

        Zend_Layout::startMvc(array(
                    'layout' => $this->_module,
                    'layoutPath' => implode(DIRECTORY_SEPARATOR, array($rootPath, 'RM', 'userdata', 'layouts'))
                ));

        $model = new RM_Modules();
        $modules = $model->fetchAllEnabled();
        $manager = new RM_Module_Manager();

        foreach ($modules as $module) {
            $moduleObject = $manager->getModule($module->name);
            if ($moduleObject instanceof RM_System_Setup) {
                $moduleObject->preDispatch();
            }
        }

        $frontController->dispatch($this->getRequestHTTP());
    }

    public function initMode() {
        $configFile = implode(DIRECTORY_SEPARATOR, array($this->_rootPath, 'RM', 'system', 'config', 'core.config.ini'));
        $configSections = parse_ini_file($configFile, true);
        $mode = $configSections['main']['mode'];
        self::$_mode = $mode;
    }

    private function _getAutoloaderOptions() {
        $configFile = implode(DIRECTORY_SEPARATOR, array($this->_rootPath, 'RM', 'system', 'config', 'core.config.ini'));
        $configSections = parse_ini_file($configFile, true);

        $autoloaderCache = $configSections['production']['autoloader.cache'];
        if (self::$_mode !== 'production') {
            $mode = self::$_mode . ' : production';
            if (isset($configSections[$mode]['autoloader.cache'])) {
                $autoloaderCache = $configSections[$mode]['autoloader.cache'];
            }
        }

        if ($autoloaderCache == 1) {
            $fileDir = implode(DIRECTORY_SEPARATOR, array(
                        $this->_rootPath,
                        'RM',
                        'userdata',
                        'temp',
                        'classes'
                    ));
            if (is_dir($fileDir) == false) {
                mkdir($fileDir, 0755);
            }
            $filePath = $fileDir . DIRECTORY_SEPARATOR . 'cache.txt';
            if (is_file($filePath) == false) {
                touch($filePath);
                chmod($filePath, 0755);
            }
            if (is_file($filePath) && is_writable($filePath)) {
                return array(
                    'type' => RM_AutoLoader::CACHE_TYPE,
                    'filepath' => $filePath
                );
            }
        }

        return array(
            'type' => RM_AutoLoader::SIMPLE_TYPE
        );
    }

    public function initAutoload() {
        $rootPath = $this->_rootPath;

        $pathes = array();
        $pathes[] = implode(DIRECTORY_SEPARATOR, array($rootPath, 'RM', 'system', 'application', 'classes'));
        $pathes[] = implode(DIRECTORY_SEPARATOR, array($rootPath, 'RM', 'system', 'application', 'controllers'));
        $pathes[] = implode(DIRECTORY_SEPARATOR, array($rootPath, 'RM', 'system', 'application', 'models'));
        $pathes[] = implode(DIRECTORY_SEPARATOR, array($rootPath, 'RM', 'system', 'libs'));
        $pathes[] = implode(DIRECTORY_SEPARATOR, array($rootPath, 'RM', 'system', 'libs', 'Dwoo'));
        $pathes[] = implode(DIRECTORY_SEPARATOR, array($rootPath, 'RM', 'system', 'libs', 'pclZip'));
        $pathes = array_merge($pathes, $this->_getExtraPathes());
        $pathes[] = get_include_path();
        $pathString = implode(PATH_SEPARATOR, $pathes);
        set_include_path($pathString);

        //Old: Zend_Autoloader
        //include_once "Zend".DIRECTORY_SEPARATOR."Loader".DIRECTORY_SEPARATOR."Autoloader.php";
        //$autoloader = Zend_Loader_Autoloader::getInstance();
        //$autoloader->registerNamespace(array('RM_', 'Dwoo_'));

        include_once implode(DIRECTORY_SEPARATOR, array($rootPath, 'RM', 'system', 'application', 'classes', 'RM', 'AutoLoader.php'));
        RM_AutoLoader::init($this->_getAutoloaderOptions());

        include_once "Dwoo.php";
        include_once "pclzip.lib.php";
    }

    public function initDatabaseTablesMetadataCache() {
        //we could use cache only if safe_mode is off
        if (!ini_get('safe_mode')) {
            Zend_Db_Table_Abstract::setDefaultMetadataCache(
                            Zend_Cache::factory(
                                    'Core',
                                    'File',
                                    array('automatic_serialization' => true),
                                    array('cache_dir' => implode(DIRECTORY_SEPARATOR, array($this->_rootPath, 'RM', 'userdata', 'temp', 'models')))
                            )
            );
        }
    }

    public function initDB() {
        $db = Zend_Db::factory($this->_config->db->adapter, $this->_config->db->params);
        //TODO: add a 'charset' parameter into config, instead of the line below
        $db->getConnection()->query("SET NAMES 'utf8'");
        $db->getConnection()->query('SET CHARACTER SET \'utf8\'');
        Zend_Db_Table::setDefaultAdapter($db);
    }

    public function initConfig() {
        $coreConfig = new Zend_Config_Ini(
                        implode(DIRECTORY_SEPARATOR, array($this->_rootPath, 'RM', 'system', 'config', 'core.config.ini')),
                        self::$_mode,
                        array('allowModifications' => true)
        );
        Zend_Registry::set('config', $coreConfig->merge($this->_config));
    }

    public function isSafeMode() {
        if ($this->_safeMode == null) {
            $config = parse_ini_file(implode(DIRECTORY_SEPARATOR, array($this->_rootPath, 'RM', 'system', 'config', 'core.config.ini')), true);
            $mainConfig = $config['main'];
            $this->_safeMode = (bool) $mainConfig['safe_mode'];
        }
        return $this->_safeMode;
    }

    /**
     * Initialize autoloading for modules and plugins
     */
    public function initAuloadMP() {
        $isSafeMode = $this->isSafeMode();

        $paths = array();

        $modulesDAO = new RM_Modules();
        $modules = $modulesDAO->fetchAll();
        foreach ($modules as $module) {
            if ($isSafeMode && $module->core == 0) {
                continue;
            }
            $bothPath = RM_Module_Manager::getModuleFolderpath($module->name, $this->_rootPath) . DIRECTORY_SEPARATOR;
            $paths[] = $bothPath . RM_Module_Config::CLASSES;
            $paths[] = $bothPath . RM_Module_Config::CONTROLLERS;
        }

        $pluginsDAO = new RM_Plugins();
        $plugins = $pluginsDAO->fetchAll();
        foreach ($plugins as $plugin) {
            if ($isSafeMode && $plugin->core == 0) {
                continue;
            }
            $bothPath = RM_Plugin_Manager::getPluginFolderpath($plugin->name, $this->_rootPath) . DIRECTORY_SEPARATOR;
            $paths[] = $bothPath . RM_Plugin_Config::CLASSES;
            $paths[] = $bothPath . RM_Plugin_Config::CONTROLLERS;
        }

        $MPPathString = implode(PATH_SEPARATOR, $paths);
        set_include_path(get_include_path() . PATH_SEPARATOR . $MPPathString);
    }

    /**
     * Returns the run status
     *
     * @return string
     */
    public function getRunStatus() {
        $model = new RM_System();
        $data = $model->getRunStatus()->toArray();
        return (int) $data[0]['runstatus'];
    }

    protected function _getExtraPathes() {
        return array();
    }

    /**
     * This is a default value for unit list length for the User end.
     * This method could be overloaded in child classes to get default value from CMS.
     *
     * @return int
     */
    public function getDefaultUnitListLength() {
        return 20;
    }

    /**
     * Return Dispatcher Name
     *
     * @return object
     */
    public abstract function getRequestHTTP();

    /**
     * Returns user that is already loginned into CMS or Guest user.
     *
     * @return RM_User_CMS_Interface|null
     */
    public abstract function getUser();

    /**
     * Returns if the user has authenticated to the host CMS
     *
     * @return RM_User_CMS_Interface|null
     */
    public abstract function getLoginStatus();

    /**
     * !IMPORTANT! This method should be overloaded in child classes.
     * It's not an abstract to prevent fatal errors after core upgrade if there is an old
     * connector class in CMS module without this method implementing.
     * Returns all CMS users.
     *
     * @throw RM_Exception
     * @return array with RM_User_CMS_Interface objects
     */
    public function getUsers() {
        throw new RM_Exception('You need to upgrade CMS Resmania module to enable this feature.');
    }

    /**
     * Return Dispatcher Name
     *
     * @return object
     */
    public function getDispatcher() {
        $dispatcher = 'RM_Controller_Dispatcher_' . ucfirst(strtolower($this->_module));
        return new $dispatcher;
    }

    /**
     * Return current locale selected by user
     *
     * @return string
     */
    public function getLocale() {
        return $this->_locale;
    }

    /**
     * @return Zend_Controller_Router_Interface
     */
    public function getRouter() {
        return $this->_router;
    }

    /**
     * @return string
     */
    public function getRootPath() {
        return $this->_rootPath;
    }

    /**
     * Returns path to RM code root folder
     *
     * @return string
     */
    public function getCorePath() {
        return $this->getRootPath() . DIRECTORY_SEPARATOR . 'RM';
    }

    /**
     * @return string
     */
    public function getTempFolderPath() {
        $tempFolderName = 'temp';
        $tempFolderPath = $this->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . $tempFolderName;
        return $tempFolderPath;
    }

    /**
     * @return string
     */
    public function getModule() {
        return $this->_module;
    }

    /**
     * Install database     
     * @return null
     */
    public abstract function installDB();

    /**
     * Returns all js file urls (without root path URL) for back end inbluding
     *
     * @return array of file URLs
     */
    public abstract function getBackendJSFileURLs();

    /**
     * Uninstall database
     *
     * @return null
     */
    public abstract function uninstallDB();

    /**
     * Returns root URL for CMS
     *
     * @return string Root URL
     */
    public abstract function getRootURL();

    /**
     * Returns root admin URL for CMS
     *
     * @return string Root URL
     */
    public abstract function getAdminRootURL();

    /**
     * Returns root user URL for CMS
     *
     * @return string Root URL
     */
    public abstract function getUserRootURL();

    /**
     * @return RM_Users
     */
    public abstract function getUsersModel();

    public function getConfigValues() {
        // config values to be exposed to js
        $filter = '
            rm_config_admin_help_panel_enable,
            rm_config_calendar_startday,
            rm_config_dateformat
            ';

        $config = new RM_Config;
        $configValues = $config->get($filter);

        $configResult = array();
        foreach ($configValues as $index => $value) {
            $configResult[] = "'" . $index . "':'" . $value . "'";
        }
        return "RM.Config={" . implode(',', $configResult) . "}";
    }

    abstract public function getViewPortJsCode();

     /**
     * Sets the CMS Page Title
     */
    public abstract function setPageTitle($title);
}