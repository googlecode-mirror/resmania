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
class RM_Form_Validator {
    /**     
     * @var RM_Form_Row
     */
    protected $_form;

    /**
     * Array of error while validating
     * 
     * @var array 
     */
    protected $_errors = array();
    
    /**
     * @param RM_Form_Row $form
     */
    function __construct($form){
        $this->_form = $form;
    }

    /**
     * This method need to be overriden in child classes
     *
     * @param Zend_Request_Interface $request
     * @return bool in this class this method always returns true
     */
    function validate($request){
        return true;
    }

    /**
     * Returns array of error ids in selected locale
     *     
     * @return array
     */
    public function getErrors(){
        return $this->_errors;        
    }    
}