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
 *
 * Class for handling system specific DAO methods
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_System extends RM_Model {

    protected $_name = 'rm_system';

    /**
     * this gets and returns the current DB version
     *
     * @return object   object containing data
     */
    public function getDBVersion() {
        $sql = 'SELECT  `db_version`
                FROM  `rm_system`
                LIMIT 1';
        return $this->_getBySQL($sql);
    }

    /**
     * this gets and returns the current runstatus
     *
     * @return object   object containing data
     */
    public function getRunStatus() {
        $sql = 'SELECT  `runstatus`
                FROM  `rm_system`
                LIMIT 1';
        return $this->_getBySQL($sql);
    }

    /**
     * this sets the runstatus
     *
     */
    public function setRunStatus($status) {
        $where = "id = 1";
        $data['runstatus'] = $status;
        return parent::update($data, $where);
    }

    /**
     * Splits a SQL file and Parses each Query line by line
     *
     * @deprecated
     * @todo we need to move this method into RM_SQL_Manager class to have one place code for
     * parsing and execution SQL queries
     * @return bool true for success, false for failed
     */
    public function parseSQL($sqlscript) {
        $queries = $this->splitSQL($sqlscript);
        if (empty($queries)) {
            return true;
        }
        $db = Zend_Db_Table::getDefaultAdapter();
        foreach ($queries as $query) {
            $query = trim($query);
            if ($query == "") {
                continue;
            }
            try {
                $db->getConnection()->query($query);
            } catch (Exception $e) {
                RM_Log::toLog("Upgrade SQL Failed! Query: " . $query . "    Exception: " . $e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @param string
     * @return array
     */
    public function splitSQL($sql) {
        $sql = trim($sql);

        //Remove comments
        $sql = preg_replace("/\n\#[^\n]*/", '', $sql);
        $sql = preg_replace("/^--[^\n]*/", '', $sql); //First comment
        $sql = preg_replace("/\n--[^\n]*/", '', $sql);

        //Remove 'tabs'
        $sql = preg_replace("/\t/", '', $sql);

        $buffer = array();
        $ret = array();
        $in_string = false;

        for ($i = 0; $i < strlen($sql) - 1; $i++) {
            if ($sql[$i] == ";" && !$in_string) {
                $ret[] = trim(substr($sql, 0, $i));
                $sql = substr($sql, $i + 1);
                $i = 0;
            }

            if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\" && ($sql[$i] != "'" || $sql[$i - 1] != "'" || $sql[$i - 2] != "\\")) {
                $in_string = false;
            } elseif (
                    !$in_string
                    && ($sql[$i] == '"' || $sql[$i] == "'")
                    && (!isset($buffer[0]) || $buffer[0] != "\\")
            ) {
                $in_string = $sql[$i];
            }

            if (isset($buffer[1])) {
                $buffer[0] = $buffer[1];
            }
            $buffer[1] = $sql[$i];
        }

        if (!empty($sql)) {
            $ret[] = trim($sql);
        }
        return ($ret);
    }

}
