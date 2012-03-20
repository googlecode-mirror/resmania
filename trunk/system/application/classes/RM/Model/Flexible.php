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
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
abstract class RM_Model_Flexible extends RM_Model
{
    public function convertFromGUI($data)
    {
        $info = $this->info();
        foreach ($info['metadata'] as $column) {
            if (isset($data[$column['COLUMN_NAME']]) == false) continue;

            switch ($column['DATA_TYPE']) {
                case 'tinyint':
                    if (isset($data[$column['COLUMN_NAME']]) == false){
                        $data[$column['COLUMN_NAME']] = $column['DEFAULT'];
                    }
                    break;
                case 'datetime':
                case 'date':                    
                    $config = new RM_Config();
                    $data[$column['COLUMN_NAME']] = $config->convertDates(
                        $data[$column['COLUMN_NAME']],
                        RM_Config::JS_DATEFORMAT,
                        RM_Config::MYSQL_DATEFORMAT
                    );
                    break;
            }
        }
        return $data;
    }

    public function convertToGUI($data)
    {
        $info = $this->info();
        foreach ($info['metadata'] as $column) {
            switch ($column['DATA_TYPE']) {
                case 'datetime':
                case 'date':                    
                    $config = new RM_Config();
                    $data[$column['COLUMN_NAME']] = $config->convertDates(
                        $data[$column['COLUMN_NAME']],
                        RM_Config::MYSQL_DATEFORMAT,
                        RM_Config::JS_DATEFORMAT
                    );
                    break;
            }
        }
        return $data;
    }

    public function insertFromGUI($data)
    {
        $data = $this->convertFromGUI($data);
        return parent::insert($data);
    }

    public function updateFromGUI($data)
    {        
        $data = $this->convertFromGUI($data);        
        return parent::update($data, $this->_primary[1].'='.$data[$this->_primary[1]]);
    }

    /**
     * @param mixed $id - primary key value
     * @return array
     */
    public function getToGUI($id)
    {
        $data = parent::find($id)->current();        
        $data = $this->convertToGUI($data);
        return $data;
    }
}