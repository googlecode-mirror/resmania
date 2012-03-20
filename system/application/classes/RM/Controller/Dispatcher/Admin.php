<?php
class RM_Controller_Dispatcher_Admin extends RM_Controller_Dispatcher {
	public function formatControllerName($unformatted)
    {
        return 'RM_Admin_'.$unformatted.'Controller';
    }
}
