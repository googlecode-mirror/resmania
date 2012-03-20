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
class RM_Licensing extends RM_Model {
    protected $_name = 'rm_licensing';

    protected function _getAll()
    {
        $sql = "
            SELECT
                DISTINCT *
            FROM
                rm_licensing
            ";

        return $this->_getBySQL($sql);
    }

    public function getAll()
    {
        return $this->_getAll();
    }

    public function getByKey($key){
        $sql = "
            SELECT
                *
            FROM
                rm_licensing
            WHERE
                license_key='".$key."'";

        return $this->_getBySQL($sql);
    }
}