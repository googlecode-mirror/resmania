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
 * Provides support for modules/plugins (extensions) that need to upload media
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Media_Extensions_Manager extends RM_Media_Manager {

    /**
     * @var string
     */
    protected $_mediaFolder;
    /**
     * @var string
     */
    protected $_extensionsMediaURL;
    /**
     * @var RM_Unit_Row
     */
    protected $_extensionName;

    /**
     * Constructor
     */
    function __construct($extensionName) {
        $this->_extensionName = $extensionName;
        parent::__construct();
        $connector = RM_Environment::getConnector();
        $rootPath = $connector->getRootPath();
        $ds = DIRECTORY_SEPARATOR;
        $this->_mediaFolder = $rootPath . $ds . 'RM' . $ds . 'userdata' . $ds . 'images' . $ds . $extensionName;

        if (!file_exists($this->_mediaFolder)) {
            $this->createFolder();
        }

        $this->_extensionsMediaURL = RM_Environment::getConnector()->getUserRootURL() . "RM/userdata/images/" . $extensionName;
    }

    /**
     * Create  folder for media files
     *
     * @param int $unitID - unit primary key value
     * @return bool success or failure;
     */
    function createFolder() {
        // get config values
        $rmConfig = new RM_Config();
        $chmodOctal = intval($rmConfig->getValue('rm_config_chmod_value'), 8);

        $folder = $this->getFolder();
        return mkdir($folder, $chmodOctal, true);
    }

    /**
     * Return full path to unit folder
     *
     * @param int $unitID - unit primary key value
     * @return string
     */
    function getFolder() {
        return $this->_mediaFolder;
    }

    /**
     * Create new unique filename with the same extension
     *
     * @param string $filename
     * @return string
     */
    private function _createUniqueImageFilename($filename) {
        $chunks = explode('.', $filename);
        $extension = $chunks[count($chunks) - 1];
        unset($chunks[count($chunks) - 1]);
        return implode('.', $chunks) . '.' . time() . '.' . $extension;
    }

    /**
     * This method is for adding image from media library to an unit
     *
     * @param Zend_Db_Table_Row $unit
     * @param string $filename - media manager image name
     * @return mixed The primary key of the row inserted.
     */
    function addImage($filename) {
        $filepath = $this->getFilepath($filename);
        if (is_file($filepath) == false) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
            return $translate->_('Admin.System.MediaManager', 'ImageFileNotExists');
        }

        //If we don't have an unit image folder or already deleted it
        if (is_dir($this->getFolder()) == false) {
            $this->createFolder();
        }

        //Check image name in unit folder
        if (is_file($this->getFolder() . DIRECTORY_SEPARATOR . $filename)) {
            $filename = $this->_createUniqueImageFilename($filename);
        }

        $result = copy($filepath, $this->getFolder() . DIRECTORY_SEPARATOR . $filename);
        if ($result == false) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
            return $translate->_('Admin.System.MediaManager', 'CantCreateUnitImage');
        }

        $result = $this->createThumbnail($filename);
        if ($result == false) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
            return $translate->_('Admin.System.MediaManager', 'CantCreateUnitImage');
        }

        return true;
    }

    /**
     * Returns url to unit media file by it's filename
     *
     * @param Zend_Db_Table_Row $unit
     * @param string $filename - media manager image name
     * @param RM_Media_Image $thumbnail
     * @return string
     */
    function getFileURL($filename) {
        return $this->_extensionsMediaURL . "/" . $filename;
    }

    public function upload($formName = 'extension_media_upload', $sizeType) {
        $filename = $this->_upload($formName, $this->getFolder());
        $thumbnail = $this->createThumbnail($filename, $sizeType);
        return $thumbnail;
    }

    /**
     * Create all thumbnails for unit
     *     
     * @param string $filename
     * @return null
     */
    public function createThumbnail($filename, $sizeType = "small") {
        switch ($sizeType) {
            case "full":
                $thumbnail = RM_Media_Image::get(RM_Media_Image::MAIN);
                break;
            default:
                $thumbnail = RM_Media_Image::get(RM_Media_Image::THUMB);
                break;
        }


        $extensionMediaFolder = $this->getFolder();

        RM_Filesystem::deleteFiles(array(
                    $unitFolder . DIRECTORY_SEPARATOR . $thumbnail->createFilename($filename),
                ));

        $thumbnail = $this->_createThumbnail($thumbnail, $extensionMediaFolder, $filename);

        return $thumbnail;
    }

    /**
     * Delete unit image
     *     
     * @param string $filename
     * @return boolean
     */
    public function deleteImageFiles($filename) {
        $extensionMediaFolder = $this->getFolder();

        $files = array();
        $files[] = $unitFolder . DIRECTORY_SEPARATOR . $filename;
        $files[] = $unitFolder . DIRECTORY_SEPARATOR . RM_Media_Image::get(RM_Media_Image::THUMB)->createFilename($filename);
        return RM_Filesystem::deleteFiles($files);
    }

}