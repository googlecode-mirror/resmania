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
* Admin menu interface
*
* @access       public
* @author       Rob/Valentin
* @copyright    2011 ResMania Ltd.
* @version      1.2
* @link         http://docs.resmania.com/api/
* @since        06-2011
*/
interface RM_Admin_Menu_Interface {
    /**
     * This method will return node object for main admin menu tree.     
     * If there is no need to present a node in the main admin tree method should return NULL
     *
     * @return stdClass | null
     */
     public function getNode();

     /**
     * This method will return node object for config admin menu tree.
     * If there is no need to present a node in the config admin tree method should return NULL
     *
     * @return stdClass | null
     */
     public function getConfigNode();
}