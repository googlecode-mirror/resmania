<?php
class RM_EmailNotifications_Event_Handler_CustomerRegistrationSuccessful extends RM_EmailNotifications_Event_Handler
{    
    protected function _assign(Dwoo_Data $data)
    {
        $user = $this->_eventData->getUser();
        $user = $user->toArray();
        $data->assign('user', $user);
        return $data;
    }
}
 
