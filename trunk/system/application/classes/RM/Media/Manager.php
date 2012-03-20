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
 * Class for support js media manager
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Media_Manager {

    /**
     * @var string
     */
    protected $_imageFolder;
    /**
     * @var string
     */
    protected $_imageURL;
    /**
     * @var string
     */
    protected $_rootURL;
    /**
     * List of all supported image file type extensions
     *
     * @var array
     */
    protected $_extensions = array('jpg', 'gif', 'png', 'jpeg', 'bmp');

    /**
     * Constructor
     */
    function __construct() {
        $connector = RM_Environment::getConnector();
        $rootPath = $connector->getRootPath();

        $ds = DIRECTORY_SEPARATOR;
        $this->_imageFolder = $rootPath . $ds . 'RM' . $ds . 'userdata' . $ds . 'images' . $ds . 'media';
        $this->_rootURL = $connector->getRootURL();
        $this->_imageURL = $this->_rootURL . 'RM/userdata/images/media';
    }

    /**
     * Return full path to file by filename
     *
     * @param string $filename filename     
     * @return string
     */
    function getFilepath($filename) {
        return $this->_imageFolder . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Check for a new images to exists
     *
     * @return int
     */
    function newImages() {
        return count($this->_getNewImages());
    }

    private function _getNewImages() {
        $files = RM_Filesystem::getFiles($this->_imageFolder, $this->_extensions);
        $newImages = array();
        foreach ($files as $filename) {
            if (RM_Media_Image::isOriginal($filename)) {
                //check for thumbnail
                $thumbnail = RM_Media_Image::get(RM_Media_Image::ADMIN);
                $thumbnailPath = $this->_imageFolder . DIRECTORY_SEPARATOR . $thumbnail->createFilename($filename);
                if (is_file($thumbnailPath) == false) {
                    $newImages[] = $filename;
                }
            }
        }
        return $newImages;
    }

    /**
     * Rescan image folder for a new images and create a new thumbnails for every new one
     *
     * @return void
     */
    function rescan() {
        $newImages = $this->_getNewImages();
        foreach ($newImages as $newImageFilename) {
            $this->createThumbnail($newImageFilename);
        }
    }

    /**
     * Return all thumbnails for admin end
     *
     * @return array - array of RM_Media_Manager_Image
     */
    function getList() {
        $files = RM_Filesystem::getFiles($this->_imageFolder, $this->_extensions);
        $adminThumbnail = RM_Media_Image::get(RM_Media_Image::ADMIN);

        $images = array();
        foreach ($files as $filename) {
            if (RM_Media_Image::isOriginal($filename) == false)
                continue;

            $filepath = $this->_imageFolder . DIRECTORY_SEPARATOR . $filename;

            $image = new RM_Media_Manager_Image();
            $image->name = $filename;
            $image->size = filesize($filepath);
            $image->lastmod = filemtime($filepath);
            $image->url = $this->_imageURL . '/' . $adminThumbnail->createFilename($filename);
            $image->largeimageurl = $this->_imageURL . '/' . $filename;
            $images[] = $image;
        }

        return $images;
    }

    protected function _upload($formName, $uploadFolderPath) {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        //TODO: we need a way of preventing overwriting images if file with the same name already exists
        try {
            $adapter->setDestination($uploadFolderPath);
        } catch (Zend_File_Transfer_Exception $exception) {
            throw new RM_Exception($exception->getMessage());
        }

        if (!$adapter->receive()) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
            $message = $translate->_('Admin.System.MediaManager', 'UploadFailure');
            $message.= '. ' . implode("; ", $adapter->getMessages());
            throw new RM_Exception($message);
        }

        $files = $adapter->getFileInfo();
        $filename = $files[$formName]['name'];

        return $filename;
    }

    /**
     * Upload image and create a thumbnail
     *
     * @throw RM_Exception
     * @param string $formName - optional, default: 'media_manager_upload'
     * @return bool
     */
    public function upload($formName = 'media_manager_upload') {
        $filename = $this->_upload($formName, $this->_imageFolder);
        $this->createThumbnail($filename);
        return $filename;
    }

    /**
     * Create a thumbnail for an image of Media manager
     *
     * @param string $filename
     */
    public function createThumbnail($filename) {
        $folderpath = $this->_imageFolder;
        $thumbnail = RM_Media_Image::get(RM_Media_Image::ADMIN);
        $this->_createThumbnail($thumbnail, $folderpath, $filename);
    }

    /**
     * Create a thumbnail for an image
     *
     * @param RM_Media_Image $thumbnail
     * @param string $folderpath 
     * @param string $filename 
     */
    protected function _createThumbnail(RM_Media_Image $thumbnail, $folderpath, $filename) {
        $filepath = $folderpath . DIRECTORY_SEPARATOR . $filename;
        $thumbfileName = $thumbnail->createFilename($filename);
        $thumbpath = $folderpath . DIRECTORY_SEPARATOR . $thumbfileName;

        list($width, $height, $type) = getimagesize($filepath);
        list($thumbWidth, $thumbHeight) = $thumbnail->getImageSize($width, $height);

        if (function_exists('imagecreatetruecolor')) {
            $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        } else {
            //If not GD PHP extension installed images will be in low quality
            $thumb = imagecreate($thumbWidth, $thumbHeight);
        }

        switch ($type) {
            case IMAGETYPE_JPEG :
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_GIF :
                $source = imagecreatefromgif($filepath);
                break;
            case IMAGETYPE_PNG :
                $source = imagecreatefrompng($filepath);
                break;
            case IMAGETYPE_BMP :
                $source = imagecreatefromwbmp($filepath);
                break;
        }

        imagecopyresized($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

        switch ($type) {
            case IMAGETYPE_JPEG :
                imagejpeg($thumb, $thumbpath, $thumbnail->getQuality());
                break;
            case IMAGETYPE_GIF :
                imagegif($thumb, $thumbpath);
                break;
            case IMAGETYPE_PNG :
                imagepng($thumb, $thumbpath, round($thumbnail->getQuality() / 100));
                break;
            case IMAGETYPE_BMP :
                image2wbmp($thumb, $thumbpath);
                break;
        }

        imagedestroy($thumb);

        return $thumbfileName;
    }

    /**
     * Delete image files from media manager by it's name
     *
     * @param string $filename filename
     * @return bool
     */
    public function deleteImageFiles($filename) {
        $files = array();
        $files[] = $this->_imageFolder . DIRECTORY_SEPARATOR . $filename;
        $files[] = $this->_imageFolder . DIRECTORY_SEPARATOR . RM_Media_Image::get(RM_Media_Image::ADMIN)->createFilename($filename);
        return RM_Filesystem::deleteFiles($files);
    }

    /**
     * Resize all thumbnails
     */
    public function resize() {
        RM_Media_Image::initialize();
        $files = RM_Filesystem::getFiles($this->_imageFolder, $this->_extensions);
        foreach ($files as $filename) {
            if (RM_Media_Image::isOriginal($filename)) {
                $this->createThumbnail($filename);
            }
        }
    }

}