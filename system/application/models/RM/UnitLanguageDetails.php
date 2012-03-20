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
class RM_UnitLanguageDetails extends RM_Model_Multilingual
{
	protected $_name = 'rm_unit_language_details';
    protected $_primary = array('iso', 'unit_id');
	protected $_referenceMap    = array(
        'Languages' => array(
            'columns'           => 'language_ico',
            'refTableClass'     => 'RM_Languages',
            'refColumns'        => 'ico'
        ),
        'Units' => array(
            'columns'           => 'unit_id',
            'refTableClass'     => 'RM_Units',
            'refColumns'        => 'id'
        )
    );

    /**
     * Returns all rows assigned to a unit.
     *
     * @param RM_Unit_Row $unit
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchByUnit(RM_Unit_Row $unit)
    {
        return $this->fetchAll($this->select()->where('unit_id=?', $unit->id));
    }

    public function update($data)
    {
        parent::update($data, 'unit_id = "'.$data['unit_id'].'" AND iso = "'.$data['iso'].'"');
    }

    public function addLanguage($iso)
    {
        $languageModel = new RM_Languages();
        $defaultLocale = $languageModel->getDefaultLocale();

        $rows = $this->fetchAll($this->select()->where('iso=?', $defaultLocale));
        foreach ($rows as $row) {
            $newRow = $row->toArray();
            $newRow['iso'] = $iso;
            $this->insert($newRow);
        }
    }

    public function deleteLanguage($iso)
    {
        $this->delete('iso="'.$iso.'"');
    }
}