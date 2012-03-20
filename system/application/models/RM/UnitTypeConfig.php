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
class RM_UnitTypeConfig extends RM_Model {
	protected $_name = 'rm_unit_type_config';
	protected $_rowClass = 'RM_Config_Field';
	protected $_referenceMap    = array(
        'UnitConfig' => array(
            'columns'           => 'unit_config_id',
            'refTableClass'     => 'RM_UnitConfig',
            'refColumns'        => 'id'
        ),
        'UnitTypes' => array(
            'columns'           => 'unit_type_id',
            'refTableClass'     => 'RM_UnitTypes',
            'refColumns'        => 'id'
        )
    );

    /**
     * Register new unit type for all DEFAULT unit config value except some providerd
     *
     * @param  $unitTypeID
     * @param array $except
     * @return void
     */
    function register($unitTypeID, $except = array())
    {
        $model = new RM_UnitConfig();
        $default = $model->getDefault();
        foreach ($default as $row) {
            if (in_array($row->id, $except)){
                continue;
            }
            if ($this->exist($unitTypeID, $row->id) == false) {
                $this->insert(array(
                    'unit_type_id' => $unitTypeID,
                    'unit_config_id' => $row->id
                ));
            }
        }
    }

    function exist($unitTypeID, $unitConfigID)
    {
        return ($this->fetchAll($this->select()
                ->where('unit_type_id='.$unitTypeID)
                ->where('unit_config_id='.$unitConfigID))->count() != 0);
    }
}