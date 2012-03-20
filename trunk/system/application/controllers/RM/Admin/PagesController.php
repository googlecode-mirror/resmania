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
 * Admin Page Contoller
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_PagesController extends RM_Controller
{
    /**     
     * This method returns content of the requested page. This is implicated in admin/pages/edit.js
     *
     * @param    request id pageID in format <folder name>.<folder name>. ... .<filename without extension>, first folder name should be under core views folder: RM/userdata/views
     * @return   json    data in format
     * id => pageID (id that was in original request)
     * content => html page content of the requested page
     *
     * or if failure json format:
     * success => false
     * error => here will be translated error message with the current locale
     */
    function editJsonAction(){
        $pageID = $this->_getParam('id');
        if ($pageID == null) {
            return array('data' => array(
                'success' => false,
                'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Pages', 'PageIsNotSpecified')
            ));
        }
               
        $filename = $this->_convert($pageID);

        if (is_file($filename) == false) {
            return array('data' => array(
                'success' => false,
                'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Pages', 'NoFile').$filename
            ));
        }

        $content = file_get_contents($filename);
        if ($content === false) {
            return array('data' => array(
                'success' => false,
                'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Pages', 'FileIsNotReadable').$filename
            ));
        }        

        //json = {
        //  id: pageID
        //  content: html from file
        //}
        return array(
            'data' => array(
                'id' => $pageID,
                'content' => $content
            )
        );
    }

    /**          
     * This method save new content to the requested page
     *
     * @param    request id pageID in format <folder name>.<folder name>. ... .<filename without extension>, first folder name should be under core views folder: RM/userdata/views
     * @param    request content html file content that need to saved into file directly
     * @return   json    data in format
     * success => bool true or false
     * error => text if success will be false here will be error message translated with the current locale
     */
    function updateJsonAction()
    {
        $pageID = $this->_getParam('id');
        if ($pageID == null) {
            return array('data' => array(
                'success' => false,
                'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Pages', 'PageIsNotSpecified')
            ));
        }

        $filename = $this->_convert($pageID);

        if (is_file($filename) == false) {
            return array('data' => array(
                'success' => false,
                'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Pages', 'NoFile').$filename
            ));
        }        

        $content = $this->_getParam('content');
        if ($content == null) {
            return array('data' => array(
                'success' => false,
                'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Pages', 'ContentIsNotSpecified')
            ));
        }

        $result = file_put_contents($filename, $content);
        if ($result === false) {
            return array('data' => array(
                'success' => false,
                'error' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Config.Pages', 'FileIsNotWriteable').$filename
            ));
        }

        return array('data' => array(
            'success' => true
        ));
    }

    /**
     * Converts page ID into full filename
     *
     * @param string $pageID
     * @return string filename
     */
    private function _convert($pageID){
        $path = explode('.', $pageID);
        $filename = $path[count($path) - 1].'.phtml'; //the last will be a filename, all our templates have .phtml extension
        unset($path[count($path) - 1]); //we don't need to include file name as folder name

        $filename = implode(DIRECTORY_SEPARATOR, array(
            RM_Environment::getConnector()->getRootPath(),
            'RM',
            'userdata',
            'views',
            implode(DIRECTORY_SEPARATOR, $path),
            $filename
        ));

        return $filename;
    }
}