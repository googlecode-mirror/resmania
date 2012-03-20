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
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Form_Validator_Login extends RM_Form_Validator {
    /**
     * @param RM_Form_Row $form
     */
    function __construct($form) {
        parent::__construct($form);
    }

    /**
     * IMPORTANT side effect. This method will automatically authenticate using to CMS if
     * enable_cms_integration is on in config.
     *
     * @param Zend_Request_Interface $request
     * @return bool
     */
    function validate($request){       
        $result = true;

        //We check username for alphanumeric
        $username = $request->getParam('username', null);
        $validatorChain = new RM_Validate('Username');
        $usernameResult = $validatorChain->addValidator(new Zend_Validate_Alnum())->isValid($username);
        if (!$usernameResult) {
            $this->_errors = $validatorChain->getErrors();
            $result = false;
        }

        //We check password for alphanumeric
        $password = $request->getParam('password', null);
        $validatorChain = new RM_Validate('Password');
        $passwordResult = $validatorChain->addValidator(new Zend_Validate_Alnum())->isValid($password);
        if (!$passwordResult) {
            $this->_errors = array_merge($this->_errors, $validatorChain->getErrors());
            $result = false;
        }

        $config = new RM_Config();
        $isCmsAuthentication = $config->getValue('rm_config_enable_cms_integration');
        if ($isCmsAuthentication) {
            $authenticationResult = RM_Environment::getConnector()->authenticate(
                $request->getParam('username'),
                $request->getParam('password')
            );
            if ($authenticationResult !== true) {
                if (is_object($authenticationResult)) {
                    $this->_errors[] = $authenticationResult->getMessage();
                } else {
                    $this->_errors[] = 'UserNotFound';
                }
                $result = false;
            }
        } else {
            $userModel = new RM_Users();
            $user = $userModel->getBy($request->getParam('username'));
            if ($user === null) {
                $this->_errors[] = 'UserNotFound';
                $result = false;
            }

            //Finally we tries to find existing user in database with the same username/password
            $userModel = new RM_Users();
            $user = $userModel->getBy($request->getParam('username'), $request->getParam('password'));
            if ($user === null) {
                $this->_errors[] = 'WrongPassword';
                $result = false;
            }
        }
        return $result;
    }
}