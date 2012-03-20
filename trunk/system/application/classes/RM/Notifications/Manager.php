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
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Notifications_Manager
{        
    /**
     * @var RM_Notifications_Manager
     */
    private static $_instance = null;

    /**
     * Array of RM_Notifications_Observer class names that need to be notified
     * @var array 
     */
    private $_observers = array();

    /**
     * Private constructor to implement the singleton pattern
     * If you want to get an object of the class you need to use static method "getInstance"          
     */
    private function __construct(){
        $this->_init();
    }

    /**
     * Will initialize the full observers structure
     */
    private function _init()
    {
        $model = new RM_Modules();
        $modules = $model->fetchAllEnabled();
        $manager = new RM_Module_Manager();        

        foreach ($modules as $module) {
            $moduleObject = $manager->getModule($module->name);
            //We will get the last enabled module in the system
            //that implements one of our internal interfaces
            if ($moduleObject instanceof RM_Notifications_Observer) {
                $this->_observers[] = $moduleObject;
            }
        }
    }

    /**
     * Factory method for getting an object
     *
     * @return RM_Notifications_Manager
     */
    public static function &getInstance(){
        if (self::$_instance === null) {            
            self::$_instance = new RM_Notifications_Manager();
        }
        return self::$_instance;
    }

    /**
     * This is the main method of fire every event in RM core
     *
     * @param string $eventName event name - one of the RM_Notifications_Event::<const>
     * @param mixed $data information that related to event
     */
    public function fire($eventName, $data)
    {
        foreach ($this->_observers as $observer) {
            $observer->notify($eventName, $data);
            RM_Log::toLog("Event Fired: ".$eventName);
        }
    }
}