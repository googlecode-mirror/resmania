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
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_ReservationConfig extends RM_Model {
    protected $_name = 'rm_reservation_config';
    protected $_rowClass = 'RM_Config_Field';

    public function getAdminList() {
        return $this->fetchAll("admin_list_preferences != ''");
    }

    /**
     * Returns all fields that need to be shown on the admin end on reservation edit page.
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getAdminEditFields() {
        return $this->fetchAll($this->select()->where("details = 0")->where("admin_view_edit != ''"));
    }

    public function getFields() {
        return $this->fetchAll('details = 0');
    }

    public function getDetailFields() {
        return $this->fetchAll('details = 1');
    }

    public function getWizardFields() {
        return $this->fetchAll('admin_view_wizard != ""');
    }

    public function getBlockFields() {
        return $this->fetchAll('admin_view_block != ""');
    }

    public function getAdminEdit() {
        return $this->fetchAll('admin_edit_preferences !=""');
    }

}