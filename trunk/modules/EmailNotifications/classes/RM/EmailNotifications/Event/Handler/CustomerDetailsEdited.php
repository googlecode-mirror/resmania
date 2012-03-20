<?php
class RM_EmailNotifications_Event_Handler_CustomerDetailsEdited extends RM_EmailNotifications_Event_Handler
{    
    protected function _assign(Dwoo_Data $data)
    {
        $data->assign('user', $this->_eventData->getUser()->toArray());
        return $data;
    }
}
 
