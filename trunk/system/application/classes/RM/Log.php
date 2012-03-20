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
 * Class standard logging of events to the ResMania system log
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */

class RM_Log extends Zend_Log {
    public static function toLog($message, $level = Zend_Log::INFO, $file = 'system_log.txt')
    {
        $filename = implode(DIRECTORY_SEPARATOR, array(
            RM_Environment::getConnector()->getCorePath(),
            'userdata',
            'logs',
            $file
        ));

        if (is_file($filename) == false) {
            return false;
        }

        if (is_writable($filename) == false) {
            return false;
        }

        try {
            $stream = new Zend_Log_Writer_Stream($filename);
            $logger = new RM_Log($stream);
            $logger->log($message, $level);
            $stream->shutdown();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function createErrorMessage(Exception $exception)
    {
        $message = "Error: ".$exception->getMessage()."\n";
        $message.= "Trace: \n".$exception->getTraceAsString();        
        return $message;
    }
}