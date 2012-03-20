<?php
class RM_EmailNotifications_Event_Handler_ReservationCompleteSuccessful extends RM_EmailNotifications_Event_Handler
{    
    protected function _assign(Dwoo_Data $data)
    {
        $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);

        $locationsDAO = new RM_Locations();
        $reservationID = $this->_eventData->getReservationID();
        $reservationModel = new RM_Reservations();
        $reservation = $reservationModel->find($reservationID)->current();

        // reservation details
        $details = $this->_eventData->getAllDetails();
        $arrayDetails = array();
        foreach ($details as $detail){

            $unit = $detail->getUnit()->toArray();
            $period = $detail->getPeriod()->toArray();
            $periodWithTime = $detail->getPeriod()->toArray(true);

            $location = $locationsDAO->fetchByUnit($unit['id'])->toArray();

            $extrasForTemplate = array();
            $extras = $detail->getExtras();
            foreach ($extras as $extra) {
                $extrasForTemplate[] = $extra->toArray();
            }

            $arrayDetails[] = array(
                'unit' => $unit,
                'locationInfo' => isset($location[0]) ? $location[0] : '',
                'period' => $period,
                'periodtime' => $periodWithTime,
                'persons' => $detail->getPersons()->getAll(),
                'total' => $detail->getTotal(),
                'extras' => $extrasForTemplate
            );
        }

        // total paid and total due
        $billing = new RM_Billing();
        $priceCharges = $billing->getPrice($reservationID);
        $billingArray['tax'] = $priceCharges->tax;
        $billingArray['paid'] = $billing->getPaymentsTotal($reservation);
        $billingArray['due'] = $priceCharges->total;
        $billingArray['confirmed'] = $reservation->confirmed ? $translate->_('MessageYes') : $translate->_('MessageNo');

        // return the data to the template
        $data->assign('extras', $extrasForTemplate);
        $data->assign('details', $arrayDetails);
        $data->assign('user', $this->_eventData->getUser()->toArray());
        $data->assign('reservation_id', $reservationID);
        $data->assign('billing', $billingArray);

        return $data;
    }
}
 
