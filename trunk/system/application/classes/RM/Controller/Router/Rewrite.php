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
abstract class RM_Controller_Router_Rewrite extends Zend_Controller_Router_Rewrite implements RM_Controller_Router_Interface {

    protected $_module;

    public function  __construct($module) {
        $this->_module = $module;
    }

    public function _($controller = '', $action = '', $options = array()){
        $userParams = array_merge($options, array('controller' => $controller, 'action' => $action));
        return $this->assemble($userParams);               
    }

    /* (non-PHPdoc)
     * @see system/libs/Zend/Controller/Router/Zend_Controller_Router_Rewrite#assemble()
     */
    public function assemble($userParams, $name = null, $reset = false, $encode = true)
    {
        if ($name == null) {
            try {
                $name = $this->getCurrentRouteName();
            } catch (Zend_Controller_Router_Exception $e) {
                $name = 'default';
            }
        }

        $params = array_merge($this->_globalParams, $userParams);

        $route = $this->getRoute($name);
        $url   = $route->assemble($params, $reset, $encode);        

        return $url;
    }    

    public function getAccembleURLJsCode($name = null)
    {
        if ($name == null) {
            try {
                $name = $this->getCurrentRouteName();
            } catch (Zend_Controller_Router_Exception $e) {
                $name = 'default';
            }
        }
        $route = $this->getRoute($name);
        return $route->getAccembleURLJsCode();
    }        
}