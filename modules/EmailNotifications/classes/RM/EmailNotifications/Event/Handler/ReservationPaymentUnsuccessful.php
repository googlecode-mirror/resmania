<?php
class RM_EmailNotifications_Event_Handler_ReservationPaymentUnsuccessful extends RM_EmailNotifications_Event_Handler
{    
    protected function _assign(Dwoo_Data $data)
    {
       
        $data->assign('reservation_id', $this->_eventData->getReservationID());
        return $data;
    }
}
 
