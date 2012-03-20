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
class RM_Plugins extends RM_Model {
    protected $_name = 'rm_plugins';
    protected $_rowClass = 'RM_Plugin_Row';    

    /**
     * Check if plugin enabled or disabled
     *
     * @param RM_Plugin_Row $plugin
     * @return bool
     */
    function isEnabled($plugin){
        return ($plugin->enabled == 1);
    }

    function disable($plugin)
    {
        $plugin->enabled = 0;
        $plugin->save();        
    }

    function enable($plugin)
    {
        $plugin->enabled = 1;
        $plugin->save();
    }

    public function getAllEnabled()
    {
        $rows = $this->fetchAllEnabled();
        return $this->_getObjects($rows);
    }

    /**
     * Return all plugin objects
     *
     * @todo RM_Plugin_Manager has the very similar method: getPlugins. "In the end, there can be only one."
     * @return array
     */
    public function getAll(){
        $rows = $this->fetchAll();
        return $this->_getObjects($rows);
    }

    /**
     * @param string $name
     * @return RM_Plugin
     */
    public function get($name)
    {
        $className = 'RM_Plugin_' . $name;
        return new $className();
    }

    /**
     * Return all enabled plugins within a defined scope
     *
     * @todo RM_Plugin_Manager has the very similar method: getPlugins. "In the end, there can be only one." :-)
     * @todo I have added $scope today, this could be configuration/plugins etc. This is just where this should be shown.
     * @return array
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
     * @return RM_Plugin_Row
     */
    function fetchByName($name){
        return $this->fetchRow($this->select()->where("name='$name'"));
    }

    /**
     * Returns all plugins that belong to a module with input name
     *
     * @param string $moduleName Module name
     * @return Zend_Db_Table_Rowset
     */
    function fetchByModuleName($moduleName, $onlyEnabled = false){
        $select = $this->select()->where("module_name='$moduleName'");
        if ($onlyEnabled) {
            $select->where("enabled='1'");
        }

        return $this->fetchAll($select);
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