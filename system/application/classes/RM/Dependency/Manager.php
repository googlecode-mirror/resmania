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
class RM_Dependency_Manager {
    /**
     * Return dependency object by it's database row representation
     *
     * @param RM_Dependency_Row $row
     * @return RM_Dependency_Abstract
     * @throw RM_Exception if dependency class name not exists
     */
    private function _getDependencyInstance($row){
        $type = $row->parent_type;
        $className = 'RM_Dependency_' . ucfirst(strtolower($type));
        if (!class_exists($className)) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
            throw new RM_Exception($translate->_('Admin.Dependencies', 'WrongDependencyClassName'));
        }
        return new $className($row);
    }

    /**
     * Check all dependencies of dependency child.
     *
     * @param $child RM_Dependency_Item_Interface
     * @return true|array - true if all dependencies resolved, otherwise array of dependencies that are not resolved
     */
    public function checkDependencies($child)
    {                
        $dependencies = $this->getDependencies($child);
        $notResolvedDependencies = array();        
        foreach ($dependencies as $dependency){
            if ($dependency->validate() == false) {
                $notResolvedDependencies[] = $dependency;                
            }
        }
        if (count($notResolvedDependencies) == 0) {
            return true;
        } else {
            return $notResolvedDependencies;
        }
    }

    /**
     * Returns all dependency child dependencies
     *
     * @param RM_Dependency_Child_Interface $child
     * @return array
     */
    function getDependencies($child){
        $model = new RM_Dependencies();
        $rows = $model->getDependencies($child);
        return $this->_getDependencies($rows);
    }   

    /**
     * Returns all dependecies objects in array
     *
     * @param Zend_Db_Table_Rowset $databaseRows
     * @return array
     */
    private function _getDependencies($databaseRows){
        $result = array();
        foreach ($databaseRows as $row){
            $result[] = $this->_getDependencyInstance($row);
        }
        return $result;
    }    
}