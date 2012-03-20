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
 * Admin Users Controller
 *
 * This class provides the GUI action handler any action generated from the GUI is contolled
 * by this class. This includes menu items, buttons etc.
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_UsersController extends RM_Controller {
    /*
     * This gets the list setup, which includes column fields.
     */

    public function listJsAction() {
        $fieldsDAO = new RM_UserConfig();
        $configFields = $fieldsDAO->getAdminList()->toArray();

        foreach ($configFields as $key => $configField) {
            if (intval($configField['admin_list']) == 1) {
                if ($configField['admin_list_preferences'] !== "") {
                    $metainfo[] = $configField['admin_list_preferences'];
                }
            }
        }

        return "RM.Common.Users_List_Setup([" . implode(',', $metainfo) . "]);";
    }

    /*
     * This provides the listAction to return data via JSON to the Javascript code
     * for display in the GUI
     *
     * @return     JSON    returns JSON formatted data which is read by the GUI
     * @todo       add remaining actions (deleteAction, newAction, insertAction etc)
     *             add the error exception handling.
     */

    public function listJsonAction() {
        $offset = $this->_getParam('start', null);
        $count = $this->_getParam('limit', null);
        $sort = $this->_getParam('sort', 'id');
        $direction = $this->_getParam('dir', 'ASC');
        $query = $this->_getParam('query', null);
        $filters = $this->_getParam('filter', array());

        if ($query !== null) {
            $chunks = explode(',', $query);
            foreach ($chunks as $chunk) {
                $chunk = trim($chunk);
                if ($chunk == "")
                    continue;

                $filter = array(
                    'type' => 'string',
                    'value' => trim($chunk)
                );
                $filters[] = array(
                    'field' => 'rm_users.first_name',
                    'data' => $filter
                );
                $filters[] = array(
                    'field' => 'rm_users.last_name',
                    'data' => $filter
                );
            }
        }

        $group = "";

        $configModel = new RM_Config();
        $userGroupsEnabled = $configModel->getValue('rm_config_enable_user_groups');

        if ($userGroupsEnabled) {
            $group = RM_UserGroups::REGULAR;
        }

        $order = $sort . ' ' . $direction;
        $dao = new RM_Users;

        $total = $dao->getAll(
                        $group,
                        $order,
                        null,
                        null,
                        $filters,
                        RM_Users::FILTER_TYPE_OR
                )->count();

        $users = $dao->getAll(
                        $group,
                        $order,
                        $count,
                        $offset,
                        $filters,
                        RM_Users::FILTER_TYPE_OR
                )->toArray();

        // title replacement from Int to string
        $users = $dao->userTitles($users, str_replace(chr(39), chr(34), $this->_translate->_('Common.JSON', 'Titles')));
        // type replacement from int to string
        $users = $dao->userTypes($users, str_replace(chr(39), chr(34), $this->_translate->_('Common.JSON', 'Types')));

        $json = new stdClass;
        $json->total = $total;
        $json->data = $users;

        return array("data" => $json);
    }

    public function getuserJsonAction() {
        $id = $this->_getParam('id');
        $dao = new RM_Users;

        $users = $dao->get($id)->toArray();
        $json = new stdClass;
        $json->total = 1;
        $json->data = $users;
        return array("data" => $json);
    }

    public function deleteJsonAction() {
        $ids = $this->_getParam('ids', array());

        $connectorObj = & RM_Environment::getConnector();
        $userModel = $connectorObj->getUsersModel();
        $errors = $userModel->delete($ids);

        if (count($errors) == 0)
            return array('data' => array('success' => true));

        return array('data' => array(
                'success' => false,
                'error' => implode('. ', $errors)
        ));
    }

    public function newJsonAction() {
        //TODO: need to add code to control the usergroup selection
        $userGroup = 0;

        $userGroupDAO = new RM_UserConfig();
        $fields = $userGroupDAO->getFields($userGroup);

        foreach ($fields as $field) {
            $jsonFields[] = $field->view_preferences;
        }

        /* data returned to the dispatcher is returned as an array.
         * the element data represents the data
         * the element encoded represents if the data is already json encoded or not (true=encoded)
         */
        return array("data" => "{fields : [" . implode(',', $jsonFields) . "]}", "encoded" => true);
    }

    public function insertJsonAction() {
        $wizard = $this->_getParam('wizard', false);
        if ($wizard) {
            $userDetails = $this->_getParam('new_wizard_users');
        } else {
            $userDetails = $this->_getParam('new_users');
        }

        $connectorObj = & RM_Environment::getConnector();
        $users = $connectorObj->getUsersModel();

        try {
            $id = $users->insert($userDetails);
        } catch (RM_Exception $exception) {
            $id = $id;
            // pass message back to JS
            // extra parameter on the return array below
            return array(
                'data' => array(
                    'success' => false,
                    'msg' => RM_Environment::getInstance()
                            ->getTranslation(RM_Environment::TRANSLATE_ERRORS)
                            ->_('RM.User.Creation', $exception->getMessage())
                )
            );
        }

        return array(
            'data' => array('success' => true, 'id' => $id)
        );
    }

    public function editJsonAction() {
        $json = new stdClass;

        $id = $this->_getParam('id');
        $dao = new RM_Users();
        $user = $dao->getToGUI($id);

        $config = new RM_UserConfig();
        $fields = $config->getEditFormByUser($user['group_id']);

        foreach ($fields as $field) {
            $jsonFields[] = $field->view_preferences;
        }

        // just get the selected UserType
        $groups = new RM_UserGroups();
        $groupinfo = $groups->getAll();

        $json = array("data" => "{ users : " . Zend_Json::encode($user->toArray()) . ", fields : [" . implode(',', $jsonFields) . "], groupinfo : " . Zend_Json::encode($groupinfo->toArray()) . "}", "encoded" => true);
        return $json;
    }

    public function listresJsonAction() {

        $id = $this->_getParam('id');

        $config = new RM_Config(); // used for date conversion

        $model = new RM_Reservations;
        $Users_Reservation_Total = $model->fetchAllByUserID($id)->count();
        $Users_Reservation_Info = $model->fetchAllByUserID($id)->toArray();
        $jsonReservations = Array();

        $reservationDetailsModel = new RM_ReservationDetails();

        foreach ($Users_Reservation_Info as $reservation) {
            $tempVal->reservation_id = $reservation['reservation_id'];
            $tempVal->unit_id = $reservation['unit_id']; //TODO: We need to convert this to a meaningful word ie: unitname (ID:X)
            $tempVal->start_date = $config->convertDates($reservation['start_datetime'], RM_Config::MYSQL_DATEFORMAT, RM_Config::JS_DATEFORMAT);
            $tempVal->end_date = $config->convertDates($reservation['end_datetime'], RM_Config::MYSQL_DATEFORMAT, RM_Config::JS_DATEFORMAT);
            $jsonReservations[] = clone $tempVal;
        }
        $ret = array("data" => '{"total": ' . $Users_Reservation_Total . ', "data" : ' . Zend_Json::encode($jsonReservations) . '}', 'encoded' => true);
        return $ret;
    }

    public function updateJsonAction() {
        $userDetails = $this->_getParam('edit_users');

        if ($userDetails['new_password'] != '') {
            $userDetails['password'] = $userDetails['new_password'];
        }
        unset($userDetails['new_password']);

        $connectorObj = RM_Environment::getConnector();
        $users = $connectorObj->getUsersModel();

        try {
            $id = $users->update($userDetails);
        } catch (RM_Exception $exception) {
            return array(
                'data' => array(
                    'success' => false,
                    'msg' => RM_Environment::getInstance()
                            ->getTranslation(RM_Environment::TRANSLATE_ERRORS)
                            ->_('RM.User.Creation', $exception->getMessage())
                )
            );
        }

        return array(
            'data' => array('success' => true)
        );
    }

    /**
     * Sync CMS users with Resmania users.
     * Basically it copy cms users that are not presented in Resmania into Resmania user database table,
     * without passwords.
     */
    public function syncJsonAction() {
        try {
            $users = RM_Environment::getConnector()->getUsers();
        } catch (Exception $e) {
            return array('data' => array(
                    'success' => false,
                    'message' => $e->getMessage()
            ));
        }

        $count = 0;
        $table = new RM_Users();
        foreach ($users as $user) {
            $resmaniaUser = $user->findResmaniaUser();
            if ($resmaniaUser == null) {
                $newResmaniaUser = $user->convertToResmaniaUser();
                $newResmaniaUser->group_id = RM_UserGroups::REGULAR;
                $newResmaniaUser->setTable($table); //This is for internal class connecting.
                $newResmaniaUser->save();
                $count++;
            }
        }

        if ($count == 0) {
            $message = $this->_translate->_('Admin.Users.List', 'SyncSuccessNoNew');
        } else {
            $message = $this->_translate->_('Admin.Users.List', 'SyncSuccess') . $count;
        }

        return array('data' => array(
                'success' => true,
                'message' => $message
        ));
    }

}