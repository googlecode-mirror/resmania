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
 *
 * Admin Language Contoller
 *
 * This handles all AJAX requests from the Admin GUI Language Section.
 * These methods will create an AJAX response containing JSON data. The JSON
 * data is read by the JS code and rendered into interface.
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_User_LanguageController extends RM_Language_Controller {

    public function listJsAction() {
        $languageModel = new RM_Languages();
        $languages = $languageModel->fetchAll();
        foreach ($languages as $language) {
            $jsonLanguages[] = array($language->iso, $language->name);
        }
        return "RM.Languages = " . Zend_Json::encode($jsonLanguages) . ";";
    }

    public function getconstantsAction() {
        ob_clean();
        echo RM_JavaScript_Loader::getConstants();
        die;
    }

}