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
* This interface will present CMS user object to Resmania core code
*
* @access       public
* @author       Rob/Valentin
* @copyright    2011 ResMania Ltd.
* @version      1.2
* @link         http://docs.resmania.com/api/
* @since        06-2011
*/

interface RM_User_CMS_Interface {
    /**
     * This method indicates is CMS user a guest or already registered CMS user
     * @return bool
     */
    public function isGuest();

    /**
     * Tries to find a Resmania user that linked to CMS user, if not returns null
     * @return RM_User_Row|null
     */
    public function findResmaniaUser();

    /**
     * Convert CMS user objcet into RM_User_Row object (without saving it into database)
     * @return RM_User_Row
     */
    public function convertToResmaniaUser();
}