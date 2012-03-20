<?php

/**
 * ResMania - Reservation System Framework http://resmania.com
 * Copyright (C) 2011  ResMania Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 *
 * Class for all manipulation with reservation process.
 *
 * This class use singleton pattern, so there will be only one object of this class in the
 * system at once. Also it saves his state into session. We can't use Zend_Session, 'cause
 * there will be a conflict between CMS Session classes and Zend_Session class. Zend_Session::start()
 * class need to be invoked before any other code, but most of CMS have there own session_start method.
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 * @example     1. To get instance of this class:
 * $instance = RM_Reservation_Manager::getInstance();
 *
 * 2. Class automatically saves its internal state after any of the setting method,
 * but you could also use public save() method for this purpose
 */
class RM_Reservation_Manager {

    private $_criteria = null;
    /**
     * Reservation ID. Bookingref.
     * 
     * @var string
     */
    private $_reservationID = null;
    /**
     * Taxes information
     *
     * @var array
     */
    private $_taxes = array();
    /**
     * Reservation details
     *
     * <unit_id> => RM_Reservation_Details
     *
     * @var array
     */
    private $_details = array();
    /**
     * User that logined and creating a reservation
     * @var RM_User_Row
     */
    private $_user = null;
    /**
     * Array with form errors in format
     * <form_name> => array with error codes
     * @var array
     */
    private $_formErrors = array();
    /**
     * Indicates in what status is payment transaction
     *
     * @var int
     */
    private $_paymentTransactionStatus = RM_Payments_Status::NO_TRANSACTION;
    /**
     *
     * @todo we need to think how we could combine this
     * property with @see RM_Reservation_Manager::$_paymentTransactionStatus into one object "Transaction"
     *
     * @var float
     */
    private $_paymentTransactionTotal = null;
    /**
     * @var RM_Reservation_Manager
     */
    private static $_instance = null;
    /**
     * Index string for $_SESSION set/get methods
     * @var string
     */
    private static $_index = 'rm_reservation_manager';

    /**
     * Factory method for getting an object
     *
     * @return RM_Reservation_Manager
     */
    public static function &getInstance() {
        if (self::$_instance === null) {
            if (isset($_SESSION[self::$_index])) {
                self::$_instance = unserialize($_SESSION[self::$_index]);
            } else {
                self::$_instance = new RM_Reservation_Manager();
            }
        }
        return self::$_instance;
    }

    /**
     * Private constructor to implement the singleton pattern
     * If you want to get an object of the class you need to use static method "getInstance"     
     */
    private function __construct() {
        $this->_setReservationID(RM_Reservations::createReservationID());
        $this->save();
    }

    /**
     * Save object (every other setter method, or method that change internal state of an object invoke this method by default)
     */
    public function save() {
        $_SESSION[self::$_index] = serialize($this);
    }

    /**
     * Add additional information for reservation process
     *
     * @param RM_Reservation_Details $details
     * @return RM_Reservation_Manager
     */
    public function addDetails(RM_Reservation_Details $details) {
        $selectedUnit = $details->getUnit(); //don't use getUnit as this returns the saved unit
        $id = $selectedUnit->id;
        $this->_details[$id] = $details;
        $this->save();
        return $this;
    }

    /**
     * Delete reservation details for some unit
     *
     * @param RM_Unit_Row $unit
     * @return RM_Reservation_Manager
     */
    function deleteDetails(RM_Unit_Row $unit) {
        unset($this->_details[$unit->getId()]);
        $this->save();
        return $this;
    }

    /**
     * Reset all details
     *
     * @return RM_Reservation_Manager
     */
    function resetAllDetails() {
        $this->_details = array();
        $this->save();
        return $this;
    }

    /**
     * Return reservation details for one unit
     *
     * @param RM_Unit_Row $unit
     * @return RM_Reservation_Details|null
     */
    function getDetails(RM_Unit_Row $unit) {
        if (isset($this->_details[$unit->getId()]) == false) {
            return null;
        }
        return $this->_details[$unit->getId()];
    }

    /**
     * Returns all details that user select while reservation process
     *
     * @return array Array with objects RM_Reservation_Details
     */
    function getAllDetails() {
        return $this->_details;
    }

    /**
     * Return total amount of price for every reservation unit details
     *
     * @return float
     */
    function getDetailsPrice() {
        $totalPrice = 0;
        $details = $this->getAllDetails();
        foreach ($details as $detail) {

            $subtotal = $detail->getTotal();
            if (!$subtotal['success']) {
                $subtotal = 0;
            }

            $totalPrice += $subtotal;
        }
        return $totalPrice;
    }

    /**
     * Return total amount of days for every reservation details
     *
     * @return int
     */
    function getTotalDays() {
        $totalDays = 0;
        $details = $this->getAllDetails();
        foreach ($details as $detail) {
            $totalDays += $detail->getPeriod()->getLength();
        }
        return $totalDays;
    }

    /**
     * Return total reservation time in seconds. Temp method.
     *
     * @deprecated
     * @return int
     */
    function getTotalTime() {
        $total = 0;
        $details = $this->getAllDetails();
        foreach ($details as $detail) {
            $period = $detail->getPeriod();
            $endTimestamp = $period->getEnd()->toString('U');
            $startTimestamp = $period->getStart()->toString('U');
            $total += $endTimestamp - $startTimestamp;
        }
        return $total;
    }

    /**
     * Set reservation payment status
     *
     * @param int $status one of the constans from class @see RM_Payments_Status
     * @return RM_Reservation_Manager
     */
    public function setPaymentStatus($status) {
        $this->_paymentTransactionStatus = $status;
        $this->save();
        return $this;
    }

    /**
     * Set reservation ID     
     * @param string $reservationID
     * @return RM_Reservation_Manager
     */
    private function _setReservationID($reservationID) {
        $this->_reservationID = $reservationID;
        $this->save();
        return $this;
    }

    /**
     * Returns reservation ID     
     * @return string
     */
    public function getReservationID() {
        return $this->_reservationID;
    }

    /**
     * Reset reservation ID
     * @return RM_Reservation_Manager
     */
    public function resetReservationID() {
        $this->_reservationID = null;
        return $this;
    }

    /**
     * Returns reservation payment status
     *
     * @return int one of the constans of the class @see RM_Payments_Status
     */
    public function getPaymentStatus() {
        return $this->_paymentTransactionStatus;
    }

    /**
     * Set reservation payment total amount
     *
     * @param float $total
     * @return RM_Reservation_Manager
     */
    public function setPaymentTotal($total) {
        $this->_paymentTransactionTotal = $total;
        $this->save();
        return $this;
    }

    /**
     * Reset reservation payment total     
     * @return RM_Reservation_Manager
     */
    public function resetPaymentTotal() {
        $this->_paymentTransactionTotal = null;
        $this->save();
        return $this;
    }

    /**
     * Returns reservation payment total amount
     *
     * @return int one of the constans of the class @see RM_Payments_Status
     */
    public function getPaymentTotal() {
        return $this->_paymentTransactionTotal;
    }

    /**
     * Set all form errors to manager object to store them
     *
     * @param string $formname
     * @param array $errros
     * @return RM_Reservation_Manager
     */
    public function setFormErrors($formname, $errros) {
        $this->_formErrors[$formname] = $errros;
        $this->save();
        return $this;
    }

    /**
     * Reset all form error for the selected form
     *
     * @param string $formname
     * @return RM_Reservation_Manager
     */
    public function resetFormErrors($formname) {
        $this->_formErrors[$formname] = array();
        return $this;
    }

    /**
     * Reset all form error for every form
     *     
     * @return RM_Reservation_Manager
     */
    public function resetAllFormErrors() {
        foreach ($this->_formErrors as $formname => $value) {
            $this->resetFormErrors($formname);
        }
        return $this;
    }

    /**
     * Returns all form errors individual for selected form
     *
     * @param string $formname
     * @return array
     */
    public function getFormErrors($formname) {
        if (isset($this->_formErrors[$formname])) {
            return $this->_formErrors[$formname];
        }
        return array();
    }

    /**
     * Returns all form errors
     *     
     * @return array
     */
    public function getAllFormErrors() {
        return $this->_formErrors;
    }

    /**
     * Set user that made a reservation.
     * Impements fluent interface to be more usefull.
     *
     * @param RM_User_Row $user
     * @return RM_Reservation_Manager
     */
    public function setUser($user) {
        $this->_user = $user;
        $this->save();
        return $this;
    }

    /**
     * Returns user that makes a reservation
     *
     * @return RM_User_Row
     */
    public function getUser() {
        return $this->_user;
    }

    /**
     * Set taxes
     *
     * @param array $taxes - array of taxes in format: tax_id => price
     * @return RM_Reservation_Manager - to implement fluent interface
     */
    public function setTaxes($taxes) {
        $this->_taxes = $taxes;
        return $this;
    }

    /**
     * Reset all taxes information
     *
     * @return RM_Reservation_Manager
     */
    public function resetTaxes() {
        $this->_taxes = array();
        return $this;
    }

    /**
     * Returns taxes
     *
     * @return array
     */
    public function getTaxes() {
        return $this->_taxes;
    }

    /**
     * Sets the search criteria and saves this.
     *
     * @param RM_Unit_Search_Criteria $criteria
     * @return RM_Reservation_Manager
     */
    public function setCriteria(RM_Unit_Search_Criteria $criteria) {
        $this->_criteria = $criteria;
        $this->save();
        return $this;
    }

    /**
     * Returns saved search Criteria
     * this will always return an istance of RM_Unit_Search_Criteria
     * even if it's empty.
     *
     * @return RM_Unit_Search_Criteria     
     */
    public function getCriteria() {
        return $this->_criteria;
    }

    /**
     * Reset criteria
     *
     * @return RM_Reservation_Manager
     */
    public function resetCriteria() {
        $this->_criteria = null;
        $this->save();
        return $this;
    }

    /**
     * Reset user
     */
    public function resetUser() {
        $this->_user = null;
        $this->save();
        return $this;
    }

    /**
     * Reset every information that user select during reservation process.
     *
     * @return RM_Reservation_Manager
     */
    public function reset() {
        $methods = get_class_methods(get_class($this));
        foreach ($methods as $method) {
            if (strpos($method, 'reset') === 0 &&
                    ($method != 'reset') &&
                    ($method != 'resetFormErrors') &&
                    ($method != 'resetUser')) { // We don't need to reset user in the same session
                $this->$method();
            }
        }

        $this->_setReservationID(RM_Reservations::createReservationID());
        return $this;
    }

}