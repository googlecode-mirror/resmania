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
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 *
 * Singleton class for wrapp all application global preferences
 *
 * @access       public
 * @author       Valentin/Rob
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 *
 * @example the basic example of use. Do not use a constuctor, it's private: use static method 'getInstance' instead
 * $priceSystem = RM_Environment::getInstance()->getPriceSystem();
 */

class RM_Environment {

    /**
     * Only one copy of object of this class in the whole application
     *
     * @var RM_Environment
     */
    private static $_instance;
    /**
     * Current selected locale
     * @var string
     */
    private $_locale = null;
    private $_language = null;

    const TRANSLATE_MAIN = 'main';
    const TRANSLATE_ERRORS = 'errors';
    const TRANSLATE_HELP = 'help';
    const TRANSLATE_LOCATIONS = 'locations';

    /*
     * update urls
     */
    private $_distoPoint = "http://distro.resmania.com/";
    private $_coreFileName = "core.zip";
    private $_coreConfigURL = "http://distro.resmania.com/config.json";
    private $_extensionVersionURL = "http://distro.resmania.com/extensions.json";
    /**
     * Application price system
     *
     * @var RM_Prices_Interface
     */
    private $_priceSystem;
    /**
     * Application payment system
     *
     * @var RM_Payment_Interface
     */
    private $_paymentSystem;
    /**
     * Application deposit system
     *
     * @var RM_Deposit_Plugin_Interface
     */
    private $_depositSystem;
    /**
     * Application tax system
     *
     * @var RM_Taxes_Interface
     */
    private $_taxSystem;
    /**
     * Application extras system. Only for extras applied to reservation directly.
     *
     * @var array or RM_Extras_Interface
     */
    private $_extrasSystems = array();
    /**
     * Application others system. Only for others applied to reservation directly.
     *
     * @var array or RM_Others_Interface
     */
    private $_othersSystems = array();
    /**
     * This is a full list of price plugins to collaborate with
     *
     * @var array
     */
    private $_discounts = array();
    /**
     * Application SEF manager
     *
     * @var RM_SEF_Manager_Interface
     */
    private $_sefManager;
    /**
     * Application SEF manager
     *
     * @var RM_Controller_Router_Interface
     */
    private $_router;
    private $_translations = array();

    /**
     * Returns the current CORE version
     * 
     * @return string
     */
    public function getVersion() {
        return Zend_Registry::get('config')->get('version');
    }

    /**
     * Returns the distribution point
     *
     * @return string
     */
    public function getDistroURL() {
        return $this->_distoPoint;
    }

    /**
     * Returns the zend package name
     *
     * @return string
     */
    public function getPackageName() {
        return $this->_coreFileName;
    }

    /**
     * Returns the config.json version file URL
     *
     * @return string
     */
    public function getVersionFileURL() {
        return $this->_coreConfigURL;
    }

    /**
     * Returns url to the site where user will be able to download the latest version of a core
     *
     * @todo this method should return real url later
     * @return string
     * @deprecated
     */
    public function getURLToCoreUpdate() {
        return "https://secure.resmania.com/downloads";
    }

    /**
     * Ruturn current enabled price system in application
     *
     * @return RM_Prices_Manager
     */
    public function getPriceSystem() {
        return RM_Prices_Manager::getInstance();
    }

    /**
     * Ruturn current enabled payment system in application
     *
     * @return RM_Payments_Interface
     */
    public function getPaymentSystem() {
        return $this->_paymentSystem;
    }

    /**
     * Return current enabled extras system
     *
     * @return array of RM_Extras_Interface
     */
    public function getExtrasSystems() {
        return $this->_extrasSystems;
    }

    /**
     * Return current enabled others system
     *
     * @return array of RM_Others_Interface
     */
    public function getOthersSystems() {
        return $this->_othersSystems;
    }

    /**
     * Returns all available discounts systems
     *
     * @return array
     */
    public function getDiscounts() {
        return $this->_discounts;
    }

    /**
     * Returns current locale
     *
     * @param bool $languageOnly - optional: default = false, if true returns only language iso code, 'en' for example 
     * @return string
     */
    public function getLocale($languageOnly = false) {
        if ($this->_locale == null || $this->_language == null) {
            $this->_initializeLocale();
        }

        if ($languageOnly) {
            return $this->_language;
        }
        return $this->_locale;
    }

    /**
     * Get current selected locale
     *
     * @return void
     */
    private function _initializeLocale() {
        $config = new RM_Config();
        if (RM_Environment::getConnector()->getModule() == 'admin') {
            $locale = $config->getValue('rm_config_language_default_back');
        } elseif (RM_Environment::getConnector()->getModule() == 'user') {
            $locale = $config->getValue('rm_config_language_default_front');
        }
        if ($locale == null || $locale == 'cms') {
            $locale = RM_Environment::getConnector()->getLocale();
        }

        $this->_locale = $locale;

        list($language, $translation) = explode('_', $locale);
        $this->_language = $language;
    }

    /**
     * Return current enabled tax system in application
     *
     * @return RM_Taxes_Interface
     */
    public function getTaxSystem() {
        return $this->_taxSystem;
    }

    /**
     * Return current deposit system. Last enabled deposit system in application
     *
     * @return RM_Deposit_Plugin_Interface
     */
    public function getDepositSystem() {
        return $this->_depositSystem;
    }

    /**
     * Returns current connector object depending on CMS
     * 
     * @return RM_Connector
     */
    public static function getConnector() {
        return RM_Connector::getInstance();
    }

    /**
     * Check current version, if current version is lower than latest one from repository, this will returns true.
     *
     * @return false|string - latest version or string or false
     */
    public function checkVersion() {
        $latestVersion = $this->getLatestVersion();
        if (version_compare($this->getVersion(), $latestVersion, '<')) {
            return $latestVersion;
        }
        return false;
    }

    /**
     * Round prices and format it for the view
     *
     * @todo add the configuration option below.
     * @param float|int $number price
     * @return float
     */
    public function roundPrice($price) {
        //$config = new RM_Config();
        //$config->getValue('m_config_currency_decimalplaces');
        return number_format(round($price, 2), 2, '.', '');
    }

    /**
     * @deprecated
     * @param  $url
     * @return array|mixed
     */
    private function _getExtensionVersions($url) {
        $configJson = @file_get_contents($url);
        try {
            if (isset($configJson)) {
                $config = Zend_Json::decode($configJson, Zend_Json::TYPE_ARRAY);
                return $config;
            } else {
                return array();
            }
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Returns latest plugins versions in format: <plugin name> => '<version number in std php format>'
     *
     * @deprecated
     * @return array|null
     */
    public function getPluginsVersions() {
        return $this->_getExtensionVersions($this->_pluginsVersionURL);
    }

    /**
     * Returns latest modules versions in format: <module name> => '<version number in std php format>' 
     *
     * @deprecated
     * @return array|null
     */
    public function getModuleVersions() {
        return $this->_getExtensionVersions($this->_modulesVersionURL);
    }

    /**
     * Clear all cache files.
     *
     * @return void
     */
    public function clearCache() {
        $cacheDir = self::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        RM_Filesystem::emptyFolder($cacheDir . DIRECTORY_SEPARATOR . 'cache');
        RM_Filesystem::emptyFolder($cacheDir . DIRECTORY_SEPARATOR . 'css');
        RM_Filesystem::emptyFolder($cacheDir . DIRECTORY_SEPARATOR . 'classes');
        RM_Filesystem::emptyFolder($cacheDir . DIRECTORY_SEPARATOR . 'extensions');
        RM_Filesystem::emptyFolder($cacheDir . DIRECTORY_SEPARATOR . 'language');
        RM_Filesystem::emptyFolder($cacheDir . DIRECTORY_SEPARATOR . 'models');
    }

    public function getWelcomeFeed() {
        $frontendName = 'Core';
        $frontendOptions = array();
        $frontendOptions['lifetime'] = Zend_Registry::get('config')->get('cache')->get('lifetime');

        $backendName = 'File';
        $backendOptions = array();

        $cacheDir = RM_Environment::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'cache';
        if (is_dir($cacheDir) == false) {
            $rmConfig = new RM_Config();
            $chmodOctal = intval($rmConfig->getValue('rm_config_chmod_value'), 8);
            mkdir($cacheDir, $chmodOctal);
        }
        $backendOptions['cache_dir'] = $cacheDir;

        $backendOptions['file_name_prefix'] = 'rm_welcome_cache';

        $cacheName = 'welcome';
        $cache = Zend_Cache::factory($frontendName, $backendName, $frontendOptions, $backendOptions);
        if ($pagecontent = $cache->load($cacheName)) {
            return $pagecontent;
        }

        $pagecontent = @file_get_contents('http://resmania-eu.s3.amazonaws.com/resources/welcome_msg_v1.2.html');
        if (!$pagecontent) {
            $pagecontent = "<div class='RM_HomePageFeed_notavailable_msg'>".$this->getTranslation()->_('Admin.Homepage', 'NotAvailableMsg')."</div>";
        } else {
            $cache->save($pagecontent, $cacheName);
        }
        return $pagecontent;
    }

    /**
     * Returns latest extensions versions in format:
     * array(
     *  modules =>
     *      [ <module name> => '<version number in std php format>' ]
     *  plugins =>
     *      [ <plugin name> => '<version number in std php format>' ]
     * )
     * or null is connection to server is lost
     *
     * @return array|null
     */
    public function getExtensionVersions() {
        $frontendName = 'Core';
        $frontendOptions = array();
        $frontendOptions['lifetime'] = Zend_Registry::get('config')->get('cache')->get('lifetime');
        $frontendOptions['automatic_serialization'] = true;

        $backendName = 'File';
        $backendOptions = array();

        $cacheDir = self::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'extensions';
        if (is_dir($cacheDir) == false) {
            $rmConfig = new RM_Config();
            $chmodOctal = intval($rmConfig->getValue('rm_config_chmod_value'), 8);
            mkdir($cacheDir, $chmodOctal);
        }
        $backendOptions['cache_dir'] = $cacheDir;
        $backendOptions['file_name_prefix'] = 'rm_extensions_cache';

        $cacheName = 'versions';
        $cache = Zend_Cache::factory($frontendName, $backendName, $frontendOptions, $backendOptions);
        if ($config = $cache->load($cacheName)) {
            return $config;
        }

        $configJson = @file_get_contents($this->_extensionVersionURL);
        try {
            if (isset($configJson) && $configJson !== false) {
                $config = Zend_Json::decode($configJson, Zend_Json::TYPE_ARRAY);
                $cache->save($config, $cacheName);
                return $config;
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Returns extension names that are out of date and there are new versions of them on the resmania site
     *
     * @return array - array in format
     * [ modules => array() , plugins => array() ]
     */
    public function getOutOfDateExtensions() {
        $pluginsModel = new RM_Plugins();

        $result = array('modules' => array(), 'plugins' => array());
        $versions = $this->getExtensionVersions();
        foreach ($versions['plugins'] as $pluginInfo) {
            $plugin = $pluginsModel->fetchByName($pluginInfo['name']);
            if ($plugin == null) {
                continue;
            }
            if (version_compare($plugin->version, $pluginInfo['version'], '<')) {
                $result['plugins'][] = $pluginInfo['name'];
            }
        }

        $modulesModel = new RM_Modules();
        foreach ($versions['modules'] as $moduleInfo) {
            $module = $modulesModel->fetchByName($moduleInfo['name']);
            if ($module == null) {
                continue;
            }
            if (version_compare($module->version, $moduleInfo['version'], '<')) {
                $result['modules'][] = $moduleInfo['name'];
            }
        }

        return $result;
    }

    /**
     * Returns latest version from Webformatique repository
     *
     * @todo this method need a body to real checking the latest version
     * @return string
     */
    public function getLatestVersion() {

        // get the override upgrade version if set
        $overrideUpgradeVersion = Zend_Registry::get('config')->get('upgrade')->get('version');
        if ($overrideUpgradeVersion != 0) {
            return trim($overrideUpgradeVersion);
        }

        $configJson = @file_get_contents($this->_coreConfigURL);
        try {
            if (isset($configJson)) {
                $config = Zend_Json::decode($configJson, Zend_Json::TYPE_OBJECT);
                if (isset($config->version)) {
                    $version = $config->version;
                } else {
                    $version = 0; // return a low version so the upgrade notice is not returned
                }
            } else {
                $version = 0; // return a low version so the upgrade notice is not returned
            }
        } catch (Exception $e) {
            $version = 0; // return a low version so the upgrade notice is not returned
        }

        return $version;
    }

    /**
     * Return current enabled captcha manager
     *
     * @return RM_Captcha_Interface
     */
    public function getCaptcha() {
        //TODO: we need to add extra code for selection current captcha manager depending on user choice
        return new RM_Captcha_Recaptcha();
    }

    /**
     * Return router interface
     *
     * @return RM_Controller_Router_Interface
     */
    public function getRouter() {
        if ($this->_router == null) {
            $this->_router = RM_Environment::getConnector()->getRouter();
            if ($this->_sefManager !== null && $this->_sefManager instanceof RM_SEF_Manager_Interface) {
                $router = $this->_sefManager->getRouter();
                if ($router !== null) {
                    $this->_router = $router;
                }
            }
        }
        return $this->_router;
    }

    /**
     * Private constructor that invokes only from 'getInstance' method
     */
    private function __construct() {
        $this->_initializeLocale();

        $model = new RM_Modules();
        $allModules = $model->fetchAll();
        $modules = $model->fetchAllEnabled();

        foreach ($modules as $module) {
            $moduleObject = RM_Module_Manager::getModule($module->name);
            //We will get the last enabled module in the system
            //that implements one of our internal interfaces
            if ($moduleObject instanceof RM_Payments_Interface) {
                $this->_paymentSystem = $moduleObject;
            }
            if ($moduleObject instanceof RM_Extras_Interface) {
                $this->_extrasSystems[] = $moduleObject;
            }
            if ($moduleObject instanceof RM_Others_Interface) {
                $this->_othersSystems[] = $moduleObject;
            }
            if ($moduleObject instanceof RM_SEF_Manager_Interface) {
                $this->_sefManager = $moduleObject;
            }
        }

        $pluginModel = new RM_Plugins();
        $allPlugins = $pluginModel->fetchAll();
        $plugins = $pluginModel->fetchAllEnabled();

        $this->_taxSystem = new RM_Taxes_Default;
        foreach ($plugins as $plugin) {
            $pluginObject = RM_Plugin_Manager::getPlugin($plugin->name);
            if ($pluginObject instanceof RM_Taxes_Interface) {
                $this->_taxSystem = $pluginObject;
            }
            if ($pluginObject instanceof RM_Discounts_Plugin_Interface) {
                $this->_discounts[] = $pluginObject;
            }
            //We will assign only one deposit system (the last one enabled)
            if ($pluginObject instanceof RM_Deposit_Plugin_Interface) {
                $this->_depositSystem = $pluginObject;
            }
        }

        $this->_initializeTranslate($allModules, $allPlugins);
    }

    /**
     * Returns translate object initialized with need constans
     *
     * @param  $type - one of the translation type, see this class constants
     * @param  $translatePaths
     * @return Zend_Translate
     */
    private function _getTranslation($type, $translatePaths) {
        $ignore = array(
            RM_Environment::TRANSLATE_MAIN,
            RM_Environment::TRANSLATE_ERRORS,
            RM_Environment::TRANSLATE_HELP,
            RM_Environment::TRANSLATE_LOCATIONS,
            '.',
            '.svn'
        );
        $ignoreTypes = array_diff($ignore, array($type));
        return new Zend_Translate(array(
            'adapter' => 'RM_Translate_Adapter_Text',
            'content' => $translatePaths,
            'locale' => $this->getLocale(),
            'scan' => Zend_Translate::LOCALE_DIRECTORY,
            'ignore' => $ignoreTypes,
            'type' => RM_Translate_Adapter_Text::FOLDERS
        ));
    }

    /**
     * Initialize translate constants using Zend_Cache cache
     *
     * @param  array $translatePaths - all paths for translate files (core+modules+plugins)
     * @return void
     */
    private function _initializeTranslateFilesCache($translatePaths) {
        $frontendName = 'Core';
        $frontendOptions = array();
        $frontendOptions['lifetime'] = Zend_Registry::get('config')->get('cache')->get('lifetime');
        $frontendOptions['automatic_serialization'] = true;

        $backendName = 'File';
        $backendOptions = array();
        $backendOptions['cache_dir'] = self::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'language';
        $backendOptions['file_name_prefix'] = 'rm_zend_cache';

        $cache = Zend_Cache::factory($frontendName, $backendName, $frontendOptions, $backendOptions);
        //Zend_Translate::setCache($cache); // this is a new option that we didn't check

        $types = array(RM_Environment::TRANSLATE_MAIN, RM_Environment::TRANSLATE_ERRORS, RM_Environment::TRANSLATE_HELP);
        foreach ($types as $type) {
            $cacheName = $type . '_' . self::getConnector()->getModule() . '_' . $this->getLocale();
            if (!($translation = $cache->load($cacheName))) {
                $translation = $this->_getTranslation($type, $translatePaths);
                $cache->save($translation, $cacheName);
            }
            $this->_translations[$type] = $translation;
        }
    }

    /**
     * Initialize translate constants without cache
     *
     * @param  array $translatePaths - all pathers for translate files (core+modules+plugins)
     * @return void
     */
    private function _initializeTranslateFiles($translatePaths) {
        $types = array(RM_Environment::TRANSLATE_MAIN, RM_Environment::TRANSLATE_ERRORS, RM_Environment::TRANSLATE_HELP);
        foreach ($types as $type) {
            $this->_translations[$type] = $this->_getTranslation($type, $translatePaths);
        }
    }

    /**
     * Initializes translation files. 
     * At first this checks cache if there is no this data there, then parse them.
     *
     * @param array $module - array of module objects
     * @param array $plugins - array of plugin objects
     */
    private function _initializeTranslate($modules, $plugins) {
        $translatePaths = array();
        $translatePaths[] = self::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR;

        foreach ($modules as $module) {
            $translatePaths[] = self::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module->name . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR;
        }

        foreach ($plugins as $plugin) {
            $translatePaths[] = self::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin->name . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR;
        }

        if (ini_get('safe_mode')) {
            $this->_initializeTranslateFiles($translatePaths);
        } else {
            $this->_initializeTranslateFilesCache($translatePaths);
        }
    }

    /**
     * Returns translation object with all language constants
     *
     * @param string $type one of the RM_Environment class constants:
     * RM_Environment::TRANSLATE_MAIN
     * RM_Environment::TRANSLATE_ERRORS
     * RM_Environment::TRANSLATE_HELP
     *
     * @return Zend_Translate
     */
    public function getTranslation($type = RM_Environment::TRANSLATE_MAIN) {
        return $this->_translations[$type];
    }

    /**
     * The only one method to return the instance of this class, 'cause constructor is private
     *
     * @return RM_Environment
     */
    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Returns HTML code for css files inclusion
     *
     * @param array $cssLibFiles - array with filepathes relateive from RM root folder
     * @return string|void HTML code for css inclusion
     */
    public function includeCssLibFiles($cssLibFiles) {
        if ((int) Zend_Registry::get('config')->get('css')->get('combined') !== 1) {
            return $this->_includeCssLibFilesInOldStyle($cssLibFiles);
        }

        $folders = $this->_combineCssLibFiles($cssLibFiles);
        $combinedFileName = "combined.css";
        $includeFiles = array();
        foreach ($folders as $folderPath => $files) {
            if (count($files) == 1) {
                $includeFiles[] = RM_Environment::getConnector()->getRootURL() . '/' . implode('/', explode(DIRECTORY_SEPARATOR, $folderPath)) . '/' . $files[0];
                continue;
            }
            $fullFolderPath = RM_Environment::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . $folderPath;
            $fullCombinedFilePath = $fullFolderPath . DIRECTORY_SEPARATOR . $combinedFileName;
            if (file_exists($fullCombinedFilePath) == false) {
                $handle = @fopen($fullCombinedFilePath, 'w');
                if (!$handle) {
                    RM_Log::toLog('Could not create file: ' . $fullCombinedFilePath, Zend_Log::WARN);
                    foreach ($files as $file) {
                        $includeFiles[] = RM_Environment::getConnector()->getRootURL() . '/' . implode('/', explode(DIRECTORY_SEPARATOR, $folderPath)) . '/' . $file;
                    }
                    continue;
                }
                foreach ($files as $file) {
                    $fullFilePath = $fullFolderPath . DIRECTORY_SEPARATOR . $file;
                    if (file_exists($fullFilePath) == false) {
                        continue;
                    }
                    if (is_readable($fullFilePath) == false) {
                        $includeFiles[] = RM_Environment::getConnector()->getRootURL() . '/' . implode('/', explode(DIRECTORY_SEPARATOR, $fullFilePath));
                        continue;
                    }
                    fwrite($handle, file_get_contents($fullFilePath));
                }
                fclose($handle);
            }
            $includeFiles[] = RM_Environment::getConnector()->getRootURL() . '/' . implode('/', explode(DIRECTORY_SEPARATOR, $folderPath)) . '/' . $combinedFileName;
        }

        $html = array();
        foreach ($includeFiles as $includeFile) {
            $html[] = '<link rel="stylesheet" type="text/css" href="' . $includeFile . '"/>';
        }
        return implode("\n", $html);
    }

    /**
     * Combine together css files from one folder
     *
     * @param  $cssLibFiles
     * @return array in format - <path to folder> => array(<file1>, <file2>)
     */
    private function _combineCssLibFiles($cssLibFiles) {
        $folders = array();
        foreach ($cssLibFiles as $cssLibFile) {
            $chunks = explode('/', $cssLibFile);
            $fileName = $chunks[count($chunks) - 1];
            unset($chunks[count($chunks) - 1]);
            $dirName = implode(DIRECTORY_SEPARATOR, $chunks);

            if (isset($folders[$dirName]) == false) {
                $folders[$dirName] = array();
            }
            $folders[$dirName][] = $fileName;
        }
        return $folders;
    }

    /**
     * !!!IMPORTANT!!! This will not work on IE, 'cause there is a limit (31) for css include files
     *
     * @param  $cssLibFiles
     * @return void
     */
    private function _includeCssLibFilesInOldStyle($cssLibFiles) {
        $html = array();
        foreach ($cssLibFiles as $cssFile) {
            $html[] = '<link rel="stylesheet" type="text/css" href="' . RM_Environment::getConnector()->getRootURL() . $cssFile . '"/>';
        }
        return implode("\n", $html);
    }

}