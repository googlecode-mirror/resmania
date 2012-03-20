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
 * Error Controller
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_ErrorController extends RM_Controller {

    function errorAction() {
        $error = $this->_getParam('error_handler');
        $detailedErrorMessage = RM_Log::createErrorMessage($error->exception);

        $config = Zend_Registry::get('config');
        $logResult = RM_Log::toLog($detailedErrorMessage, RM_Log::ERR);

        if ((int) $config->get('errors')->get('debug') == 1) {
            $errorMessage = nl2br($detailedErrorMessage);
        } else {
            //TODO: add language constants.
            $errorMessage = "There was internal server error.";
            if ($logResult == false) {
                $errorMessage.= " Please make file system_log.txt writable to log error messages.";
            } else {
                $errorMessage.= " All information is in system_log.txt file.";
            }
        }

        ob_end_clean();
        header('RMError: 1');
        echo $errorMessage;
        die();
    }

}
