<?php
class RM_User_PaylaterController extends RM_Controller
{
    public function formAction(){
        $this->_withoutView();
        
        $bookingref = RM_Reservation_Manager::getInstance()->getReservationID();

        $billingModel = new RM_Billing();
        $billingRow = $billingModel->createRow();
        $billingRow->reservation_id = $bookingref;
        $billingRow->total_paid = '0.00';
        $billingRow->save();

        RM_Reservation_Manager::getInstance()            
            ->setPaymentTotal('0.00')
            ->setPaymentStatus(RM_Payments_Status::TRANSACTION_END_SUCCESSFULLY);

        $this->_forward('success', 'Payments');
    }
}