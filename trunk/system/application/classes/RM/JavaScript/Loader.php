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
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_JavaScript_Loader
{
    /**
     * Return all language constants in javascript format for later use on the GUI.
     *
     * @return    string javascript code with all language constants
     */
    public static function getConstants()
    {
        $adapter = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN)->getAdapter();
        $messages = $adapter->getMessages(RM_Environment::getInstance()->getLocale());
        $originalMessages = $adapter->getMessages('en_GB');

        $js = "RM.Translate = {};\n";
        $nameSpaces = array();
        $nameSpaces[] = 'RM.Translate';

        foreach ($originalMessages as $section => $originalMessage) {
            $sectionChunks = explode('.', $section);
            $messageConstantName = $sectionChunks[count($sectionChunks) - 1];

            unset($sectionChunks[count($sectionChunks) - 1]);
            $nameSpace = 'RM.Translate';
            foreach ($sectionChunks as $chunk){
                $nameSpace.= ".$chunk";
                if (in_array($nameSpace, $nameSpaces) == false){
                    $nameSpaces[] = $nameSpace;
                    $js.="$nameSpace = {};\n";
                }
            }

            $realSectionName = implode('.',$sectionChunks);
            if (isset($messages[$section])) {
                $message = $messages[$section];
            } else {
                $message = $originalMessage;
            }
            if (strpos($realSectionName, '.JSON') !== false){
                $js.="RM.Translate.$realSectionName.$messageConstantName = ".$message.";\n";
            } else {
                $js.="RM.Translate.$realSectionName.$messageConstantName = '".addslashes($message)."';\n";
            }
        }

        return $js;
	}

    public static function getControllersJavaScript()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $directories = $frontController->getControllerDirectory();
        $moduleDirectories = $directories[RM_Environment::getConnector()->getModule()];
        $jsResult = "";

        foreach ($moduleDirectories as $directoryName) {
            $moduleDirectory = $directoryName.DIRECTORY_SEPARATOR.'RM'.DIRECTORY_SEPARATOR.ucfirst(RM_Environment::getConnector()->getModule());
            $files = RM_Filesystem::getFilesRecursively($moduleDirectory);
            foreach ($files as $file) {
                Zend_Loader::loadFile($file, null, true);
                $className = self::_getClassName($moduleDirectory, $file);
                $jsMethodNames = self::_getJsMethodList($className);
                if (count($jsMethodNames) == 0) {
                    continue;
                }
                $controller = new $className($frontController->getRequest(), $frontController->getResponse());
                foreach ($jsMethodNames as $jsMethodName) {
                    $jsResult .= $controller->$jsMethodName();
                }
            }
        }

        return $jsResult;
    }

    private static function _getClassName($directory, $file)
    {
        $classPath = str_replace($directory.DIRECTORY_SEPARATOR, '', $file);
        $classPath = str_replace('.php', '', $classPath);
        $classChunks = explode(DIRECTORY_SEPARATOR, $classPath);
        $className = 'RM_'.ucfirst(RM_Environment::getConnector()->getModule()).'_'.implode('_', $classChunks);
        return $className;
    }

    private static function _getJsMethodList($className)
    {
        $jsMethodNames = array();
        $methods = get_class_methods($className);
        if (empty($methods)) {
            RM_Log::toLog("Failed to load class: ".$className, INFO_GENERAL);
        }
        if (!empty($methods)){
            foreach ($methods as $methodName) {
                if (strpos($methodName, 'JsAction') !== false) {
                    $jsMethodNames[] = $methodName;
                }
            }
        }
        return $jsMethodNames;
    }
}
