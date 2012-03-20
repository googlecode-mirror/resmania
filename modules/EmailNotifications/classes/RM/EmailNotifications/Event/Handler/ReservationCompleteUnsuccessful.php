<?php
class RM_EmailNotifications_Event_Handler_ReservationCompleteUnsuccessful extends RM_EmailNotifications_Event_Handler
{    
    protected function _assign(Dwoo_Data $data)
    {
        try{
            $details = $this->_eventData->getAllDetails();

            $arrayDetails = array();
            foreach ($details as $detail){
                $arrayDetails[] = array(
                    'unit' => $detail->getUnit()->toArray(),
                    'period' => (array)$detail->getPeriod(),
                    'periodtime' => (array)$detail->getPeriod(true),
                    'persons' => $detail->getPersons()->getAll(),
                    'total' => $detail->getTotal()
                );
            }

            $data->assign('details', $arrayDetails);
            $data->assign('user', $this->_eventData->getUser()->toArray());
            $data->assign('reservation_id', $this->_eventData->getReservationID());
        } catch (Exception $e){
            return false;
        }
        return $data;
    }
    
}
 
