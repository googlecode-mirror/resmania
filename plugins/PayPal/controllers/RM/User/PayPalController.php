<?php
class RM_User_PayPalController extends RM_Controller
{
    public function formAction()
    {
        $reservationDetails = RM_Reservation_Manager::getInstance()->getAllDetails();
        $reservationsModel = new RM_Reservations();
        $unitModel = new RM_Units;
        
        $lang = RM_Environment::getInstance()->getLocale();
      
        //$chargetotal = 0;

        $bookingref = RM_Reservation_Manager::getInstance()->getReservationID();

        // create the description text
        $description =  $bookingref.": ";
        $count = 1;
        foreach ($reservationDetails as $details){
            $unit = $details->getUnit();
            $period = $details->getPeriod();

            $unit_details = $unitModel->get($unit->getId(), $lang);
            $description .= $this->_translate->_('User.PayPal.Main', 'Selection')." ".$count."(".$unit_details->name." (".$unit->getId().") ".$period->getStart()." ".$this->_translate->_('User.PayPal.Main', 'To')." ".$period->getEnd().")";
            $count+=1;
        }

        $plugin = new RM_Plugin_PayPal();
        $chargetotal = $plugin->getTotalPrice($reservationsModel->find($bookingref)->current());       

        RM_Reservation_Manager::getInstance()->setPaymentTotal($chargetotal);

        $provider = new RM_Plugin_PayPal();
        $result = $provider->initialize($description, $bookingref, $chargetotal);
        $this->view->fields = $provider->getFields();
        $this->view->paypal_url = $provider->getPaypalURL();

        // return the json data so that the submit form can be rendered to pass this to paypal
    }

    public function ipnAction(){
        $this->_withoutView();
        $provider = new RM_Plugin_PayPal();
        $provider->validateIPN();

        RM_Log::toLog("PayPal ipnAction Called");

        if ( $provider->ipnData['payment_status'] == "Completed" || $provider->ipnData['payment_status'] == "Pending" ){

            RM_Log::toLog("PayPal payment status: ".$provider->ipnData['payment_status']);

            if (str_word_count($provider->ipnData['invoice'],0,"RM-")>0){
                $bookingref = ltrim($provider->ipnData['invoice'],"RM-");
            } else {
                $bookingref = $provider->ipnData['invoice'];
            }

            RM_Log::toLog("Booking ref passed back from PayPal: ".$bookingref);

            $model = new RM_Reservations();
            $reservation = $model->find($bookingref)->current();
            RM_Log::toLog("Reservation Record ID: ".$reservation->id);

            $model->confirm($reservation);
            RM_Log::toLog("Confirmed Updated");

            $model->inProgressComplete($reservation);
            RM_Log::toLog("InProgress Marker Updated");

            // save the total

            // we have a problem here, when this action is called it is called
            // from the paypal.com server. The session is not the same so saving to it
            // is impossible. I think we need to pass to PayPal the 'custom' parameter
            // this could contain something like the session id, however I am not
            // sure if it's possible to update the data in a session with a session
            // id?

            //$manager = RM_Reservation_Manager::getInstance();

            $total_paid = $provider->ipnData['mc_gross'];

            RM_Log::toLog("Total Passed back from PayPal: ".$total_paid);

            //$manager->setPaymentTotal($total_paid); // save the total incase we need it later.

            //if (!$total_paid) $this->_redirect('Reservations', 'notcomplete'); // this handles if the total amount is null'd

            $billingModel = new RM_Billing();
            $billingRow = $billingModel->createRow();
            $billingRow->reservation_id = $bookingref;
            $billingRow->total_paid = $total_paid;
            $billingID = $billingRow->save();

            RM_Log::toLog("Billing Updated");

            // TODO: I can't get thsi working..

            // save the payment information
            $billingPaymentsModel = new RM_BillingPayments();
            $billingPaymentRow = $billingPaymentsModel->createRow();
            $billingPaymentRow->id = $billingID;
            $billingPaymentRow->provider = "PayPal";
            $billingPaymentRow->transaction_id = $provider->ipnData['txn_id'];
            $billingPaymentRow->status = $provider->ipnData['payment_status'];
            $billingPaymentRow->total = $provider->ipnData['mc_gross'];
            $billingPaymentRow->transaction_date = Date("Y-m-d");
            $billingPaymentRow->save();

            RM_Log::toLog("Billing Payments Updated");

            // TODO: if the IPN is successful then we need to set the in_progress flag to 0
            // this is also done in the reservation controller on success we have to do it here also
            // as it is possible the customer may not return to the site via the payment provider
            // return URL in this case the in_progress flag will never be updated that's why we
            // need to do this here also
            // I need to ask Valentin about a standard method for this.


            // TODO: we should also record other information such as the transaction id
            // however this can be added later.
        }
    }

    public function cancelAction(){
        //We need to reset user object accorting to ticket #59 in Trac
        RM_Reservation_Manager::getInstance()->resetUser();

        // return a cancelled message
        return array(
            'data' => array('success' => true)
        );
    }

    public function successAction(){

        // we must mark the reservation as completed successfully.
        // however the payment is not truely passed until the ipn is passed back
        // but this is asyncronus, so we have to set a success state here to the
        // user is directed to the final post booking page
    
        RM_Reservation_Manager::getInstance()->setPaymentStatus(RM_Payments_Status::TRANSACTION_END_SUCCESSFULLY);
        $this->_forward('success', 'Payments');
    }
}