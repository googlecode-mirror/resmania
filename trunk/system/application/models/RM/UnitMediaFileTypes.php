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
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_UnitMediaFileTypes extends RM_Model {    
    protected $_name = 'rm_unit_media_file_types';
    protected $_referenceMap    = array(
        'File' => array(
            'columns'           => 'file_id',
            'refTableClass'     => 'RM_UnitMediaFiles',
            'refColumns'        => 'id'
        ),
        'Types' => array(
            'columns'           => 'type_id',
            'refTableClass'     => 'RM_UnitMediaTypes',
            'refColumns'        => 'id'
        )
    );    

    /**
     * Returns media file type record by it's file and type assign
     *
     * @param Zend_Db_Table_Row $file
     * @param Zend_Db_Table_Row $type
     * @return Zend_Db_Table_Row OR null
     */
    function get($file, $type)
    {
        $select = $this->select()->where("file_id=".$file->id)->where("type_id=".$type->id);
        return $this->fetchAll($select)->current();
    }

    /**
     * Returns media file type record by it's file and type assign
     *
     * @param Zend_Db_Table_Row $file     
     * @return Zend_Db_Table_Rowset_Abstract OR null
     */
    function getByFile($file){
        return $this->fetchAll($this->select()->where("file_id=".$file->id));
    }

    /**
     * Return row by type and order
     *
     * @param Zend_Db_Table_Row $type
     * @param int $order
     * @return Zend_Db_Table_Row
     */
    function getByOrder($type, $order)
    {
        $select = $this->select()->where("order=".$order)->where("type_id=".$type->id);
        return $this->fetchAll($select)->current();
    }

    /**
     * Change all unit row orders
     *
     * @param array $info information in format:
     * [
     *    [
     *     <type_id> => <typeID>,
     *     <data> => [
     *       <order> => filename
     *       <order> => filename
     *      ...
     *    ],[...]]
     */
    function changeOrder($unit, $info)
    {
        $this->deleteByUnit($unit);
        
        foreach ($info as $information) {
            $typeID = $information['type_id'];
            $files = $information['data'];
            if (is_array($files) == false) {
                continue;
            }
            if (count($files) == 0) {
                continue;
            }
            switch ($typeID) {
                case RM_UnitMediaTypes::DELETED:
                    $this->_changeOrderDelete($files, $unit);
                    break;
                case RM_UnitMediaTypes::MAIN:
                    $this->_changeOrderMain($files, $unit);
                    break;
                case RM_UnitMediaTypes::THUMBS:
                    $this->_changeOrderThumbs($files, $unit);
                    break;
                default:
                    $fileModel = new RM_UnitMediaFiles();
                    foreach ($files as $order => $fileName) {
                        $file = $fileModel->getByUnit($unit, $fileName);
                        if ($file == null) continue;                        
                        $data = array();
                        $data['file_id'] = $file->id;
                        $data['type_id'] = $typeID;
                        $data['order'] = $order + 1;
                        $this->insert($data);
                    }
            }            
        }
    }

    /**
     * Remove old thumbs image and add new one
     *
     * @param array $files
     * @param RM_Unit_Row $unit
     */
    private function _changeOrderThumbs($files, $unit)
    {
        $fileName = $files[count($files) - 1];
        $fileModel = new RM_UnitMediaFiles();
        $file = $fileModel->getByUnit($unit, $fileName);
        if ($file == null) return;
        $data = array();
        $data['file_id'] = $file->id;
        $data['type_id'] = RM_UnitMediaTypes::THUMBS;
        $data['order'] = 1;
        $this->insert($data);
    }

    /**
     * Remove old main image and add new one
     *
     * @param array $files
     * @param RM_Unit_Row $unit
     */
    private function _changeOrderMain($files, $unit)
    {
        $fileName = $files[count($files) - 1];
        $fileModel = new RM_UnitMediaFiles();
        $file = $fileModel->getByUnit($unit, $fileName);
        if ($file == null) return;
        $data = array();
        $data['file_id'] = $file->id;
        $data['type_id'] = RM_UnitMediaTypes::MAIN;
        $data['order'] = 1;
        $this->insert($data);
    }

    /**
     * Delete all images that was in 'Delete' node
     *
     * @param array $files
     * @param RM_Unit_Row $unit
     */
    private function _changeOrderDelete($files, $unit)
    {
        //We don't need to delete file right now, maybe later
//        $fileModel = new RM_UnitMediaFiles();
//        foreach ($files as $fileName) {
//            $file = $fileModel->getByUnit($unit, $fileName);
//            $fileModel->deleteFile($unit, $file);
//        }
    }

    /**
     * Delete all file types by unit
     *
     * @param Zend_Db_Table_Row $unit
     * @return int The number of rows deleted.
     */
    function deleteByUnit($unit)
    {
        $unitMediaFilesModel = new RM_UnitMediaFiles();
        $files = $unitMediaFilesModel->get($unit);        
        foreach ($files as $file) {
            $this->delete('file_id='.$file->id);
        }        
    }
}