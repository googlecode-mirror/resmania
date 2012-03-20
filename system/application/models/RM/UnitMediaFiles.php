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
class RM_UnitMediaFiles extends RM_Model {
    protected $_name = 'rm_unit_media_files';    
    protected $_referenceMap    = array(
        'Unit' => array(
            'columns'           => 'unit_id',
            'refTableClass'     => 'RM_Units',
            'refColumns'        => 'id'
        )
    );

    /**
     * Delete all information about file
     *
     * @param Zend_Db_Table_Row $unit
     * @param Zend_Db_Table_Row $file
     */
    function deleteFile($unit, $file){
        $mediaManager = new RM_Media_Unit_Manager($unit);
        $mediaManager->deleteImageFiles($file->filename);
        
        $model = new RM_UnitMediaFileTypes();
        $unitTypeFiles = $model->getByFile($file);
        foreach ($unitTypeFiles as $typeFile) {
            $typeFile->delete();
        }
        return $file->delete();
    }

    /**
     * Return image by unit and filename
     *
     * @param RM_Uit $unit
     * @param string $filename
     */
    function getByUnit($unit, $filename){
        $select = $this->select();
        $select->where("unit_id='".$unit->id."'")->where("filename='$filename'");
        return $this->fetchAll($select)->current();
    }   

    /**
     * Returns all thumbnails for unit
     *
     * @param RM_Unit $unit
     * @return Zend_Db_Table_Rowset
     */
    function getThumbnals($unit){
        $typeModel = new RM_UnitMediaTypes();
        $type = $typeModel->find(RM_UnitMediaTypes::THUMBS)->current();

        $files = $this->get($unit, $type);
        return $files;
    }

    /**
     * Returns main media file by unit
     *
     * @param RM_Unit $unit
     * @return Zend_Db_Table_Row
     */
    function getMainFile($unit){
        $typeModel = new RM_UnitMediaTypes();
        $type = $typeModel->find(RM_UnitMediaTypes::MAIN)->current();

        $file = $this->get($unit, $type)->current();
        if ($file == null){
            //we don't have a main file - so we just pick up the first file in the list
            $file = $this->get($unit)->current();
            //In any way, unit could be without images at all, so we need to check return value for 'null'
        }
        return $file;
    }

    /**
     * Returns all files assigned to unit and belong to type in order
     *
     * @param Zend_Db_Table_Row $unit
     * @param Zend_Db_Table_Row|int $type Type object or type id
     * @return Zend_Db_Table_Rowset
     */
    function get(RM_Unit_Row $unit, $type = null){
        if (is_object($type) && is_a($type, 'Zend_Db_Table_Row')){
            $typeID = $type->id;
        } else if (is_int($type)>0) {
            $typeID = $type;
        } else {
            $typeID = null;
        }

        $sql = "
            SELECT
                rumf.*
            FROM
                rm_unit_media_files rumf";

        if ($typeID !== null) {
            $sql.= " 
            INNER JOIN
                rm_unit_media_file_types rumft ON rumf.id=rumft.file_id AND rumft.type_id=".$typeID;
        }
        $sql.= "
            WHERE
                rumf.unit_id=".$unit->id;
        
        if ($typeID !== null) {
            $sql.= "
                ORDER BY
                    rumft.order
            ";
        }

        return $this->_getBySQL($sql);
    }
}