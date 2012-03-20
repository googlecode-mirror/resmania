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
class RM_Languages extends RM_Model {
    protected $_name = 'rm_languages';
    protected $_defaultLocale = 'en_GB';

    public function getDefaultLocale()
    {
        return $this->_defaultLocale;
    }

    public function fetchAllEnabled()
    {
        return $this->fetchAll($this->select()->where('enabled=1'));
    }

    function disable($language)
    {
        $language->enabled = 0;
        return $language->save();
    }

    function enable($language)
    {
        $language->enabled = 1;
        return $language->save();
    }

    /**
     * Returns the language in the system, by locale. If there was no this locale in the system, tries to find
     * other language with the same ISO code, if there was no this language installed returns the default one 'en-GB'
     *
     * @param  $locale - string locale in format: <language>_<TRANSLATION>
     * @return Zend_Db_Table_Row_Abstract
     */
    public function find($locale = null)
    {
        if ($locale == null || strlen($locale)<5) {
            return parent::find($this->_defaultLocale);
        }

        $rows = parent::find($locale);
        if ($rows->count() !== 0){
            return $rows;
        }

        list($language, $translation) = explode('_', $locale);        
        $rows = $this->fetchAll($this->select()->where("iso LIKE '".$language."_%'"));
        if ($rows->count() !== 0) return $rows;
        
        return parent::find($this->_defaultLocale);
    }
}