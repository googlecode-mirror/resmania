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
 * Class for creation validation chain.
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */

class RM_Validate extends Zend_Validate {
    /**
     * This error prefix we will use to transform zend error ids to rm error ids     
     * @var string
     */
    protected $_errorPrefix = '';

    /**
     * @param string $errorPrefix this error prefix we will use to transform zend error ids to rm error ids
     */
    public function  __construct($errorPrefix = '') {
        $this->_errorPrefix = $errorPrefix;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns array of validation failure message codes
     *
     * @return array     
     */
    public function getErrors(){
        $zendErrors = parent::getErrors();
        $rmErrors = array();
        foreach ($zendErrors as $error){
            $rmErrors[] = $this->_errorPrefix.ucfirst($error);
        }
        return $rmErrors;
    }
}