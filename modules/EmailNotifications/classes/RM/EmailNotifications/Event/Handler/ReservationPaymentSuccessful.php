<?php
class RM_EmailNotifications_Event_Handler_ReservationPaymentSuccessful extends RM_EmailNotifications_Event_Handler
{    
    protected function _assign(Dwoo_Data $data)
    {
        $details = $this->_eventData->getAllDetails();
        $reservationID = $this->_eventData->getReservationID();
        $reservationModel = new RM_Reservations();
        $reservation = $reservationModel->find($reservationID)->current();

        $arrayDetails = array();
        foreach ($details as $detail){
            $arrayDetails[] = array(
                'unit' => $detail->getUnit()->toArray(),
                'period' => (array)$detail->getPeriod(),
                'periodtime' => (array)$detail->getPeriod(true),
                'persons' => $detail->getPersons(),
                'total' => $detail->getTotal()
            );
        }

        // total paid and total due
        $billing = new RM_Billing();
        $priceCharges = $billing->getPrice($reservationID);
        $billingArray['tax'] = $priceCharges->tax;
        $billingArray['paid'] = $billing->getPaymentsTotal($reservation);
        $billingArray['due'] = $priceCharges->total;
        $billingArray['confirmed'] = $reservation->confirmed ? $translate->_('MessageYes') : $translate->_('MessageNo');

        $data->assign('details', $arrayDetails);
        $data->assign('reservation_id', $this->_eventData->getReservationID());
        return $data;
    }
}
 
