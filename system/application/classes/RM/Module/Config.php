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
class RM_Module_Config {
    var $information = array(
        "name" => "",
        "sort_order" => "0",
        "author" => "",
        "creation_date" => "1970-00-00",
        "copyright" => "",
        "license" => "",
        "author_email" => "",
        "author_url" => "",
        "version" => "",
        "description" => "",
        "core" => "0",
        "preferences" => ""
    );
    
    var $dependencies = array();
    
    const INSTALL_SQL = "install.sql";
    const UNINSTALL_SQL = "uninstall.sql";
    const SQL = "sql";
    const CONTROLLERS = "controllers";
    const JS = "js";
    const VIEWS = "views";    
    const CLASSES = "classes";        
    const IMAGES = "images";
    const CSS = "css";
}
