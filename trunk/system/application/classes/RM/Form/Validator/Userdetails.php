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
class RM_Form_Validator_Userdetails extends RM_Form_Validator {
    /**
     * List of all panel ids that need to be validated
     * @var array
     */
    private $_neeedToBeValidatedPanelsIDs = array(
        'captcha_user',
        'email_user',
        'password_user',
        'passwordrepeat_user',
        'username_user',        
        'phonenumber_user',
        'mobilenumber_user',
        'terms_user'
    );

    /**
     * @var Zend_Request_Interface
     */
    private $_request;

    /**
     * @param RM_Form_Row $form
     */
    function  __construct($form) {
        parent::__construct($form);
    }    

    /**
     * Validate information from request
     *
     * @param Zend_Request_Interface $request
     * @return bool
     */
    function validate($request){
        $this->_request = $request;

        $valid = true;
        $state = $this->_form->getState();        

        foreach ($state as $column) {
            foreach ($column as $panel) {
                if (in_array($panel->id, $this->_neeedToBeValidatedPanelsIDs)){
                    $methodName = '_'.$panel->id.'_validate';                    
                    $valid &= $this->$methodName(); //error text messages will be automatically assigned into $this->_errors internal array
                }
            }
        }
        return $valid;
    }

    private function _numberic_notrequired_fields_validate($name){
        $validatorChain = new RM_Validate(ucfirst($name));
        $result = $validatorChain
            ->addValidator(new Zend_Validate_NotEmpty())
            ->isValid($this->_request->getParam($name, null));

        if (!$result) {            
            return true;
        }

        $validatorChain = new RM_Validate(ucfirst($name));
        $result = $validatorChain
            ->addValidator(new Zend_Validate_Digits())
            ->isValid($this->_request->getParam($name, null));

        if (!$result) {
            $this->_errors = array_merge($this->_errors, $validatorChain->getErrors());
        }
        return $result;
    }

    private function _terms_user_validate()
    {
        $validatorChain = new RM_Validate('Terms');
        $result = $validatorChain->addValidator(new Zend_Validate_NotEmpty())->isValid($this->_request->getParam('terms', null));
        if (!$result) {
            $this->_errors = array_merge($this->_errors, $validatorChain->getErrors());
        }
        return $result;
    }

    /**
     * Telephone need to be numeric.
     *
     * @todo here could be added all other restrictions for telephone number, i.e. minimum length
     * @return bool
     */
    private function _phonenumber_user_validate(){
        return $this->_numberic_notrequired_fields_validate('telephone');
    }

    /**
     * Mobile phone number need to be numeric.
     *
     * @todo here could be added all other restrictions for mobile phone number, i.e. minimum length
     * @return bool
     */
    private function _mobilenumber_user_validate(){
        return $this->_numberic_notrequired_fields_validate('mobile');        
    }

    /**
     * Validate user captcha information. Depending on what 
     * captcha enabled in system.
     *
     * @return bool
     */
    private function _captcha_user_validate(){
        $user = RM_Reservation_Manager::getInstance()->getUser();
        if ($user) {
            return true;
        }
        $result = RM_Environment::getInstance()->getCaptcha()->validate();
        if (!$result) {
            $this->_errors[] = 'CaptchaCodeIsInvalid';
        }
        return $result;
    }

    /**
     * Validate user email using Zend_Validate_EmailAddress class
     *
     * @return bool
     */
    private function _email_user_validate(){
        $validatorChain = new RM_Validate('EmailAddress');
        $result = $validatorChain->addValidator(new Zend_Validate_EmailAddress())->isValid($this->_request->getParam('email', ''));
        if (!$result) {
            $this->_errors = array_merge($this->_errors, $validatorChain->getErrors());
        }
        return $result;
    }

    /**
     * Password need to be alphanumeric.
     *
     * @todo here could be added all other restrictions for password security, i.e. minimum length
     * @return bool
     */
    private function _password_user_validate(){
        $validatorChain = new RM_Validate('Password');
        $result = $validatorChain
            ->addValidator(new Zend_Validate_Alnum())
            ->isValid($this->_request->getParam('password', null));
            
        if (!$result) {
            $this->_errors = array_merge($this->_errors, $validatorChain->getErrors());
        }
        return $result;
    }

    /**
     * Password repeat also need to be alpha numeric and exactly the same as a password
     *
     * @return bool
     */
    private function _passwordrepeat_user_validate(){
        $validatorChain = new RM_Validate('PasswordRepeat');
        $result = $validatorChain
            ->addValidator(new Zend_Validate_Alnum())
            ->addValidator(new Zend_Validate_Identical($this->_request->getParam('password', null)))
            ->isValid($this->_request->getParam('password_repeat', null));
            
        if (!$result) {
            $this->_errors = array_merge($this->_errors, $validatorChain->getErrors());
        }
        return $result;
    }

    /**
     * Username should be non empty and alpha numeric
     *
     * @return bool
     */
    private function _username_user_validate(){
        $validatorChain = new RM_Validate('Username');
        $result = $validatorChain
            ->addValidator(new Zend_Validate_Alnum())
            ->isValid($this->_request->getParam('username', null));
            
        if (!$result) {
            $this->_errors = array_merge($this->_errors, $validatorChain->getErrors());
        }
        return $result;
    }
}