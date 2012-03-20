<?php
/**
* Admin Prices Controller
*
* This handles all AJAX requests from the Admin GUI Prices Section.
* These methods will create an AJAX response containing JSON data. The JSON
* data is read by the JS code and rendered into interface.
*
* @access 	public
* @author 	Rob, Valentin
* @copyright	ResMania 2009 all rights reserved.
* @version	1.0
* @link		http://developer.resmania.com/api
* @since  	05-2009
*/
class RM_User_PaymentsController extends RM_Controller
{
    public function formAction(){
        $this->_withoutView();

        $paymentPluginName = $this->_getParam('payment', null);
        if ($paymentPluginName == null){
            RM_Reservation_Manager::getInstance()->setFormErrors('summary', array($this->_translate->_('User.Form.Summary.Validation', 'WrongPaymentOption')));
            $this->_redirect('Reservations', 'summary');
        }

        $pluginClassname = 'RM_Plugin_'.$paymentPluginName;
        if (class_exists($pluginClassname) == false) {
            RM_Reservation_Manager::getInstance()->setFormErrors('summary', array($this->_translate->_('User.Form.Summary.Validation', 'WrongPaymentOption').':'.$paymentPluginName));
            $this->_redirect('Reservations', 'summary');
        }

        $plugin = new $pluginClassname;
        if (is_a($plugin, 'RM_Payments_Plugin') == false) {
            $this->_redirect('Reservations', 'summary');
        }

        $pluginController = $paymentPluginName;
        $pluginAction = 'form';
        $this->_forward($pluginAction, $pluginController);
    }

    public function successAction()
    {
        if (RM_Reservation_Manager::getInstance()->getPaymentStatus() !== RM_Payments_Status::TRANSACTION_END_SUCCESSFULLY) {
            $this->_redirect('Reservations', 'notcomplete');
        }
        $this->_redirect('Reservations', 'success');
    }


}