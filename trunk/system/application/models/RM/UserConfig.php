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
class RM_UserConfig extends RM_Model {
	protected $_name = 'rm_users_config';
	//protected $_rowClass = 'RM_Config_Field';

    public function getAdminList()
    {
        $sql = "
			SELECT
				*
			FROM
		        rm_users_config
			";

        return $this->_getBySQL($sql);
    }

    protected function _getAll($groupId)
    {
         $sql = "
			SELECT
				config.*
			FROM
				rm_user_group_config group_config
			INNER JOIN rm_users_config config ON
                config.id = group_config.user_config_id
			WHERE
				group_config.user_group_id = '$groupId'
			";

        return $this->_getBySQL($sql);
    }

    public function getFields($groupId){
       return $this->_getAll($groupId);
    }
    
    protected function _getEditFormByUser($users_groupID)
    {
        $sql = "
			SELECT
				config.*
			FROM
				rm_user_group_config group_config
			INNER JOIN rm_users_config config ON
                config.id = group_config.user_config_id
			WHERE
				group_config.user_group_id = '$users_groupID' AND config.view_edit = 1
			";

        return $this->_getBySQL($sql);
    }

    public function getEditFormByUser($users_groupID){
        return $this->_getEditFormByUser($users_groupID);
    }

}