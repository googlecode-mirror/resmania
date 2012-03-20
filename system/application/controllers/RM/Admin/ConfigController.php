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
* Configuration controller class
*
* @access       public
* @author       Valentin
* @copyright    2011 ResMania Ltd.
* @version      1.2
* @link         http://docs.resmania.com/api/
* @since        06-2011
*/

class RM_Admin_ConfigController extends RM_Controller {

    function clearcacheJsonAction()
    {
        RM_Environment::getInstance()->clearCache();
        return array('data' => array('success' => true));
    }

    function testemailJsonAction(){
        //TODO: all this code below is a temp code - we need to delete is later
        //and create new one in EmailNotification module

        $email = $this->_getParam('email');
        if ($email === null) {
            return array('data' => array('success' => false));
        }
        
        $configModel = new RM_Config();
        
        $mail = new Zend_Mail('UTF-8');
        $mail->addTo($email);
        $mail->setFrom($configModel->getValue('rm_config_email_settings_mailfrom'), $configModel->getValue('rm_config_email_settings_fromname'));
        $mail->setBodyText($this->_translate->_('Admin.Config.Edit', 'TestEmailMessage'));
        $mail->setSubject($this->_translate->_('Admin.Config.Edit', 'TestEmailSubject'));

        $emailType = $configModel->getValue('rm_config_email_settings_mailer');

        try {
            if ($emailType == 'PHP') {
                $mail->send();
            } else {
                $smtpConfig = array(
                    'auth' => 'Login',
                    'username' => $configModel->getValue('rm_config_email_settings_smtpuser'),
                    'password' => $configModel->getValue('rm_config_email_settings_smtppass'),
                    'port' => $configModel->getValue('rm_config_email_settings_smtpport')                    
                );
                if ($configModel->getValue('rm_config_email_settings_smtpsecure') != "") {
                    $smtpConfig['ssl'] = strtolower($configModel->getValue('rm_config_email_settings_smtpsecure'));
                }
                $mail->send(new Zend_Mail_Transport_Smtp(
                    $configModel->getValue('rm_config_email_settings_smtphost'),
                    $smtpConfig
                ));
            }
        } catch (Zend_Mail_Exception $e) {
            return array('data' => array('success' => false, 'error' => $e->getMessage()));
        }

        return array('data' => array('success' => true));
    }

    function indexAction() {
        $this->sendJSON(array());
    }

    function newAction() {
        $this->sendJSON(array());
    }

    function fieldsAction() {

    }

    function editJsonAction() {

        $this->_withoutView();

        $dao = new RM_Config;
        $fields = $dao->fetchAll()->toArray();
        
        foreach ($fields as $key => $field) {
            switch ($field['xtype']) {
            //we store in 'form_config' the full list and in value only in csv format
                case 'itemselector' :
                    $selected = explode(',', $field['value']);

                    $fullList = Zend_Json::decode($field['form_config']);
                    $fullListArray = array();
                    foreach ($fullList as $row) {
                        $fullListArray[$row[0]] = $row[1];
                    }

                    $value = array();
                    $form_config = array();
                    foreach ($fullListArray as $id => $name) {
                        if (in_array($id, $selected)) {
                            $value[] = "['$id', $name, ".array_search($id, $selected)."]";
                        } else {
                            $form_config[] = "[$id, $name, $id]";
                        }
                    }

                    $fields[$key]['value'] = "[".implode(',', $value)."]";
                    $fields[$key]['form_config'] = "[".implode(',', $form_config)."]";
                    break;
            }
        }

        $json = new stdClass;
        $json->fields = $fields;

        return array(
        'data' => $json
        );
    }

    /**
     * Updates configuration.
     *
     * @param  	request all values this values for the config update
     * @return 	json    boolean response of true or false in json format (true is success)
     */
    function updateJsonAction() {
        $model = new RM_Config;
        $fields = $model->fetchAll();
        
        foreach ($fields as $field) {
            switch ($field->xtype){
                case "checkbox":
                    $value = $this->_getParam(
                        $field->id
                    );
                    break;
                default:
                    $value = $this->_getParam(
                        $field->id,
                        $this->_getParam(
                            $field->id."_hidden"
                        )
                    );
            }
            if ($value !== null) {
                $field->value = $value;
                $field->save();
            }
        }

        // process image resizing
        if ($this->_getParam('image_resize', 0) == 1){
            //1. resize images for media, if we will later add extra options for admin thumnails
            //$mediaManager = new RM_Media_Manager();
            //$mediaManager->resize();

            //2. resize images for units
            $unitModel = new RM_Units();
            $units = $unitModel->fetchAll();
            foreach ($units as $unit) {
                $unitMediaManager = new RM_Media_Unit_Manager($unit);
                $unitMediaManager->resize();
            }
        }

        return array(
            'data' => array('success' => true)
        );
    }

    function insertAction() {
        $this->sendJSON(array());
    }
  
}