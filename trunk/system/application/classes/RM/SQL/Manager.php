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
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_SQL_Manager {
    /**
     * Execute every sql query in the file
     *
     * @param string $sqlFilepath path to file with SQL queries
     * @return bool
     */
    public static function executeSQLFile($sqlFilepath)
    {
        if (is_file($sqlFilepath) == false) {
            return true;
        }

        $sqlString = file_get_contents($sqlFilepath);
        if ($sqlString === false || $sqlString == "") {
            return true;
        }        
        $sqlString = trim($sqlString);

        $system = new RM_System();
        $result = $system->parseSQL($sqlString);        
        //$result = self::executeSQLCode($sqlString);

        return $result;
    }

    /**
     * Executes a SQL query
     *
     * @param string $sqlString SQL query
     * @return bool
     */
    public static function executeSQLCode($sqlString)
    {
        $result = true;
        $lines = explode(chr(10), $sqlString);
        foreach ($lines as $key => $line) {
            if (substr(ltrim($line), 0, 2) == '--' || ltrim($line) == '') {
                unset($lines[$key]);
            }
        }
	//$sqlString = implode(chr(10), $lines);
        if (strpos($sqlString,chr(13))>0){
            $sqlQueries = explode(";".chr(13), $sqlString);
        } else {
            $sqlQueries = explode(";".chr(10), $sqlString);
        }
        if (count($sqlQueries) > 0 ){
            $db = Zend_Db_Table::getDefaultAdapter();
            foreach ($sqlQueries as $sql){
                $sql = trim($sql);
                if ($sql !== "") {
                    $sqlResult = $db->query($sql);
                    $result = $result && $sqlResult;
                }
            }
        }
        return $result;
    }
}