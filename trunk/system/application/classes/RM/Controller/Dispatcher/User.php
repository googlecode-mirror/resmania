<?php
class RM_Controller_Dispatcher_User extends RM_Controller_Dispatcher {
	public function formatControllerName($unformatted)
    {
        return 'RM_User_'.$unformatted.'Controller';
    }
}