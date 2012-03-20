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
abstract class RM_Controller_Dispatcher extends Zend_Controller_Dispatcher_Standard {
    /**
     * Default controller
     * @var string
     */
    protected $_defaultController = 'Index';

    /**
     * Returns TRUE if the Zend_Controller_Request_Abstract object can be
     * dispatched to a controller.
     *
     * Use this method wisely. By default, the dispatcher will fall back to the
     * default controller (either in the module specified or the global default)
     * if a given controller does not exist. This method returning false does
     * not necessarily indicate the dispatcher will not still dispatch the call.
     *
     * @param Zend_Controller_Request_Abstract $action
     * @return boolean
     */
    public function isDispatchable(Zend_Controller_Request_Abstract $request)
    {
        $className = $this->getControllerClass($request);
        if (!$className) {
            return false;
        }

        if (class_exists($className, false)) {
            return true;
        }

        $fileSpec    = $this->classToFilename($className);
        $dispatchDirs = $this->getDispatchDirectory();
        
        $totalResult = false;
        foreach ($dispatchDirs as $dispatchDir) {
            $test        = $dispatchDir . DIRECTORY_SEPARATOR . $fileSpec;
            $result = Zend_Loader::isReadable($test);
            $totalResult = $totalResult || $result;
        }
        
        return $totalResult;
    }

    /**
     * Add a single path to the controller directory stack
     *
     * @param string $path
     * @param string $module
     * @return Zend_Controller_Dispatcher_Standard
     */
    public function addControllerDirectory($path, $module = null)
    {
        if (null === $module) {
            $module = $this->_defaultModule;
        }

        $module = (string) $module;
        $path   = rtrim((string) $path, '/\\');

        if (isset($this->_controllerDirectory[$module]) == false) {
            $this->_controllerDirectory[$module] = array();
        }

        $this->_controllerDirectory[$module][] = $path;
        return $this;
    }

    /**
     * Load a controller class
     *
     * Attempts to load the controller class file from
     * {@link getControllerDirectory()}.  If the controller belongs to a
     * module, looks for the module prefix to the controller class.
     *
     * @param string $className
     * @return string Class name loaded
     * @throws Zend_Controller_Dispatcher_Exception if class not loaded
     */
    public function loadClass($className)
    {
        $finalClass  = $className;
        if (($this->_defaultModule != $this->_curModule)
            || $this->getParam('prefixDefaultModule'))
        {
            $finalClass = $this->formatClassName($this->_curModule, $className);
        }
        if (class_exists($finalClass, false)) {
            return $finalClass;
        }

        $dispatchDirs = $this->getDispatchDirectory();
        
        $fileLoaded = false;
        $classLoaded = false;
        foreach ($dispatchDirs as $dispatchDir) {
            $loadFile    = $dispatchDir . DIRECTORY_SEPARATOR . $this->classToFilename($className);            
            if (is_file($loadFile)) {
                include_once $loadFile;
                $fileLoaded = true;
            } else {
                continue;
            }

            if (class_exists($finalClass, false)) {
                $classLoaded = true;
                break;
            } else {                
                continue;
            }
        }

        if ($fileLoaded == false) {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception('Cannot load controller class "' . $className . '"');
        }

        if ($classLoaded == false) {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception('Invalid controller class ("' . $finalClass . '")');
        }

        return $finalClass;
    }

    /**
     * Set controller directory
     *
     * @param array|string $directory
     * @return Zend_Controller_Dispatcher_Standard
     */
    public function setControllerDirectory($directory, $module = null)
    {
        $this->_controllerDirectory = array();

        if (is_string($directory)) {
            $this->addControllerDirectory($directory, $module);
        } elseif (is_array($directory)) {
            foreach ((array) $directory as $module => $paths) {
                if (is_array($paths)) {
                    foreach ((array) $paths as $path) {
                        $this->addControllerDirectory($path, $module);
                    }
                } else {
                    $this->addControllerDirectory($paths, $module);
                }
            }
        } else {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('Controller directory spec must be either a string or an array');
        }

        return $this;
    }    

    /**
     * Get controller class name
     *
     * Try request first; if not found, try pulling from request parameter;
     * if still not found, fallback to default
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return string|false Returns class name on success
     */
    public function getControllerClass(Zend_Controller_Request_Abstract $request)
    {
        $controllerName = $request->getControllerName();
        if (empty($controllerName)) {
            if (!$this->getParam('useDefaultControllerAlways')) {
                return false;
            }
            $controllerName = $this->getDefaultControllerName();
            $request->setControllerName($controllerName);
        }

        $className = $this->formatControllerName($controllerName);

        $controllerDirs      = $this->getControllerDirectory();
        $module = $request->getModuleName();
        if ($this->isValidModule($module)) {
            $this->_curModule    = $module;
            $this->_curDirectory = $controllerDirs[$module];
        } elseif ($this->isValidModule($this->_defaultModule)) {
            $request->setModuleName($this->_defaultModule);
            $this->_curModule    = $this->_defaultModule;
            $this->_curDirectory = $controllerDirs[$this->_defaultModule];
        } else {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('No default module defined for this application');
        }

        return $className;
    }

    /**
     * Retrieve default controller class
     *
     * Determines whether the default controller to use lies within the
     * requested module, or if the global default should be used.
     *
     * By default, will only use the module default unless that controller does
     * not exist; if this is the case, it falls back to the default controller
     * in the default module.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return string
     */
    public function getDefaultControllerClass(Zend_Controller_Request_Abstract $request)
    {
        $controller = $this->getDefaultControllerName();
        $default    = $this->formatControllerName($controller);
        $request->setControllerName($controller)
                ->setActionName(null);

        $module              = $request->getModuleName();
        $controllerDirs      = $this->getControllerDirectory();
        $this->_curModule    = $this->_defaultModule;

        if (is_array($controllerDirs[$this->_defaultModule])) {
            $this->_curDirectory = $controllerDirs[$this->_defaultModule][0];
        } else {
            $this->_curDirectory = $controllerDirs[$this->_defaultModule];
        }        

        if ($this->isValidModule($module)) {
            $found = false;
            if (class_exists($default, false)) {
                $found = true;
            } else {
                $moduleDirs = $controllerDirs[$module];
                if (is_array($moduleDirs)){
                    foreach ($moduleDirs as $moduleDir) {
                        $fileSpec  = $moduleDir . DIRECTORY_SEPARATOR . $this->classToFilename($default);
                        if (Zend_Loader::isReadable($fileSpec)) {
                            $found = true;
                            $this->_curDirectory = $moduleDirs;
                        }
                    }
                } else {
                    $fileSpec  = $moduleDir . DIRECTORY_SEPARATOR . $this->classToFilename($default);
                    if (Zend_Loader::isReadable($fileSpec)) {
                        $found = true;
                        $this->_curDirectory = $moduleDirs;
                    }
                }
            }
            if ($found) {
                $request->setModuleName($module);
                $this->_curModule    = $this->formatModuleName($module);
            }
        } else {
            $request->setModuleName($this->_defaultModule);
        }

        return $default;
    }
}
