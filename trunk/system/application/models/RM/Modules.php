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
class RM_Modules extends RM_Model {
    protected $_name = 'rm_modules';
    protected $_rowClass = 'RM_Module_Row';
    
    function enable($module)
    {        
        $module->enabled = 1;
        $module->save();               
    }

    /**
     * Check if module enabled or disabled
     *
     * @param RM_Module_Row $module
     * @return bool
     */
    function isEnabled($module){
        return ($module->enabled == 1);
    }

    function disable($module)
    {     
        $module->enabled = 0;
        $module->save();       
    }

    function uninstall($module)
    {
        //TODO: create code for uninstalling module
    }

    /**
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getAllEnabled()
    {
        $rows = $this->fetchAllEnabled();
        return $this->_getObjects($rows);
    }    

    /**
     * Return all module objects
     *
     * @todo RM_Module_Manager has the very similar method: getModules. "In the end, there can be only one."
     * @return array
     */
    public function getAll(){
        $rows = $this->fetchAll();
        return $this->_getObjects($rows);
    }

    /**
     * @param string $name
     * @return RM_Module
     */
    public function get($name)
    {        
        $className = 'RM_Module_' . $name;
        return new $className();
    }

    /**
     * Return all enabled modules within a defined scope
     *
     * @todo RM_Module_Manager has the very similar method: getModules. "In the end, there can be only one." :-)
     * @todo I have added $scope today, this could be configuration/modules etc. This is just where this should be shown.
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchAllEnabled(){
        $sql = 'enabled=1';
        if (RM_Connector::getInstance()->isSafeMode()){
            $sql .= ' AND core=1';
        }        
        return $this->fetchAll($sql);
    }

    private function _getObjects($rows)
    {
        $objects = array();
        foreach ($rows as $row) {            
            $objects[] = $this->get($row->name);
        }
        return $objects;
    }

    /**
     * Returns plugin row by plugin name
     *
     * @param string $name plugin name
     * @return RM_Module_Row
     */
    function fetchByName($name){
        return $this->fetchRow($this->select()->where("name='$name'"));
    }

    public function filterAll($order, $count, $offset, $filters = array())
    {
        $select = $this->select();
        foreach ($filters as $filter){
            $filterContidions = $this->_getConditions($filter);
            foreach ($filterContidions as $condition){
                $select = $select->where($condition);
            }
        }

        $select->order($order);

        if ($count !== null){
            $select->limit($count, $offset);
        }

        $rows = $this->fetchAll($select);
        return $rows;
    }

    /**
     * this method will clear the module css cache
     */
    public function clearCSSCache(){
        // clear the module CSS Cache
        $cacheDir = RM_Environment::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'userdata' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR. 'css' ;
        if (file_exists($cacheDir)) {
            RM_Filesystem::emptyFolder($cacheDir);
        }
    }
}