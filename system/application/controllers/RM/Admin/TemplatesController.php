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
 * Template Controller.
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_TemplatesController extends RM_Controller {

    /**
     * This method returns content of the requested template. This is implicated in admin/templates/edit.js
     *
     * @param    request id template id
     * @return   json    data in format
     * id => templateID (id that was in original request)
     * content => html tempalte content of the requested template
     *
     * or if failure json format:
     * success => false
     * error => here will be translated error message with the current locale
     */
    function editJsonAction() {
        $templateID = $this->_getParam('id');
        if ($templateID == null) {
            return array('data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Templates', 'TemplateIsNotSpecified')
            ));
        }

        $templateModel = new RM_Templates();
        $template = $templateModel->find($templateID)->current();

        if ($template == null) {
            return array('data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Templates', 'NoTemplate') . $templateID
            ));
        }

        $iso = $this->_getParam('language', RM_Environment::getInstance()->getLocale());
        if (isset($template->$iso) == false) {
            return array('data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Templates', 'NoLocaleInTemplate') . $iso
            ));
        }

        $content = $template->$iso;
        return array(
            'data' => array(
                'id' => $templateID,
                'content' => $content,
                'language' => $iso
            )
        );
    }

    /**
     * This method save new content to the requested template
     *
     * @param    request id template ID
     * @param    request content html template content
     * @return   json    data in format
     * success => bool true or false
     * error => text if success will be false here will be error message translated with the current locale
     */
    function updateJsonAction() {
        $templateID = $this->_getParam('id');
        if ($templateID == null) {
            return array('data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Templates', 'TemplateIsNotSpecified')
            ));
        }

        $templateModel = new RM_Templates();
        $template = $templateModel->find($templateID)->current();

        if ($template == null) {
            return array('data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Templates', 'NoTemplate') . $templateID
            ));
        }

        $content = $this->_getParam('content');
        if ($content == null) {
            return array('data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Templates', 'ContentIsNotSpecified')
            ));
        }

        $iso = $this->_getParam('language', RM_Environment::getInstance()->getLocale());
        if (isset($template->$iso) == false) {
            return array('data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Templates', 'NoLocaleInTemplate') . $iso
            ));
        }

        $template->$iso = $content;
        $template->save();

        return array('data' => array('success' => true));
    }

}