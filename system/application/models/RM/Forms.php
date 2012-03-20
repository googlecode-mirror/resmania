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
class RM_Forms extends RM_Model
{
    protected $_name = 'rm_forms';
    protected $_rowClass = 'RM_Form_Row';

    public function getAllGlobal(){
        return $this->fetchAll($this->select()->where('global=1'));
    }

    public function getAllNonGlobal(){
        return $this->fetchAll($this->select()->where('global=0'));
    }

    /**
     * this will reset the form states back to the base config
     */
    public function resetForms(){

        $sqlPathArray = array(
            "modules",
            "FormDesigner",
            "sql",
            "resetpanels.sql"
        );
        $sqlPath = RM_Environment::getConnector()->getCorePath() . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $sqlPathArray);

        return RM_SQL_Manager::executeSQLFile($sqlPath);

    }

    /**
     * this will clear the form states
     */
    public function clearForms(){
        $sqlPathArray = array(
            "modules",
            "FormDesigner",
            "sql",
            "clearpanels.sql"
        );
        $sqlPath = RM_Environment::getConnector()->getCorePath() . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $sqlPathArray);

        return RM_SQL_Manager::executeSQLFile($sqlPath);
    }

    /**
     * get CSS file contents
     */
    public function getCSSFile(){
        $cssPathArray = array(
            "userdata",
            "css",
            "user_overrides.css"
        );
        return file_get_contents(RM_Environment::getConnector()->getCorePath() . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $cssPathArray));
    }

    public function saveCSSFile($data){

        $cssPathArray = array(
            "userdata",
            "css",
            "user_overrides.css"
        );

        $cssFilePath = $corePath = RM_Environment::getConnector()->getCorePath() . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $cssPathArray);

        $f = fopen($cssFilePath,'w');
        $result = fwrite($f,$data,strlen($data));
        fclose($f);

        if (!$result){
            return false;
        }
        return true;
    }
}