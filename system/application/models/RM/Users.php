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
class RM_Users extends RM_Model_Flexible {
    protected $_name = 'rm_users';
    protected $_rowClass = 'RM_User_Row';
    protected $_primary = 'id';

    const FILTER_TYPE_OR = 'OR';
    const FILTER_TYPE_AND = 'AND';

    public function insert($data){
        $data['password'] = $this->_generatePassword($data['password']);
        if (isset($data['id'])) {
            unset($data['id']);
        }
        return parent::insert($data);
    }

    /**
     * We need to override parent Zend method to generate right password
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int          The number of rows updated.
     */
    public function update(array $data, $where){
        if (isset($data['password'])) {
            $data['password'] = $this->_generatePassword($data['password']);
        }        
        return parent::update($data, $where);
    }

    /**     
     * @param int $groupID
     * @return Zend_Db_Table_Rowset
     */
    protected function _getFieldsByGroup($groupID)
    {
        $configModel = new RM_UserConfig();
        $fields = $configModel->getFields($groupID);
        return $fields;
    }    

    /**
     * Create and return new user row from information from reguest.
     * This method is not saving row objcet into database.
     *
     * @param Zend_Request_Interface $request
     * @param int $groupID [optional] default RM_UserGroups::REGULAR
     * @param bool $resmania - if true force system to create resmania user instead of cms user
     */
    function createNewUser($request, $groupID = RM_UserGroups::REGULAR, $resmania = false){
        $fields = $this->_getFieldsByGroup($groupID);

        $notRequestFields = array(
            'group_id',
            'cms_id'
        );

        $data = array();
        $data['group_id'] = $groupID;
        foreach ($fields as $field) {
            if ($field->view_new == '1' && (in_array($field->column_name, $notRequestFields) === false)){
                $data[$field->column_name] = $request->getParam($field->column_name);
            }
        }

        $config = new RM_Config();
        if ($config->getValue('rm_config_enable_cms_user_creation') && $resmania == false) {
            $connectorObj =& RM_Environment::getConnector();
            $users = $connectorObj->getUsersModel();        
            $userInfo = $users->createRow($data);
        } else {
            $userInfo = $this->createRow($data);
        }

        return $userInfo;
    }

    /**
     * Generate encrypted passoword.
     *
     * @todo we need to add a 'salt' to md5 algorithm, to increase a password defence (just like in Joomla)
     * @param string $password alpha numeric string
     * @return string encrypted password
     */
    protected function _generatePassword($password){
        return md5($password);
    }    

    /**
     * Returns all user list
     *
     * @param int $groupID [optional][default=RM_UserGroups::REGULAR] user group id
     * @param string $order string in format "{sort order column name} {deriction}"
     * @param int $count number of rows that we need
     * @param int $offset number of rows that we need to pass by from start of the returning list (for pagination)
     * @param array $filters array with filters: each filter is in format @see Model::_getConditions method
     * @return Zend_Db_Table_Rowset
     */
    public function getAll($groupID = RM_UserGroups::REGULAR, $order = null, $count = null, $offset = null, $filters = array(), $filtersType = RM_Users::FILTER_TYPE_AND)
    {        
        $fields = $this->_getFieldsByGroup($groupID);

        $fieldsNames = array();
        foreach ($fields as $field) {
            $fieldsNames[] = 'rm_users.'.$field->column_name.' AS '.$field->column_name;
        }

        if ($offset === null){
            $offset = 0;
        }

        $sql = "
            SELECT
                ".implode(',', $fieldsNames)."
            FROM
                rm_users
            WHERE 
                rm_users.id != 0";
                
        if ($groupID) { $sql .= " AND rm_users.group_id = '$groupID' ";}

        if (count($filters) > 0) {            
            $filtersParsed = array();
            foreach ($filters as $filter) {                
                $filtersParsed[] = implode(" AND ", $this->_getConditions($filter));                
            }
            $sql .= " AND (".implode(" $filtersType ", $filtersParsed)." ) ";
        }

        if ($order !== null) {
            $sql.= " ORDER BY $order ";
        }

        if ($count !== null && $count !== '') {
            $sql.= " LIMIT $offset, $count ";
        }

        return $this->_getBySQL($sql);
    }

    /**
     * Returns user by login/password
     *
     * @param string $username username/login
     * @param string $password unencrypted password
     * @return RM_User_Row|NULL
     */
    public function getBy($username, $password = null){
        $sql = 'username="'.$username.'"';
        if ($password !== null) {
            $sql .= ' and password="'.$this->_generatePassword($password).'"';
        }
        return $this->fetchAll($sql)->current();
    }

    /**
     * Returns a list of users by field value
     *
     * @param string $fieldName
     * @param string $fieldValue
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getByField($fieldName, $fieldValue){
        return $this->fetchAll($this->select()->where("$fieldName=?", $fieldValue));
    }

    public function get($id)
    {
        $data = $this->getToGUI($id);
        return $data;
    }

    /**
     * Fetch by reservation
     *
     * @param RM_Reservation_Row $reservation
     * @return RM_User_Row
     */
    public function getByReservation(RM_Reservation_Row $reservation){
        return $this->fetchRow($this->select()->where("id=?", $reservation->user_id));
    }

    /*
    * Used for updating the User Titles from the Int value to the String
    * value in the Language file.
    *
    * @param    array   users   An array containing users information from RM_Users
    * @return     array     an array of users with the titles as the correct string
    */
    public function userTitles($users, $titleJson)
    {
        $titlesArray = Zend_Json::decode($titleJson);
        $newTitleArray = array();
        foreach ($titlesArray as $title){
            $newTitleArray[$title['id']] = $title['title'];
        }

        foreach ($users as $key => $user){
            $x = $user['title'];
            if ($x === "" || $x === null) {
                $x = 0;
            }
            $titleString = $newTitleArray[$x];
            $users[$key]['title'] = $titleString;
        }

        return $users;
    }

    /*
    * Used for updating the User Types from the Int value to the String
    * value in the Language file.
    *
    * @param    array   users   An array containing users information from RM_Users
    * @return     array     an array of users with the titles as the correct string
    */
    public function userTypes($users, $typeJson)
    {
        $typeArray = Zend_Json::decode($typeJson);
        $newTypeArray = array();
        foreach ($typeArray as $type){
            $newTypeArray[$type['id']] = $type['type'];
        }

        foreach ($users as $key => $user){
            $x = $user['group_id'];
            $typeString = $newTypeArray[$x];
            $users[$key]['group_id'] = $typeString;
        }

        return $users;
    }

    /*
    * Used to return a single user title value from the language file
    *
    * @param    int     the id of the title value i.e.: 0
    * @return   string  the value of the title i.e.: Mr
    */
    public function userTitle($titleID, $titleJson){
        $titlesArray = Zend_Json::decode($titleJson);
        foreach ($titlesArray as $title){
            if ((int)$title['id'] === $titleID){
                return $title['title'];
            }
        }
    }
}