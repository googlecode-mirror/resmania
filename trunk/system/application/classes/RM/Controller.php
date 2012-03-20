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
* Base action controller class
*
* @access       public
* @author       Valentin
* @copyright    2011 ResMania Ltd.
* @version      1.2
* @link         http://docs.resmania.com/api/
* @since        06-2011
*/

class RM_Controller extends Zend_Controller_Action {
    /**
     * @todo this need to be replaced with directly RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN) calling
     * @var Zend_Translate
     */
    protected $_translate;   

    /**
     * @see system/libs/Zend/Controller/Zend_Controller_Action#preDispatch()
     */
    public function preDispatch() {        
        $this->view->setTranslate(RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN));
        $this->_translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
        $this->view->setRouter($this->getFrontController()->getRouter());

        $config = new RM_Config();
        $this->view->guiDateFormat = $config->getJSDateformat();
        $this->view->GUIMaximisedState = $config->getValue("rm_config_admin_gui_maximised");
        $this->view->mysqlDateFormat = RM_Config::MYSQL_DATEFORMAT;
        $this->view->phpDateFormat = RM_Config::PHP_DATEFORMAT;
        $this->view->calStartDay = $config->getValue("rm_config_calendar_startday");
        $this->view->CMSIntegration = $config->getValue("rm_config_enable_cms_integration");
        $this->view->enableUserGroups = $config->getValue("rm_config_enable_user_groups");
        $this->view->enableUnitsOnTreeMenu = $config->getValue("rm_config_enable_units_on_treemenu");
        $this->view->reservationsListBufferSize = $config->getValue("rm_config_reservations_list_buffersize");
        $this->view->editorType = $config->getValue("rm_config_editor");
    }
    
    /**
     * Indicates if we don't need a view on action
     *
     * @return null
     */
    public function _withoutView(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    }

    protected function _redirect($controller, $action, $options = array()) {
        parent::_redirect(RM_Environment::getInstance()->getRouter()->_($controller, $action, $options));
    }

    /**
     * If action name contains 'json', this method delete all info in output buffering
     * all returning values from action method of the controller will be immidiately 'echo'
     * to browser and after that script will 'die'.
     *
     * @param string $action - action name
     */
    public function dispatch($action) {
    // Notify helpers of action preDispatch state
        $this->_helper->notifyPreDispatch();

        $this->preDispatch();
        if ($this->getRequest()->isDispatched()) {
            if (null === $this->_classMethods) {
                $this->_classMethods = get_class_methods($this);
            }

            if (strpos($action, 'json') !== false) {
                $chunks = explode('json', $action);
                $action = $chunks[0].'Json'.$chunks[1];
            }

            // preDispatch() didn't change the action, so we can continue
            if ($this->getInvokeArg('useCaseSensitiveActions') || in_array($action, $this->_classMethods)) {
                if ($this->getInvokeArg('useCaseSensitiveActions')) {
                    trigger_error('Using case sensitive actions without word separators is deprecated; please do not rely on this "feature"');
                }

                //We need to check if there is a 'json' prefix in the method name                
                if (strpos($action, 'Json') !== false) {
                    //We have a special algorithm for JSON method parsing
                    ob_start();
                    $this->_withoutView();
                    $jsonReturn = $this->$action();
                    if (!isset($jsonReturn['encoded']) ||
                        (isset($jsonReturn['encoded']) && $jsonReturn['encoded'] == false)) {
                        $jsonReturn['data'] = Zend_Json::encode($jsonReturn['data']);
                    }
                    ob_clean();
                    echo $jsonReturn['data'];                    
                    $this->_helper->notifyPostDispatch();
                    die();
                } else {
                    $this->$action();
                }
            } else {
                    $this->__call($action, array());
            }
            $this->postDispatch();
        }

        // whats actually important here is that this action controller is
        // shutting down, regardless of dispatching; notify the helpers of this
        // state
        $this->_helper->notifyPostDispatch();
    }

    /**
     * @deprecated
     */
    protected function _renderAJAX() {
        ob_clean();
        $this->_helper->layout->disableLayout();

        $view   = $this->initView();
        $script = $this->getViewScript($action, $noController);
        echo $view->render($script);
        die();
    }

    /**
     * Using patern 'extend parent' we add extra functionality to check
     * PHP magic quotes and if them on remove slashes from value
     *
     * @param string $paramName
     * @param mixed $default
     * @return mixed
     */
    protected function _getParam($paramName, $default = null) {
        $value = parent::_getParam($paramName, $default);
        if (get_magic_quotes_gpc() && $value !== $default) {
            $value = $this->_getParamFilter($value);
        }
        return $value;
    }

    protected function _getParamFilter($value){
        if (is_array($value)) {
            foreach ($value as $key => $val){
                $value[$key] = $this->_getParamFilter($val);
            }
        } elseif (is_object($value) == false) {
            $value = urldecode(stripslashes($value));
        }
        return $value;
    }
}