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
 * @version     1.2.1
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Extension_Manager {
    protected static function _upgradeDatabase($sqlFolderPath, $previousVersion, $latestVersion)
    {
        $files = RM_Filesystem::getFiles($sqlFolderPath, array('sql'));
        $upgradeVersions = array();
        foreach ($files as $file) {
            if (strpos($file, '.upgrade.sql') !== false){
                $upgradeVersions[] = str_replace('.upgrade.sql', '', $file);
            }
        }

        usort($upgradeVersions, 'version_compare');

        $result = true;
        foreach ($upgradeVersions as $version) {
            if (version_compare($version, $previousVersion, '>') &&
                version_compare($version, $latestVersion, '<=')){
                $result &= RM_SQL_Manager::executeSQLFile($sqlFolderPath.DIRECTORY_SEPARATOR.$version.'.upgrade.sql');
            }
        }
        return $result;
    }

    protected function _getUpgradeURL($extensionName){
        $config = new RM_Config();
        $licenseKey = $config->getValue('rm_config_licensekey');
        if ($licenseKey==""){
            return false;
        }
        $sourceFile = RM_Environment::getInstance()->getDistroURL()."d.php?d=".base64_encode( '{"d": "'.$_SERVER['SERVER_NAME'].'", "l": "'.$licenseKey.'", "v":"0", "p": "'.$extensionName.'", "qt": "validate"}');
        return $sourceFile;
    }

    protected function _moveUserDataFolders($source, $destination, $chmodOctal){
        $system = new RM_Filesystem();
        if (!is_dir($destination)) {
            mkdir($destination);
            chmod($destination, $chmodOctal);
        }
        $system->recursivecopy($source, $destination);
        rmdir($source);
        chmod($destination, $chmodOctal);
    }
}
 
