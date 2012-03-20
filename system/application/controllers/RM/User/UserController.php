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
 * Front-end user controller
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_User_UserController extends RM_User_Controller {

    /**
     * Show terms and conditions. This method returns the terms and conditons
     * from the terms and conditions template
     *
     * @return string   html
     */
    public function showtermsAction(){
        $model = new RM_Templates();
        $template = $model->find('TermsConditions')->current();
        $iso = RM_Environment::getInstance()->getLocale();
        $html  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $html .= '<style type="text/css">body {background-color: #FFFFFF;}</style></head><body>';
        $html .= $template->$iso;
        $html .= '</body></html>';
        echo $html;
        die;
    }

    /**
     * Show user details form with all panels defined by form designer.
     *
     * @return  object  view data
     */
    public function userdetailsAction(){
        
        $user = RM_Reservation_Manager::getInstance()->getUser();
        if ($user == null) {
            $config = new RM_Config();
            if ($config->getValue('rm_config_enable_cms_integration')) {
                $cmsUser = RM_Environment::getConnector()->getUser();
                if ($cmsUser->isGuest() == false) {
                    $user = $cmsUser->findResmaniaUser();
                    if ($user == null) {
                        $user = $cmsUser->convertToResmaniaUser();
                    }
                    RM_Reservation_Manager::getInstance()->setUser($user);
                }
            }
        }
        $this->view->user = $user;

        // if we have errors reset the saved user data...
        $errors = RM_Reservation_Manager::getInstance()->getFormErrors('userdetails');
        if (count($errors) !== 0){
            RM_Reservation_Manager::getInstance()->resetUser($user);
        }

        $formModel = new RM_Forms();
        $form = $formModel->find('userdetails')->current();
        

        $this->view->details_state = $form->getState();
        $this->view->details_form = $form;

        $formModel = new RM_Forms();
        $form = $formModel->find('login')->current();
        $this->view->login_state = $form->getState();
        $this->view->login_form = $form;
    }

    /**
     * User login action
     * all code is in userdetailsAction
     */
    function userloginAction(){}

    /**
     * Login validate action
     *
     * validates the users login then redirects the user
     */
    function loginvalidateAction(){
        $this->_withoutView();

        $formModel = new RM_Forms();
        $form = $formModel->find('login')->current();
        $valid = $form->validate($this->getRequest());

        if (!$valid) {
            RM_Reservation_Manager::getInstance()
                ->setFormErrors('login', $form->getErrors())
                ->save();
            $this->_redirect('User', 'userdetails');
        }

        $config = new RM_Config();
        $isCmsAuthentication = $config->getValue('rm_config_enable_cms_integration');
        if ($isCmsAuthentication) {
            $cmsUser = RM_Environment::getConnector()->getUser();
            $user = $cmsUser->findResmaniaUser();
            if ($user == null) {
                $user = $cmsUser->convertToResmaniaUser();
            }
            RM_Reservation_Manager::getInstance()->setUser($user);
        } else {
            $userModel = new RM_Users;
            $user = $userModel->getBy($this->_getParam('username'), $this->_getParam('password'));
        }

        if ($user !== null) {
            RM_Reservation_Manager::getInstance()
                ->resetFormErrors('login')
                ->setUser($user)
                ->save();
            $this->_redirect('Reservations', 'summary');
        }
    }

    /**
     * Action for validating user details form parameters.
     *
     * If some of the parameters are invalid this method will redirect user to previous page with error
     * text messages about every wrong parameter.
     * If all user detail information is valid this method will save user information into global
     * reservation manager object and will redirect user to the next step of the reservation process.
     */
    function detailsvalidateAction(){
        $this->_withoutView();
        
        $user = RM_Reservation_Manager::getInstance()->getUser();
        if ($user == null || $user->isGuest()) {
            $userModel = new RM_Users;

            // validate reCaptcha
            $config = new RM_Config();
            $useReCaptcha = $config->getValue('rm_config_recaptcha_enabled');

            if ($useReCaptcha){
                $reCaptcha = new RM_Captcha_Recaptcha();
                if (!$reCaptcha->validate()){
                    RM_Reservation_Manager::getInstance()
                        ->resetFormErrors('userdetails')
                        ->setFormErrors('userdetails', RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('RM.User.Creation','CaptchaIncorrect'))
                        ->save();

                    $user = $userModel->createNewUser($this->getRequest(), RM_UserGroups::REGULAR, true);
                    RM_Reservation_Manager::getInstance()->setUser($user);

                    $this->_redirect('User', 'userdetails');
                }
            }

            try {
                $user = $userModel->createNewUser($this->getRequest());
            } catch (RM_Exception $e) {
                RM_Reservation_Manager::getInstance()
                    ->resetFormErrors('userdetails')
                    ->setFormErrors('userdetails', RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('RM.User.Creation', $e->getMessage()))
                    ->save();

                $user = $userModel->createNewUser($this->getRequest(), RM_UserGroups::REGULAR, true);
                RM_Reservation_Manager::getInstance()->setUser($user);                

                $this->_redirect('User', 'userdetails');
            }
        }        

        //Save user object in global reservation manager object
        RM_Reservation_Manager::getInstance()->setUser($user);
        
        $this->_fireUserCreationEvent();
        
        $formModel = new RM_Forms();
        $form = $formModel->find('userdetails')->current();
        $valid = $form->validate($this->getRequest());       
        if ($valid) {
            RM_Reservation_Manager::getInstance()
                ->resetFormErrors('userdetails')
                ->save();

            //TODO: add code for getting next stage controller/action from admin preferences
            $controller = 'Reservations';
            $action = 'summary';
            $this->_redirect($controller, $action);
        } else {
            RM_Reservation_Manager::getInstance()
                ->setFormErrors('userdetails', $form->getErrors())
                ->save();
                
            $this->_redirect('User', 'userdetails');
        }
    }

    private function _fireUserCreationEvent(){

        $manager = RM_Reservation_Manager::getInstance();
        if ($manager->getCriteria() === null){
            return false;
        }
        
        // fire the notification event
        return RM_Notifications_Manager::getInstance()->fire('CustomerRegistrationSuccessful',$manager);
    }
}
