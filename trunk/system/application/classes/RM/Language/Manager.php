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
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Language_Manager
{
    /**
     * Path to a core language folder
     *
     * @var string
     */
    private $_folderPath;

    /**
     * Path to a core language cache folder
     *
     * @var string
     */
    private $_cacheFolderPath;

    /**
     * Path to a userdata folder
     *
     * @var string
     */
    private $_userdataFolderPath;

    /**
     * Path to language icon folder
     *
     * @var string
     */
    private $_iconFolderPath;

    /**
     * Config filename where all language settings stored
     *
     * @var string
     */
    private static $_configFilename = 'config.ini';

    /**
     * Form name on GUI upload page
     *
     * @var string
     */
    private static $_formName = 'language_upload';

    /**
     * Array of language files, 'cause we store different text constants in different files
     *
     * @var array
     */
    public static $files = array(
        'main.ini',
        'help.ini',
        'locations.ini',
        'errors.ini'
    );

    public static $extensionFiles = array(
        'main.ini',
        'help.ini',        
        'errors.ini'
    );

    const TYPE_MAIN = 'main';
    const TYPE_PLUGIN = 'plugins';
    const TYPE_MODULE = 'modules';

    /**
     * Constructor
     *
     * Setup all internal object properties
     */
    public function __construct()
    {
        $corePath = RM_Environment::getConnector()->getCorePath();
        $this->_folderPath = $corePath.DIRECTORY_SEPARATOR.'userdata'.DIRECTORY_SEPARATOR.'languages';
        $this->_userdataFolderPath = $corePath.DIRECTORY_SEPARATOR.'userdata';
        $this->_iconFolderPath = $corePath.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'language';
        $this->_cacheFolderPath = implode(DIRECTORY_SEPARATOR, array(
            $corePath,
            'userdata',
            'temp',
            'language'
        ));
    }

    /**
     * Store language file content
     *
     * @param string $type should be one of the type constants of this class     
     * @param string $iso ISO language code
     * @param string $filename filename
     * @param string $content file content
     * @param string $name name of the type - could be name of the plugin or module
     * @return bool
     */
    public function saveLanguageFileContent($type, $iso, $filename, $content, $name = null)
    {
        if (in_array($filename, self::$files) == false) return false;
        
        $isoFolder = $this->getFolder($type, $iso, $name);
        $filepath = $isoFolder.DIRECTORY_SEPARATOR.$filename;
        if (is_file($filepath) == false) return false;

        return file_put_contents($filepath, $content);
    }

    /**
     * Returns full file content
     *
     * @param string $type should be one of the type constants of this class     
     * @param string $iso ISO language code
     * @param string $filename filename
     * @param string $name name of the type - could be name of the plugin or module
     * @return string - full file content
     */
    public function getLanguageFileContent($type, $iso, $filename, $name = null)
    {
        if (in_array($filename, self::$files) == false) return false;
       
        $isoFolder = $this->getFolder($type, $iso, $name);
        $filepath = $isoFolder.DIRECTORY_SEPARATOR.$filename;
        if (is_file($filepath) == false) return false;
        $content = file_get_contents($filepath);
        return $content;
    }

    /**
     * Completely delete a language from the system: all database information, all language files from code and from all modules/plugins
     *
     * @param string $iso language ISO code
     * @return bool
     */
    public function deleteLanguage($iso)
    {
        $languageModel = new RM_Languages();
        if ($iso == $languageModel->getDefaultLocale()) {
            throw new RM_Exception($this->_translate->_('Admin.System.Language', 'CantDeleteDefaultLanguage'));
            return false;
        }

        $model = new RM_Languages;
        $language = $model->find($iso)->current();
        if ($language == null) {
            throw new RM_Exception($this->_translate->_('Admin.System.Language', 'WrongISOCode'));
            return false;
        }
        $language->delete();
        $this->_deleteLanguageFiles($iso);        

        $model = new RM_UnitTypes();
        $model->deleteLanguage($iso);

        $unitModel = new RM_UnitLanguageDetails();
        $unitModel->deleteLanguage($iso);

        $templatesModel = new RM_Templates();
        $templatesModel->deleteLanguage($iso);

        $manager = new RM_Module_Manager;
        $manager->deleteLanguage($iso);

        $pluginManager = new RM_Plugin_Manager();
        $pluginManager->deleteLanguage($iso);
    }   

    /**
     * Delete all files related to language with folder
     *
     * @param string $iso
     * @return null
     */
    private function _deleteLanguageFiles($iso)
    {
        $languageFolder = $this->_folderPath.DIRECTORY_SEPARATOR.$iso;
        //Delete all language files with folder
        RM_Filesystem::deleteFolder($languageFolder);

        //Delete language icon
        RM_Filesystem::deleteFile($this->getIconPath($iso));        
    }

    /**
     * Return icon filename by ISO code
     *
     * @param string $iso ISO language code
     * @return string icon filename
     */
    public function getIconFilename($iso)
    {
        return $iso.'.png';
    }    

    /**
     * Returns full path to language icon file
     *
     * @param string $iso ISO language code
     * @return string path to language icon
     */
    public function getIconPath($iso)
    {
        return $this->_iconFolderPath.DIRECTORY_SEPARATOR.$this->getIconFilename($iso);
    }

    /**
     * Returns full path to language folder
     *
     * @param string $iso ISO language code
     * @return string full path to language folder
     */
    public function getLanguageFolder($iso)
    {
        return $this->_folderPath.DIRECTORY_SEPARATOR.$iso;
    }      

    /**
     * Returns full path to language folder
     *
     * @param string $type should be one of the type constants of this class     
     * @param string $iso ISO language code
     * @param string $name name of the type - could be name of the plugin or module
     * @return string full path to language folder
     */
    public function getFolder($type, $iso, $name = null)
    {
        if ($type == self::TYPE_MAIN) return $this->getLanguageFolder($iso);
        return $this->_userdataFolderPath.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR.$iso;
    }      

    /**
     * Add language to the system, laguage files need to be in there places on language folder
     *
     * @param string $iso
     */
    public function installLanguage($iso, $name)
    {                
        $model = new RM_Languages;
        $language = array(
            'iso' => $iso,
            'name' => $name,
            'icon' => $this->getIconPath($iso)
        );
        $model->insert($language);

        //Here is a list of multilingual code models
        $model = new RM_UnitTypes();
        $model->addLanguage($iso);

        $unitModel = new RM_UnitLanguageDetails();
        $unitModel->addLanguage($iso);

        $templatesModel = new RM_Templates();
        $templatesModel->addLanguage($iso);

        $manager = new RM_Module_Manager;
        $manager->addLanguage($iso);

        $pluginManager = new RM_Plugin_Manager();
        $pluginManager->addLanguage($iso);
    }   

    /**
     * Temp folder name
     *
     * @return string
     */
    protected static function _createTempFolderName()
    {
        return (string)time();
    }

    /**
     * Upload zip srchive with all language files and config.ini
     *
     * @return array array($iso, $name) ISO code of uploaded language and language name on this language
     */
    function upload()
    {
        //0. Create temp folder
        $folderName = self::_createTempFolderName(); //We create a name in a timestamp

        $rmConfig = new RM_Config();
        $chmodOctal = intval($rmConfig->getValue('rm_config_chmod_value'),8);

        $tempFolderPath = RM_Environment::getConnector()->getTempFolderPath().DIRECTORY_SEPARATOR.$folderName;
        if (mkdir($tempFolderPath, $chmodOctal) == false){
            throw new RM_Exception($this->_translate->_('Admin.System.Language', 'CantCreateTempFolder'));
        }

        //1. Upload zip file
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
            $message = $this->_translate->_('Admin.System.Language', 'UploadFailed');
            $message.= '. '.implode("; ", $adapter->getMessages());
            throw new RM_Exception($message);                        
        }

        //2. Unpack it
        $files = $adapter->getFileInfo();
        $tempFileName = $files[self::$_formName]['name'];
        $tempFilePath = $tempFolderPath.DIRECTORY_SEPARATOR.$tempFileName;
        if (!extension_loaded('zlib')) {            
            unlink($tempFilePath);
            RM_Filesystem::deleteFolder($tempFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.System.Language', 'ZlibNotSupported'));
        }

        $zip = new PclZip($tempFilePath);
        $userDataFolder = RM_Environment::getConnector()->getCorePath().DIRECTORY_SEPARATOR.'userdata';
        $result = $zip->extract(
            PCLZIP_OPT_PATH, $userDataFolder,
            PCLZIP_OPT_SET_CHMOD, $chmodOctal
        );
        unlink($tempFilePath);
        RM_Filesystem::deleteFolder($tempFolderPath);
        if (!$result) {
            throw new RM_Exception($this->_translate->_('Admin.System.Language', 'UnzipFailed'));
        }

        //3. Parse config.ini file
        $iniFilePath = $userDataFolder.DIRECTORY_SEPARATOR.self::$_configFilename;
        if (is_file($iniFilePath) == false) {
            RM_Filesystem::deleteFolder($tempFolderPath);
            throw new RM_Exception($this->_translate->_('Admin.System.Language', 'NoConfigIniFile'));
        }        
        $parser = new RM_Language_Config_Parser();
        try {
            $config = $parser->getConfig($iniFilePath);
        } catch (RM_Exception $e) {            
            RM_Filesystem::deleteFolder($tempFolderPath);
            throw new RM_Exception($e->getMessage());
        }

        //4. Get iso
        $iso = $config->iso;        
        $name = $config->name;
        unlink($iniFilePath);
        return array($iso, $name);
    }

    /**
     * Clears the language cache files
     * @todo we need to use Zend cache methods to made this code more clear.
     */
    public function clearCache(){        
        return RM_Filesystem::emptyFolder($this->_cacheFolderPath);
    }
}