<?php
class RM_User_PayPal_ExternalController extends RM_Controller
{
    public function ipnAction()
    {
        RM_Log::toLog("PayPal ipnAction Called");

        $this->_withoutView();

        $provider = new RM_PayPal_Service();
        $result = $provider->validateIPN();

        RM_Log::toLog("PayPal ipnValidation status: ".$result);
        RM_Log::toLog("PayPal payment status: ".$provider->ipnData['payment_status']);

        if ($result == false){
            return;
        }

        if ($provider->ipnData['payment_status'] == 'Pending') {
            //Pending status is not allowed!!! 'cause it could be for many reason, even for unconfirmed mail address.
            RM_Log::toLog("PayPal pending reason: ".$provider->ipnData['pending_reason']);
        } elseif ($provider->ipnData['payment_status'] == 'Completed') {
            $this->_callback($provider);
        }
    }

    protected function _callback(RM_PayPal_Service $provider)
    {
        $bookingRef = $provider->getBookingRef($provider->ipnData['invoice']);

        $transactionInfo = new RM_Payments_Transaction_Info();
        $transactionInfo->providerName = 'PayPal';
        $transactionInfo->id = $provider->ipnData['txn_id'];
        $transactionInfo->reservationID = $bookingRef;
        $transactionInfo->statusCode = $provider->ipnData['payment_status'];
        $transactionInfo->total = $provider->ipnData['mc_gross'];

        $callbackClassName = $provider->ipnData['custom'];
        $callbackObject = new $callbackClassName;
        $callbackObject->paymentTransactionCallback($transactionInfo);
    }
}
