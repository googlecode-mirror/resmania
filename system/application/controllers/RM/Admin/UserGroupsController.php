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
 * Admin User Controller.
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_UserGroupsController extends RM_Controller {

    // the name of this function is the used to call this. excliuding "action"

    /**
     * returns usergroups.
     *
     * this method is used by the notification module and new/edit users.
     *
     * @param boolean   $all    if false only user group will be returned.
     * @return json user groups
     */
    public function getallJsonAction(){
        $dao = new RM_UserGroups;
        
        $all = $this->_getParam('returnall', false); // param to fetch all groups

        if ($all){
            $groups = $dao->fetchAll();
        } else {
            $groups = $dao->fetchAll("id='0'");
        }

        foreach ($groups as $group){
            $jsonFields[] = array("id"=>$group->id, "name"=>$group->name);
        }
        
        $json = array("data"=>Zend_Json::encode($jsonFields),"encoded"=>true);
        return $json;
    }

}

