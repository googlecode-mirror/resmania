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
class RM_Media_Unit_Manager extends RM_Media_Manager
{
    
    private $_deletedTypeNodeID = 0;

    /**
     * @var string
     */
    protected $_unitsFolder;    

    /**
     * @var string
     */
    protected $_unitURL;

    /**     
     * @var RM_Unit_Row 
     */
    protected $_unit;

    /**
     * Constructor
     */
    function  __construct(RM_Unit_Row $unit) {
        $this->_unit = $unit;

        parent::__construct();

        $connector = RM_Environment::getConnector();
        $rootPath = $connector->getRootPath();
        
        $ds = DIRECTORY_SEPARATOR;
        $this->_unitsFolder = $rootPath.$ds.'RM'.$ds.'userdata'.$ds.'images'.$ds.'units';        
        $this->_unitURL = $this->_rootURL.'RM/userdata/images/units';
    }    

    /**
     * Create unit folder for media files
     *
     * @param int $unitID - unit primary key value
     * @return bool success or failure;
     */
    function createFolder(){
        // get config values
        $rmConfig = new RM_Config();
        $chmodOctal = intval($rmConfig->getValue('rm_config_chmod_value'),8);

        $folder = $this->getFolder();
        return mkdir($folder, $chmodOctal, true);
    }

    /**
     * Return full path to unit folder
     *
     * @param int $unitID - unit primary key value
     * @return string
     */
    function getFolder(){
        return $this->_unitsFolder.DIRECTORY_SEPARATOR.$this->_unit->id;
    }

    /**
     * Create new unique filename with the same extension
     *
     * @param string $filename
     * @return string
     */
    private function _createUniqueImageFilename($filename)
    {
        $chunks = explode('.', $filename);
        $extension = $chunks[count($chunks) - 1];
        unset($chunks[count($chunks) - 1]);
        return implode('.', $chunks).'.'.time().'.'.$extension;
    }

    /**
     * This method is for adding image from media library to an unit
     *
     * @param Zend_Db_Table_Row $unit
     * @param string $filename - media manager image name
     * @return mixed The primary key of the row inserted.
     */
    function addImage($filename)
    {
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
        if (is_file($this->getFolder().DIRECTORY_SEPARATOR.$filename)){
            $filename = $this->_createUniqueImageFilename($filename);
        }

        $result = copy($filepath, $this->getFolder().DIRECTORY_SEPARATOR.$filename);
        if ($result == false) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
            return $translate->_('Admin.System.MediaManager', 'CantCreateUnitImage');
        }

        $result = $this->createThumbnail($filename);
        if ($result == false) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
            return $translate->_('Admin.System.MediaManager', 'CantCreateUnitImage');
        }

        return $this->_assignImage($filename);
    }

    /**
     * Returns url to unit media file by it's filename
     *
     * @param Zend_Db_Table_Row $unit
     * @param string $filename - media manager image name
     * @param RM_Media_Image $thumbnail
     * @return string
     */
    function getFileURL($filename, $thumbnail = null)
    {
        if ($thumbnail !== null) {
            return $this->_unitURL.'/'.$this->_unit->id.'/'.$thumbnail->createFilename($filename);
        } else {
            return $this->_unitURL.'/'.$this->_unit->id.'/'.$filename;
        }
    }

    /**
     * Return full list of unit images with thumbnails
     *
     * @param Zend_Db_Table_Row $unit
     * @return array
     */
    function getList()
    {
        $model = new RM_UnitMediaFiles();
        $files = $model->get($this->_unit);

        $thumbnail = RM_Media_Image::get(RM_Media_Image::ADMIN);
        $main = RM_Media_Image::get(RM_Media_Image::MAIN);

        $images = array();
        foreach ($files as $file) {
            $filepath = $this->getFolder().DIRECTORY_SEPARATOR.$file->filename;

            $image = new RM_Media_Manager_Image();
            $image->name = $file->filename;
            $image->size = filesize($filepath);
            $image->id = $file->id;
            $image->lastmod = filemtime($filepath);
            $image->largeimageurl = $this->getFileURL($file->filename);
            $image->url = $this->getFileURL($file->filename, $thumbnail);
            $image->mainurl = $this->getFileURL($file->filename, $main);
            $images[] = $image;
        }

        return $images;
    }

    /**
     * Return a tree nodes for unit media type files
     *     
     * @return array
     */
    function getTree(){
        $typesModel = new RM_UnitMediaTypes();
        $types = $typesModel->fetchAll();

        $jsonTypes = array();
        $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
        foreach ($types as $type){
            $jsonType = new stdClass();
            $jsonType->id = $type->id;
            $jsonType->type_id = $type->id;
            $jsonType->text = $translate->_('Admin.Unit.Media.FileTypes', $type->name);
            $jsonType->cls = "folder";
            $jsonType->iconCls = "rm-tree-node";
            $jsonType->leaf = 0;
            $jsonType->allowDrag = false;
            $jsonType->expanded = true;            

            $files = $this->_getTypeFilesNodes($type);
            if (count($files) > 0) {
                $jsonType->children = $files;                
            } else {
                $jsonType->children = array();
            }

            $jsonTypes[] = $jsonType;
        }

        //Add additional node for deleting images
        $jsonType = new stdClass();
        $jsonType->id = 0;
        $jsonType->type_id = RM_UnitMediaTypes::DELETED;
        $jsonType->text = $translate->_('Admin.Unit.Media.FileTypes', 'Delete');
        $jsonType->cls = "folder";
        $jsonType->iconCls = "rm-tree-delete";
        $jsonType->leaf = 0;
        $jsonType->allowDrag = false;
        $jsonType->children = array();
        $jsonType->expanded = true;
        $jsonTypes[] = $jsonType;

        return $jsonTypes;
    }

    /**
     * Returns file nodes for unit media file tree
     *     
     * @param Zend_Db_Table_Row $type
     * @return array
     */
    protected function _getTypeFilesNodes($type){
        $model = new RM_UnitMediaFiles();
        $files = $model->get($this->_unit, $type);

        $jsonFiles = array();
        foreach ($files as $file){
            $jsonFile = new stdClass();
            $jsonFile->id = $file->id;
            $jsonFile->text = $file->filename;
            $jsonFile->leaf = 1;
            $jsonFile->allowDrop = false;

            $jsonFiles[] = $jsonFile;
        }
        return $jsonFiles;
    }    

    public function upload($formName = 'unit_media_upload')
    {
        $filename = $this->_upload($formName, $this->getFolder());
        $this->createThumbnail($filename);
        $this->_assignImage($filename);
        return $filename;
    }

    private function _assignImage($filename)
    {
        //we need to check is the same image record is already exists
        $model = new RM_UnitMediaFiles();
        $row = $model->getByUnit($this->_unit, $filename);
        if ($row !== null) {
            //Image was already overwriting and we just need to delete database row
            $row->delete();
        }        
        $data = array();
        $data['unit_id'] = $this->_unit->id;
        $data['filename'] = $filename;
        $data['caption'] = '';
        $data['details'] = '';
        $data['notes'] = '';
        return $model->insert($data);
    }                

    /**
     * Create all thumbnails for unit
     *     
     * @param string $filename
     * @return null
     */
    public function createThumbnail($filename)
    {
        //1. remove all thumbnail files of an unit
        $adminThumbnail = RM_Media_Image::get(RM_Media_Image::ADMIN);
        $thumbnail = RM_Media_Image::get(RM_Media_Image::THUMB);
        $mainThumbnail = RM_Media_Image::get(RM_Media_Image::MAIN);

        $unitFolder = $this->getFolder();
        //TODO: add a code to check the unlinking result
        RM_Filesystem::deleteFiles(array(
            $unitFolder.DIRECTORY_SEPARATOR.$adminThumbnail->createFilename($filename),
            $unitFolder.DIRECTORY_SEPARATOR.$thumbnail->createFilename($filename),
            $unitFolder.DIRECTORY_SEPARATOR.$mainThumbnail->createFilename($filename)
        ));

        //2. create a new thumbnail for an unit
        $this->_createThumbnail($adminThumbnail, $unitFolder, $filename);
        $this->_createThumbnail($thumbnail, $unitFolder, $filename);
        $this->_createThumbnail($mainThumbnail, $unitFolder, $filename);

        return true;
    }    

    /**
     * Delete unit image
     *     
     * @param string $filename
     * @return boolean
     */
    public function deleteImageFiles($filename)
    {
        $unitFolder = $this->getFolder();

        $files = array();
        $files[] = $unitFolder.DIRECTORY_SEPARATOR.$filename;
        $files[] = $unitFolder.DIRECTORY_SEPARATOR.RM_Media_Image::get(RM_Media_Image::ADMIN)->createFilename($filename);
        $files[] = $unitFolder.DIRECTORY_SEPARATOR.RM_Media_Image::get(RM_Media_Image::THUMB)->createFilename($filename);
        $files[] = $unitFolder.DIRECTORY_SEPARATOR.RM_Media_Image::get(RM_Media_Image::MAIN)->createFilename($filename);
        return RM_Filesystem::deleteFiles($files);
    }

    /**
     * Resize all thumbnails for a unit.
     */
    public function resize(){
        RM_Media_Image::initialize();
        $model = new RM_UnitMediaFiles();
        $files = $model->get($this->_unit);                
        foreach ($files as $file) {
            $this->createThumbnail($file->filename);
        }
    }
}