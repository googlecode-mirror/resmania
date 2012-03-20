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
class RM_User_ErrorController extends RM_User_Controller {
    function errorAction()
    {
        $error = $this->_getParam('error_handler');
        $detailedErrorMessage = RM_Log::createErrorMessage($error->exception);

        $config = Zend_Registry::get('config');
        RM_Log::toLog($detailedErrorMessage, RM_Log::ERR);

        if ((int)$config->get('errors')->get('debug') == 1) {
            $errorMessage = $detailedErrorMessage;
            ob_end_clean();
            header('RMError: 1');
            echo nl2br($errorMessage);
            die();
        }

        switch ($error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()
                    ->setRawHeader('HTTP/1.1 404 Not Found');
                //TODO: add language constants, but better is to add a 404 page template
                $content = "Requested page not found.";
                break;
            default:
                $this->getResponse()
                    ->setHeader('RMError', '1');
                //TODO: add language constants, but better is to add a 404 page template
                $content = "Sorry, there was a server error. Please contact to site administrator.";
                break;
        }

        $this->getResponse()->clearBody();
        $this->view->content = $content;
    }
}