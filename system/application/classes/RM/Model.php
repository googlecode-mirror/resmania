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
 * Base Model class
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */

abstract class RM_Model extends Zend_Db_Table_Abstract {
    /**
     * Returns rowset by SQL
     *
     * @param $sql
     * @return Zend_Db_Table_Rowset
     */
    protected function _getBySQL($sql){
        $stmt = $this->_db->query($sql);
        $rows = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        $data  = array(
            'table'    => $this,
            'data'     => $rows,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        @Zend_Loader::loadClass($this->_rowsetClass);
        return new $this->_rowsetClass($data);
    }

    /**
     * Returns SQL condition (for 'where' methods) depending of the filter.
     *
     * @todo we need to create a separate filter class to incapsulate this method logic
     * @param string $filter Array from the GUI Json Filtered Grid with filter information
     * in the format:
     * filter['field'] => database table field name     
     * filter['data'] =>
     *   ['type'] => filter type, it could be:
     *     list - list of values: enum,
     *     boolean - 'true' or 'false',
     *     string - all the symbols that are in MySQL LIKE construct will be allowed, but to the end of the string system always add '%',
     *     numeric - number in INT format,
     *     date - date string in MySQL format: yyyy-mm-dd
     *   ['value'] => filter value (if we have a 'list' filter all values will be in this field in CSV format)
     *   ['comparison'] => type of comparisons, it could be: 'lt' (lower) , 'gt' (greater), 'eq' (equal)
     * @param string $tableName Name of the table that we need to search in.
     *
     * @return array with strings Condition in SQL format, without table prefix.
     */
    protected function _getConditions($filter){
        $cond = array();
        $comparisons = array(
            'lt' => '<',
            'gt' => '>',
            'eq' => '=',
            'notin' => 'NOT IN',
            'in' => 'IN'
        );

        switch ($filter['data']['type']) {
            case 'listin':
                $data = $filter['data']['value']; // comma seperated values
                $cond[] = $filter['field']." ".$comparisons[$filter['data']['comparison']]." (".$data.")";
                break;
            case 'list':
                $data = explode(',', $filter['data']['value']);
                foreach ($data as $value) {
                    $cond[] = $filter['field']." = '".$value."'";
                }
                break;
            case 'boolean':
                $data = $filter['data']['value'] == 'true' ? 1 : 0;
                $cond[] = $filter['field']." = '$data'";
                break;
            case 'string':
                $cond[] = $filter['field']." LIKE '".$filter['data']['value']."%'";
                break;
            case 'numeric':                
                $cond[] = $filter['field']." ".$comparisons[$filter['data']['comparison']]." '".$filter['data']['value']."'";
                break;
            case 'date':                                
                $cond[] = "UNIX_TIMESTAMP(".$filter['field'].")".$comparisons[$filter['data']['comparison']]."UNIX_TIMESTAMP('".$filter['data']['value']."')";
                break;
        }
        return $cond;        
    }

    /**
     * Filter all rows in the table using input parameters
     *
     * @param  $order
     * @param  $count
     * @param  $offset
     * @param array $filters
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function filterAll($order = null, $count = null, $offset = null, $filters = array())
    {
        $select = $this->select();
        foreach ($filters as $filter){
            $filterConditions = $this->_getConditions($filter);
            foreach ($filterConditions as $condition){
                $select = $select->where($condition);
            }
        }

        if ($order !== null) {
            $select->order($order);
        }

        if ($count !== null){
            $select->limit($count, $offset);
        }

        return $this->fetchAll($select);        
    }
}