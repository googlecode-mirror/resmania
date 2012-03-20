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
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
abstract class RM_Extension {

    /**
     * Extension name, this field uses as a primary key value.
     *
     * @var string
     */
    public $name;

    /**
     * Returns translated module name
     *
     * @param string $locale Locale name
     * @return string
     */
    public function getName($locale = null) {
        return RM_Environment::getInstance()
                ->getTranslation(RM_Environment::TRANSLATE_MAIN)
                ->_('Admin.' . $this->name . '.Main', 'name', $locale);
    }

    /**
     * Disable current extension.
     *
     * @todo This method is not in RM_Module_Interface and not in RM_Plugin_Interface
     * to prevent fatal error during using old extensions packages,
     * we should move this method to interfaces later. For now any module/plugin just need to overload this method to
     * add extra logic OR throw an Exception if it should not be disable.
     * @throw RM_Exception
     * @return bool - true is success, if failure exception will be raised
     */
    public function disable() {
        return true;
    }

}