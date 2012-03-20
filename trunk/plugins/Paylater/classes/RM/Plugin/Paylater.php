<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class RM_Plugin_Paylater extends RM_Payments_Plugin
{    
    /**
     * Public constructor
     */
    public function  __construct()
    {
        $this->name = 'Paylater';
    }    

    public function getConfigNode(){
        return null;
    }

    public function getNode(){
        return null;
    }

    public function beginTransaction($value, $reservationID, $description, $successUrl, $cancelUrl, $callbackClassName)    
    {
        $info = new RM_Payments_Transaction_Info();
        $info->id = '0';
        $info->providerName = $this->name;
        $info->statusCode = 'success';
        $info->total = 0;
        $info->reservationID = $reservationID;

        $callbackObject = new $callbackClassName;
        $callbackObject->paymentTransactionCallback($info);
        return;
    }
}