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
 * Class that contains all information about thumbnails
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Media_Image {
    const ADMIN = 'admin';
    const THUMB = 'thumb';
    const MAIN = 'main';

    private $_postfix;
    private $_width;
    private $_height;
    private $_quality;
    /**
     * Keep aspect ratio for an image or not while resizing
     * @var bool
     */
    private $_keepAspect;
    private static $_list;

    /**
     * Calculate thumbnail width and height it keep ratio.
     *
     * @param int $width image width
     * @param int $height image height
     * @param int $thumbWidth maximum thumbnail width
     * @param int $thumbHeight maximum thumbnail height
     * @return array - array(thumbnail width, thumbnail height)
     */
    public function getImageSize($width, $height) {
        $thumbWidth = $this->_width;
        $thumbHeight = $this->_height;

        if (!$this->_keepAspect) {
            return array($thumbWidth, $thumbHeight);
        }

        $resizeWidth = $width > $thumbWidth;
        $resizeHeight = $height > $thumbHeight;
        if ($resizeWidth && $resizeHeight) {
            if ($width / $height >= $thumbWidth / $thumbHeight) {
                $ratio = $thumbWidth / $width;
                $thumbHeight = round($height * $ratio);
            } else {
                $ratio = $thumbHeight / $height;
                $thumbWidth = round($width * $ratio);
            }
        } elseif ($resizeWidth && !$resizeHeight) {
            $ratio = $thumbWidth / $width;
            $thumbHeight = round($height * $ratio);
        } elseif (!$resizeWidth && $resizeHeight) {
            $ratio = $thumbHeight / $height;
            $thumbWidth = round($width * $ratio);
        }

        return array($thumbWidth, $thumbHeight);
    }

    /**
     * Checks if this filename is original image file name
     *
     * @param string $filename
     */
    public static function isOriginal($filename) {
        $list = self::getAll();

        $isThumbnail = 0;
        foreach ($list as $thumbnail) {
            $isThumbnail |= $thumbnail->isThumbnail($filename);
        }

        return!$isThumbnail;
    }

    /**
     * Check if this filename is a file name of selected thumbnail
     *
     * @param string $filename
     * @return bool
     */
    public function isThumbnail($filename) {
        if (strpos($filename, "." . $this->_postfix . ".") === false) {
            return false;
        }
        return true;
    }

    /**
     * Return width of an image. X.
     *
     * @return int
     */
    public function getWidth() {
        return $this->_width;
    }

    /**
     * Return height of an image. Y.
     *
     * @return int
     */
    public function getHeight() {
        return $this->_height;
    }

    /**
     * Indicates is this images keeps aspect ration of the original image or not
     *
     * @return bool
     */
    public function isKeepingAspect() {
        return $this->_keepAspect;
    }

    /**
     * Return quality of an image in percentage.
     * This needed while creation new thumbnail from original image
     *
     * @return int
     */
    public function getQuality() {
        return $this->_quality;
    }

    private function __construct() {

    }

    /**
     * Initialize all objects at a first time, or if config has been changed.
     */
    public static function initialize() {
        self::$_list = array();

        $adminThumbnail = new RM_Media_Image();
        $adminThumbnail->_postfix = self::ADMIN;
        $adminThumbnail->_width = 100;
        $adminThumbnail->_height = 100;
        $adminThumbnail->_quality = 100;
        $adminThumbnail->_keepAspect = true;
        self::$_list[self::ADMIN] = $adminThumbnail;

        $config = new RM_Config();

        $thumbnail = new RM_Media_Image();
        $thumbnail->_postfix = self::THUMB;
        $thumbnail->_width = $config->getValue('rm_config_image_thumb_settings_x_res');
        $thumbnail->_height = $config->getValue('rm_config_image_thumb_settings_y_res');
        $thumbnail->_quality = $config->getValue('rm_config_image_thumb_settings_quality');
        $thumbnail->_keepAspect = (bool) $config->getValue('rm_config_image_thumb_settings_aspect');
        self::$_list[self::THUMB] = $thumbnail;

        $main = new RM_Media_Image();
        $main->_postfix = self::MAIN;
        $main->_width = $config->getValue('rm_config_image_settings_x_res');
        $main->_height = $config->getValue('rm_config_image_settings_y_res');
        $main->_quality = $config->getValue('rm_config_image_settings_quality');
        $main->_keepAspect = (bool) $config->getValue('rm_config_image_settings_aspect');
        self::$_list[self::MAIN] = $main;
    }

    /**
     * Create filename for thumbnail
     *
     * @param string $filename original image filename
     * @return string
     */
    public function createFilename($filename) {
        $chunks = explode('.', $filename);
        $extension = $chunks[count($chunks) - 1];
        $chunks[count($chunks) - 1] = $this->_postfix;
        $chunks[] = $extension;
        return implode('.', $chunks);
    }

    /**
     * Return object by it's key
     *
     * @param string $type - one of the RM_Media_Image::CONST
     * @return RM_Media_Image
     */
    public static function get($type) {
        if (self::$_list == null) {
            self::initialize();
        }

        if (isset(self::$_list[$type]) == false) {
            return null;
        }

        return self::$_list[$type];
    }

    /**
     * Return all thumbnails objects
     *
     * @return array of RM_Media_Image objects
     */
    public static function getAll() {
        if (self::$_list == null) {
            self::initialize();
        }
        return self::$_list;
    }

}