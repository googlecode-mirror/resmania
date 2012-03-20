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
* ResMania PHP autoloader class
*
* @access       public
* @author       Rob/Valentin
* @copyright    2011 ResMania Ltd.
* @version      1.2
* @link         http://docs.resmania.com/api/
* @since        06-2011
*/

class RM_AutoLoader
{
    const CACHE_TYPE = 'cache';
    const SIMPLE_TYPE = 'simple';

    /**
     * @var RM_Loader
     */
    protected static $_instance;

    protected function __construct($rootPath){}

    static function init($options)
    {
        switch ($options['type']) {
            case self::CACHE_TYPE:
                self::$_instance = new RM_Cache_Loader($options['filepath']);
                break;
            case self::SIMPLE_TYPE:
                self::$_instance = new RM_Simple_Loader();
                break;
            default:
                throw new RM_Exception('Wrong autoloader type');                
        }
        spl_autoload_register(array('RM_AutoLoader', 'autoload'));
    }

    static function clearCache()
    {
        self::$_instance->clearCache();
    }

    public static function autoload($class)
    {
        try {
            @self::$_instance->autoload($class);
            if (!class_exists($class, false) && !interface_exists($class, false)) {
                return false;
            }
            return $class;
        } catch (Exception $e) {
            return false;
        }
    }
}

abstract class RM_Loader
{
    protected function _getClassPath($class)
    {
        $classPath = explode('_', $class);
        $classPath[count($classPath) - 1] .= '.php';
        return implode(DIRECTORY_SEPARATOR, $classPath);
    }

    abstract function autoload($class);

    public function clearCache()
    {
        return true;
    }
}

class RM_Simple_Loader extends RM_Loader
{
    public function autoload($class)
    {
        $classPath = $this->_getClassPath($class);
        include_once $classPath;
    }
}

class RM_Cache_Loader extends RM_Loader
{
    private $_cache = array();
    private $_filepath;

    public function clearCache()
    {
        $this->_cache = array();
        $this->_saveCache();
    }

    public function __construct($filepath)
    {
        $this->_filepath = $filepath;
        $this->_loadCache();
    }

    protected function _findClassFile($class)
    {
        $classFilepath = $this->_getClassPath($class);
        $paths = explode(PATH_SEPARATOR, get_include_path());
        for ($i = 0; $i < count($paths); $i++) {
            $path = rtrim($paths[$i], DIRECTORY_SEPARATOR);

            if (file_exists($path.DIRECTORY_SEPARATOR.$classFilepath)) {
                return $path.DIRECTORY_SEPARATOR.$classFilepath;
            }
        }
        return false;
    }

    public function autoload($class)
    {
        if (isset($this->_cache[$class]) == false) {
            $path = $this->_findClassFile($class);
            if ($path === false) {
                return false;
            }
            $this->_cache[$class] = array(dirname($path), basename($path));
        }
        list($dirName, $fileName) = $this->_cache[$class];

        $paths = get_include_path();
        set_include_path($dirName.PATH_SEPARATOR.$paths);
        include_once $fileName;
        set_include_path($paths);
    }

    protected function _loadCache()
    {
        if (file_exists($this->_filepath) == false) {
            return false;
        }

        if (is_writeable($this->_filepath) == false) {
            return false;
        }

        $contents = file_get_contents($this->_filepath);
        if ($contents === false) {
            return false;
        }

        $contents = trim($contents);        
        if ($contents !== ''){
            $this->_cache = unserialize($contents);
        }
        return true;
    }

    protected function _saveCache()
    {
        if (file_exists($this->_filepath) == false) {
            return false;
        }

        if (is_writeable($this->_filepath) == false) {
            return false;
        }

        file_put_contents($this->_filepath, serialize($this->_cache));
        return true;
    }

    function __destruct()
    {
        $this->_saveCache();
    }
}