<?php
class RM_Payments_Plugin extends RM_Plugin
{
    /**
     * This method internally return total reservation due for payment
     * There could be a deposit system installed and enabled
     *
     * @deprecated
     * @param RM_Reservation_Row $reservation
     * @return float
     */
    public function getTotalPrice(RM_Reservation_Row $reservation){
        $totalPrice = $reservation->getTotalPrice();

        $depositSystem = RM_Environment::getInstance()->getDepositSystem();
        if ($depositSystem == null) {
            return $totalPrice;
        }

        if ($depositSystem->isDepositUsedFor($reservation->id)) {
            return $depositSystem->calculate($totalPrice);
        }

        return $totalPrice;
    }

    /**
     * This method will begin transaction. This method should be overloaded by child classes.
     * The only one reason of not make this method abstract is to prevent fatal errors.
     *
     * @param float $value - value for the transaction
     * @param string $reservationID
     * @param string $description - just a text to identify transaction
     * @param string $successUrl - redirect to url when user successfully processed payment, BUT payment is not acctually saved to database
     * @param string $cancelUrl - redirect to url when user cancel payment or have not enough money or there was
     * something else that prevent to failed payment process
     * @param string $callbackClassName - name of the class that should be used as a callback, should implement RM_Payments_Transaction_Callback
     * @throw RM_Payments_Transaction_Exception
     * @return void
     */
    public function beginTransaction($value, $reservationID, $description, $successUrl, $cancelUrl, $callbackClassName){}
}