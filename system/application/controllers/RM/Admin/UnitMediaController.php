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
 * Unit Media Controller.
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_UnitMediaController extends RM_Controller {

    /**
     * Return all media type files for a unit
     */
    function unittypetreeJsonAction(){
        $unitID = $this->_getParam('unit_id', null);
        if ($unitID == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'UnitIDNotSpecified')));
        }
        $unitModel = new RM_Units();
        $unit = $unitModel->find($unitID)->current();
        if ($unit == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'WrongUnitID')));
        }

        $manager = new RM_Media_Unit_Manager($unit);
        $data = $manager->getTree();
        return array('data' => $data);
    }

    /**
     * Returns full list of unit media files
     */
    function unitlistJsonAction(){
        $unitID = $this->_getParam('unit_id', null);
        if ($unitID == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'UnitIDNotSpecified')));
        }
        $unitModel = new RM_Units();
        $unit = $unitModel->find($unitID)->current();
        if ($unit == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'WrongUnitID')));
        }

        $manager = new RM_Media_Unit_Manager($unit);

        $json = new stdclass;
        $json->images = $manager->getList();

        return array('data' => $json);
    }

    /**
     * Returns full list of media manager images
     */
    function mediamanagerlistJsonAction(){
        $manager = new RM_Media_Manager;

        $json = new stdclass;
        $json->images = $manager->getList();

        return array('data' => $json);
    }

    /**
     * Upload image to media manager and create unit media file record
     */
    function uploadJsonAction(){
        $unitID = $this->_getParam('unit_id', null);
        if ($unitID == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'UnitIDNotSpecified')));
        }
        $unitModel = new RM_Units();
        $unit = $unitModel->find($unitID)->current();
        if ($unit == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'WrongUnitID')));
        }

        $manager = new RM_Media_Unit_Manager($unit);
        try {
            $filename = $manager->upload();            
        } catch (RM_Exception $exception) {
            //We need to check internal Zend exceptions:
            //'The given destination is no directory or does not exist', 'The given destination is not writeable'
            $folder = $manager->getFolder();

            $errorMessage = $exception->getMessage();
            switch ($errorMessage) {
                case 'The given destination is no directory or does not exist':
                    $errorMessage = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Unit.Media', 'FolderDoesntExists').$folder;
                    break;
                case 'The given destination is not writeable':
                    $errorMessage = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Unit.Media', 'FolderIsNotWritable').$folder;
                    break;
            }

            return array('data' => array('success' => false, 'error' => $errorMessage));
        }        

        return array('data' => array('success' => true));
    }

    /**
     * Delete unit images
     */
    function deleteJsonAction(){        
        $filename = $this->_getParam('filename');
        if ($filename === null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'FilenameMustBeNonEmpty')));
        }
        $filename = trim($filename);

        $unitID = $this->_getParam('unit_id');
        if ($unitID === null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'UnitIDMustBeNonEmpty')));
        }

        $unitModel = new RM_Units();
        $unit = $unitModel->find($unitID)->current();
        if ($unit === null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'WrongUnitID')));
        }

        $model = new RM_UnitMediaFiles();
        $file = $model->getByUnit($unit, $filename);
        if ($file !== null) {
            $model->deleteFile($unit, $file);
        } else {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'WrongFilename')));
        }
        return array('data' => array('success' => true));
    }

    /**
     * Change media file preferences
     */
    function editJsonAction(){
        $fileID = $this->_getParam('file_id', null);
        if ($fileID == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'FileIDNotSpecified')));
        } 
        $fileModel = new RM_UnitMediaFiles();
        $file = $fileModel->find($fileID)->current();
        if ($file == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'WrongFileID')));
        }

        $details = $this->_getParam('details', '');
        $caption = $this->_getParam('caption', '');
        $notes = $this->_getParam('notes', '');

        $file->details = $details;
        $file->caption = $caption;
        $file->notes = $notes;

        try {
            $file->save();
        } catch (Exception $e) {
            return array('data' => array(
                'success' => false,
                'error' => $this->_translate->_('Admin.Unit.Media', 'UnableToUpdateTable').': '.$e->getMessage()
            ));
        }

        return array('data' => array('success' => true));
    }

    /**
     * This method is for adding image from media library to an unit
     */
    function addJsonAction(){               
        $unitID = $this->_getParam('unit_id', null);
        if ($unitID == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'UnitIDNotSpecified')));
        }
        $unitModel = new RM_Units();
        $unit = $unitModel->find($unitID)->current();
        if ($unit == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'WrongUnitID')));
        }
        
        $filename = urldecode($this->_getParam('filename', null));
        if ($filename == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'ImageFilenameNotSpecified')));
        }
                
        $mediaManager = new RM_Media_Unit_Manager($unit);
        try {
            $mediaManager->addImage($filename);
        } catch (RM_Exception $exception) {
            return array('data' => array('success' => false, 'error' => $exception->getMessage()));
        }
    }       

    /**
     * This method is for classification media file
     */
    function classificationmediaJsonAction(){
        $unitID = $this->_getParam('unit_id', null);
        if ($unitID == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'UnitIDNotSpecified')));
        }
        $unitModel = new RM_Units();
        $unit = $unitModel->find($unitID)->current();
        if ($unit == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'WrongUnitID')));
        }
        
        $info = $this->_getParam('info', null);
        if ($info == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('Admin.Unit.Media', 'TypeInformationNotSpecified')));
        }
        
        $fileTypeModel = new RM_UnitMediaFileTypes();

        $infoObject = Zend_Json::decode($info);        
        $result = $fileTypeModel->changeOrder($unit, $infoObject);

        if ($result) {
            return array('data' => array('success' => true));
        } else {
            return array('data' => array('success' => false));
        }
    }
}