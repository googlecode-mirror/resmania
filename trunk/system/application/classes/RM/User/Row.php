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
* User Row Class
*
* @access       public
* @author       Rob/Valentin
* @copyright    2011 ResMania Ltd.
* @version      1.2
* @link         http://docs.resmania.com/api/
* @since        06-2011
*/

class RM_User_Row extends RM_Row {
    function isGuest(){
        return !$this->isRegistered();
    }

    function isRegistered(){
        if (isset($this->id) && $this->id != ''){
            return true;
        }
        return false;
    }

    function getTitle($iso = null){
        $titles = Zend_Json::decode(str_replace("'", '"', RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN)->_('Common.JSON', 'Titles')));
        foreach ($titles as $title) {
            if ($this->title == $title['id']) return $title['title'];
        }
        return '';
    }
}