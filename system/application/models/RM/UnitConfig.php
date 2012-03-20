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
class RM_UnitConfig extends RM_Model {

    protected $_name = 'rm_unit_config';
    protected $_rowClass = 'RM_Config_Field';

    public function getNewForm($typeID) {
        //TODO: change this to Zend_Select use
        $sql = "
            SELECT
                config.*
            FROM
                rm_unit_type_config type_config
            INNER JOIN rm_unit_config config ON
                config.id = type_config.unit_config_id
            WHERE
                type_config.unit_type_id='" . $typeID . "' AND config.view_new='1'
            ";
        return $this->_getBySQL($sql);
    }

    /**
     * Returns all default rows
     *
     * @return void
     */
    public function getDefault() {
        //TODO: for now all default values are below 10 or equal to 40 (group module id)
        //we need to change this logic later somehow
        return $this->fetchAll($this->select()->where('id<=10')->orWhere('id=40'));
    }

    public function getEditFormByUnit($unit) {
        $sql = "
                SELECT
                    config.*
                FROM
                    rm_unit_type_config type_config
                INNER JOIN rm_unit_config config ON
                    config.id = type_config.unit_config_id
                WHERE
                    type_config.unit_type_id = %s AND config.view_edit = 1
                ORDER BY ordering ASC
			";

        $sql = sprintf($sql, $unit->type_id);

        return $this->_getBySQL($sql);
    }

    public function getAll($typeId) {
        return array(
            'fields' => $this->_getAll($typeId, 0),
            'language' => $this->_getAll($typeId, 1)
        );
    }

    public function getAdminList($typeId) {
        $sql = "
			SELECT
				config.*
			FROM
				rm_unit_type_config type_config
			INNER JOIN rm_unit_config config ON
                config.id = type_config.unit_config_id
                AND config.admin_list = 1
			WHERE
				type_config.unit_type_id = $typeId
			";

        return $this->_getBySQL($sql);
    }

    protected function _getAll($typeId, $language) {
        $sql = "
			SELECT
				config.*
			FROM
				rm_unit_type_config type_config
			INNER JOIN rm_unit_config config ON
                config.id = type_config.unit_config_id
                AND config.language = %d
			WHERE
				type_config.unit_type_id = %s
			";

        $sql = sprintf($sql, $language, $typeId);

        return $this->_getBySQL($sql);
    }

    public function getReservationFields() {
        return $this->fetchAll($this->select()->where('admin_reservation=1')->where('language=0'));
    }

    public function getReservationLanguageFields() {
        return $this->fetchAll($this->select()->where('admin_reservation=1')->where('language=1'));
    }

    public function getAllReservationFields() {
        return $this->fetchAll($this->select()->where('admin_reservation=1'));
    }

    public function getFields($typeId) {
        return $this->_getAll($typeId, 0);
    }

    public function getLanguageFields($typeId) {
        return $this->_getAll($typeId, 1);
    }

}