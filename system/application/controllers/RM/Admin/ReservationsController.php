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
 * Admin Reservation Controller
 *
 * This handles all AJAX requests from the Admin GUI Reservations Section.
 * These methods will create an AJAX response containing JSON data. The JSON
 * data is read by the JS code and rendered into interface.
 *
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since       5-2011
 */
class RM_Admin_ReservationsController extends RM_Controller {

    /**
     * Method for manual adding payment from the Admin end.
     * All params are from request.
     *
     * @param reservation_id
     * @param transaction_id - default 'ADMIN'
     * @param provider - default curent server date
     * @param total - default 0 
     * @return array
     */
    public function addpaymentJsonAction() {
        $reservationID = $this->_getParam('reservation_id');
        if ($reservationID == null) {
            return array(
                'data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()
                            ->getTranslation(RM_Environment::TRANSLATE_ERRORS)
                            ->_('Admin.Reservation.Edit', 'ReservationIDMissed')
                )
            );
        }

        $model = new RM_Reservations();
        $reservation = $model->find($reservationID)->current();
        if ($reservation == null) {
            return array(
                'data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()
                            ->getTranslation(RM_Environment::TRANSLATE_ERRORS)
                            ->_('Admin.Reservation.Edit', 'WrongReservationID')
                )
            );
        }

        $transactionDate = $this->_getParam('transaction_date');
        $transactionID = $this->_getParam('transaction_id', '');
        if ($transactionID == '') {
            $transactionID = 0;
        }
        $providerName = $this->_getParam('provider', '');
        if ($providerName == '') {
            $providerName = 'Administrator';
        }
        $totalPaid = $this->_getParam('total');

        $billingModel = new RM_Billing();
        $billingRow = $billingModel->createRow();
        $billingRow->reservation_id = $reservationID;
        $billingRow->total_paid = $totalPaid;
        $billingID = $billingRow->save();

        $billingPaymentsModel = new RM_BillingPayments();
        $billingPaymentRow = $billingPaymentsModel->createRow();
        $billingPaymentRow->id = $billingID;
        $billingPaymentRow->provider = $providerName;
        $billingPaymentRow->transaction_id = $transactionID;
        $billingPaymentRow->status = 'SUCCESS';
        $billingPaymentRow->total = $totalPaid;
        $billingPaymentRow->transaction_date = $transactionDate;
        $billingPaymentRow->save();

        return array(
            'data' => array(
                'success' => true
            )
        );
    }

    /**
     * this function deletes a payment entry
     */
     public function deletepaymentJsonAction(){

        $reservationID = $this->_getParam('reservation_id');
        $totalPaid = $this->_getParam('total_paid');

        $currencyChars = preg_replace('#[0-9 ]*#', '', $totalPaid);

        $totalPaid = trim($totalPaid,$currencyChars);

        $billingModel = new RM_Billing();
        $deleteResults = $billingModel->delete("reservation_id='".$reservationID."' AND total_paid='".$totalPaid."'");

         return array(
            'data' => array(
                'success' => $deleteResults
            )
        );
     }

    /**
     * this function returns the saved reservation price
     *
     * @return array/json
     */
    public function getsavedpricesJsonAction(){
        $reservationID = $this->_getParam('reservation_id');
        if ($reservationID == null) {
            return array(
                'data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()
                            ->getTranslation(RM_Environment::TRANSLATE_ERRORS)
                            ->_('Admin.Reservation.Edit', 'ReservationIDMissed')
                )
            );
        }
        $billingModel = new RM_Billing();
        $getSavedPrice = $billingModel->getPrice($reservationID);

        return array(
            'data' => $getSavedPrice,
            'encoded' => false
        );
    }

    /**
     * this saves the reservation total amount
     */
    public function edittotalJsonAction(){
        $reservationID = $this->_getParam('reservation_id');
        if ($reservationID == null) {
            return array(
                'data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()
                            ->getTranslation(RM_Environment::TRANSLATE_ERRORS)
                            ->_('Admin.Reservation.Edit', 'ReservationIDMissed')
                )
            );
        }

        $total = $this->_getParam('rm_reservation_edit_edit_total');

        $reservationModel = new RM_Reservations();
        $reservationDetailsModel = new RM_ReservationDetails();

        $reservation = $reservationModel->find($reservationID)->current();
        $reservationDetails = $reservationDetailsModel->getAllByReservation($reservation)->current();
        $reservationDetails->total_price = $total;
        $success = ($reservationDetails->save() !=0) ? true : false;

        return array(
            'data' => array("success"=>$success),
            'encoded' => false
        );
    }

    public function printinvoiceJsonAction() {
        $reservationID = $this->_getParam('reservation_id');
        if ($reservationID == null) {
            return array(
                'data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()
                            ->getTranslation(RM_Environment::TRANSLATE_ERRORS)
                            ->_('Admin.Reservation.Edit', 'ReservationIDMissed')
                )
            );
        }

        $model = new RM_Reservations();
        $reservation = $model->find($reservationID)->current();
        if ($reservation == null) {
            return array(
                'data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()
                            ->getTranslation(RM_Environment::TRANSLATE_ERRORS)
                            ->_('Admin.Reservation.Edit', 'WrongReservationID')
                )
            );
        }

        $data = RM_Reservations::getInvoice($reservation);

        return array(
            'data' => $data,
            'encoded' => true
        );
    }

    public function emailinvoiceJsonAction() {
        $reservationID = $this->_getParam('reservation_id');
        if ($reservationID == null) {
            return array(
                'data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()
                            ->getTranslation(RM_Environment::TRANSLATE_ERRORS)
                            ->_('Admin.Reservation.Edit', 'ReservationIDMissed')
                )
            );
        }

        $model = new RM_Reservations();
        $reservation = $model->find($reservationID)->current();
        if ($reservation == null) {
            return array(
                'data' => array(
                    'success' => false,
                    'error' => RM_Environment::getInstance()
                            ->getTranslation(RM_Environment::TRANSLATE_ERRORS)
                            ->_('Admin.Reservation.Edit', 'WrongReservationID')
                )
            );
        }

        $emailBody = RM_Reservations::getInvoice($reservation);

        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyHtml($emailBody);

        $config = new RM_Config();
        $mail->setFrom($config->getValue('rm_config_administrator_email'), $config->getValue('rm_config_email_settings_fromname'));

        $confirm = $this->_getParam('rm_reservation_edit_send_invoice_confirm', 0);
        if ($confirm) {
            $userModel = new RM_Users();
            $user = $userModel->getByReservation($reservation);
            if ($user !== null) {
                $mail->addTo($user->email, $user->first_name . ' ' . $user->last_name);
            }
        }

        $extraAddresses = $this->_getParam('rm_reservation_edit_send_invoice_email', '');
        $extraAddresses = explode(',', $extraAddresses); //Here could be a multiple emails to send invoice to
        foreach ($extraAddresses as $address) {
            $mail->addTo($address);
        }

        $mail->setSubject(RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN)->_('Admin.Reservations.Edit', 'InvoiceForReservation') . $reservation->id);

        try {
            $mail->send();
            return array('data' => array('success' => true));
        } catch (Exception $e) {
            return array('data' => array('success' => false, 'msg' => $e->getMessage()));
        }
    }

    /**
     * Deletes Reservation Details (Assigned Units) from a Reservation.
     *
     * Used to delete assigned units from a reservation. The units assigned to a
     * reservation are stored in the details tables. This method will delete the
     * selected units based on the reservation id.
     *
     * @param  	request id  the id of the reservation.
     * @param  	request ids  an array of the Unit ID's to be deleted..
     * @return 	json    boolean response of true or false in json format (true is success)
     */
    public function deletedetailJsonAction() {
        $reservationID = $this->_getParam('reservation_id');
        if ($reservationID == null) {
            return array('data' => array('success' => false));
        }
        $unitID = $this->_getParam('unit_id');
        if ($unitID == null) {
            return array('data' => array('success' => false));
        }

        $model = new RM_ReservationDetails;
        $detail = $model->find($reservationID, $unitID)->current();
        if ($detail == null) {
            return array('data' => array('success' => false));
        }

        $result = $detail->delete();

        $model = new RM_Reservations();
        $reservation = $model->find($reservationID)->current();
        $reservation->recalculate();

        if ($result == 1) {
            return array('data' => array('success' => true));
        } else {
            return array('data' => array('success' => false));
        }
    }

    /**
     * Returns the list configuration for reservations
     *
     * This creates a JS variable containing the list configuration/setup for
     * the reservation list. This is implicated in list.js
     *
     * @return   js variable used directly by list.js
     */
    public function listJsAction() {
        $fieldsDAO = new RM_ReservationConfig();
        $configFields = $fieldsDAO->getAdminList()->toArray();

        foreach ($configFields as $key => $configField) {
            $metainfo[] = $configField['admin_list_preferences'];
        }

        return "RM.Common.Reservations_List_Setup([" . implode(',', $metainfo) . "]);";
    }

    /**
     * Returns data for the Reservation List.
     *
     * This method returns the list data in JSON format. This is implicated in
     * lists.js
     *
     * @param   request start   list start used for pagenation
     * @param   request limit   list limit used for pagenation
     * @param   request sort    list sort parameter
     * @param   request dir list direction Asc/desc
     * @param   request filter  an array of any filters applied
     * @return   json    the json data for the list.
     */
    public function listJsonAction() {
        $offset = $this->_getParam('start');
        $count = $this->_getParam('limit');
        $sort = $this->_getParam('sort', 'reservation_id');
        $direction = $this->_getParam('dir', 'DESC');
        $filters = $this->_getParam('filter', array());

        $order = $sort . ' ' . $direction;
        $dao = new RM_ReservationDetails();

        $total = $dao->getAll($order, null, null, $filters)->count();
        $reservations = $dao->getAll($order, $count, $offset, $filters)->toArray();

        $count = 0;
        $model = new RM_Reservations();
        foreach ($reservations as $reservation) {
            $reservation = $model->find($reservation)->current();
            if ($reservation->isPaid()) {
                $reservations[$count]['is_paid'] = true;
            } else {
                $reservations[$count]['is_paid'] = false;
            }
            $count +=1;
        }

        $json = new stdClass;
        $json->total = $total;
        $json->data = $reservations;

        return array(
            'data' => $json
        );
    }

    /**
     * Confirm Reservation
     *
     * Sets the selected reservation to confirmed. Once confirmed reservations
     * for the selected units will not be allowed on the reservations for
     * the assigned/selected dates. (blocks the dates from the calendar)
     *
     * @param   request ids  an array containing selected reservation id's
     * @return   json    boolean response in json format (true = success)
     */
    public function confirmJsonAction() {
        $ids = $this->_getParam('ids', array());
        $model = new RM_Reservations();

        foreach ($ids as $id) {
            $reservation = $model->find($id)->current();
            if ($reservation == null)
                continue;
            $model->confirm($reservation);
        }

        return array(
            'data' => array('success' => true)
        );
    }

    /**
     * UnConfirm Reservation
     *
     * Sets the selected reservation to un-confirmed. When a reservation
     * is confirmed for selected units it can be unconfirmed with this method
     * allowed other reservations to be accepted on the same selected dates.
     *
     * @param   request ids  an array containing selected reservation id's
     * @return   json    boolean response in json format (true = success)
     */
    public function unconfirmJsonAction() {
        $ids = $this->_getParam('ids', array());
        $model = new RM_Reservations();

        foreach ($ids as $id) {
            $reservation = $model->find($id)->current();
            if ($reservation == null)
                continue;
            $model->unconfirm($reservation);
        }

        return array(
            'data' => array('success' => true)
        );
    }

    /**
     * Delete Reservation
     *
     * Deletes the selected reservations by id. Will remove all connected entrys
     * for the selected reservation from the reservations and reservation details
     * tables.
     *
     * @param   request ids  an array containing selected reservation id's
     * @return   json    boolean response in json format (true = success)
     */
    public function deleteJsonAction() {
        $ids = $this->_getParam('ids', array());
        $model = new RM_Reservations();

        foreach ($ids as $id) {
            $reservation = $model->find($id)->current();
            if ($reservation == null)
                continue;
            $reservation->delete();
        }

        return array(
            'data' => array('success' => true)
        );
    }

    /**
     * Creates the Calendar Data (Free/Busy Time)
     *
     * This creates the blocked and reserved period data for use by the calendar.
     * It uses data from the reservations table and creates a JSON string
     * containing the data. This data is read by the calendar JS and plotted
     * accordingly.
     *
     * This function handles new and existing reservations, new reservations do
     * not exist in the reservations tables so we have to build the selected
     * information, such as the id, start and end dates.
     *
     * @deprecated
     * @param   request type indicates if this is a new or existing reservation.
     * @param   request id the reservation id.
     * @param   request start_datetime   the selected reservation start date
     * @param   request end_datetime   the selected reservation end date
     * @return   json    returns json array information containing reserved_periods and blocked_periods
     */
    public function calendarJsonAction() {
        $type = $this->_getParam('type', '');

        $model = new RM_Reservations();
        $config = new RM_Config();

        // if the type = new then there will be no id in the live db to check
        if ($type == "new") {
            // build the reservation info...
            $reservation->id = $this->_getParam('id', '');
            $reservation->start_datetime = $this->_getParam('start_datetime', '');
            $reservation->end_datetime = $this->_getParam('end_datetime', '');
        } else {
            $id = $this->_getParam('id', '');
            $reservation = $model->find($id)->current();
        }

        $reservations = $model->fetchAllLinkedByUnit($reservation);
        $jsonDisabledPeriods = array();
        foreach ($reservations as $period) {
            if ($period->id == $reservation->id)
                continue;

            $jsonPeriod = new stdClass;
            $jsonPeriod->start_date = $config->convertDates($period->start_datetime, RM_Config::JS_DATEFORMAT, RM_Config::PHP_DATEFORMAT);
            $jsonPeriod->end_date = $config->convertDates($period->end_datetime, RM_Config::JS_DATEFORMAT, RM_Config::PHP_DATEFORMAT);
            $jsonDisabledPeriods[] = clone $jsonPeriod;
        }

        $jsonReservation = new stdClass;
        $jsonReservation->start_date = $config->convertDates($reservation->start_datetime, RM_Config::JS_DATEFORMAT, RM_Config::PHP_DATEFORMAT);
        $jsonReservation->end_date = $config->convertDates($reservation->end_datetime, RM_Config::JS_DATEFORMAT, RM_Config::PHP_DATEFORMAT);
        $jsonReservations[] = $jsonReservation;

        $json = "{
            reserved_periods: " . Zend_Json::encode($jsonReservations) . ",
            blocked_periods: " . Zend_Json::encode($jsonDisabledPeriods) . "
        }";

        return array(
            'data' => $json,
            'encoded' => true
        );
    }

    /**
     * Return blocked periods for a unit except one current reservation
     */
    public function getreservedperiodsJsonAction() {
        $untiID = $this->_getParam('unit_id', null);
        if ($untiID == null) {
            return array('data' => array('success' => false));
        }
        $unitModel = new RM_Units();
        $unit = $unitModel->find($untiID)->current();
        if ($unit == null) {
            return array('data' => array('success' => false));
        }

        $reservationID = $this->_getParam('reservation_id', null);
        if ($reservationID == null) {
            return array('data' => array('success' => false));
        }
        $reservationModel = new RM_Reservations();
        $reservation = $reservationModel->find($reservationID)->current();
        $reservations = array();
        if ($reservation !== null) {
            $reservations[] = $reservation;
        }

        $reservationDetailsModel = new RM_ReservationDetails();
        $unitReservationDetails = $reservationDetailsModel->findByUnit($unit, $reservations);

        $jsonDisabledPeriods = array();
        foreach ($unitReservationDetails as $unitReservationDetail) {
            $jsonDisabledPeriods[] = "{
                start_date: '" . $unitReservationDetail->getStartDatetime(RM_Config::PHP_DATEFORMAT) . "',
                end_date: '" . $unitReservationDetail->getEndDatetime(RM_Config::PHP_DATEFORMAT) . "'
            }";
        }

        $priceSystem = RM_Prices_Manager::getInstance()->getRealPriceSystem($unit);
        $showTimeFields = (int) $priceSystem->getShowTime($unit);

        return array(
            'data' => "{
                blocked_periods: [" . implode(',', $jsonDisabledPeriods) . "],
                pricesystem: '" . $priceSystem->name . "',
                showtime: " . $showTimeFields . ",
            }",
            'encoded' => true
        );
    }

    /**
     * Edit Reservation sets up the enviroment for editing a reservation and loads data.
     *
     * Creates temporary entries for the temp reservation data, loads the form
     * configuration, data for users, calendar information. This method is the PHP
     * code behind the edit.js function. All initial data loaded onto this form
     * is controlled by this method.
     *
     * @param   request id the reservation id.
     * @return 	json    json array information.
     */
    public function editJsonAction() {

        $reservationID = $this->_getParam('id', null);

        if ($reservationID == null) {
            return array('data' => array('success' => false));
        }

        $reservationModel = new RM_Reservations();
        $reservation = $reservationModel->find($reservationID)->current();
        if ($reservation == null) {
            return array('data' => array('success' => false));
        }

        $details = $reservation->findReservationDetails();

        $billing = new RM_Billing();
        $reservationDetailsModel = new RM_ReservationDetails();

        $jsonDetails = array();
        $savedpriceData = array();
        $config = new RM_Config();

        $summaryModel = new RM_ReservationSummary();

        foreach ($details as $detail) {
            $unit = $detail->findUnit();
            if ($unit == null)
                continue;

            $priceSystem = RM_Prices_Manager::getInstance()->getRealPriceSystem($unit);
            $showTimeFields = (int) $priceSystem->getShowTime($unit);

            $unitReservationDetails = $reservationDetailsModel->findByUnit($unit, array($reservation));
            $jsonDisabledPeriods = array();
            // TODO: Need to check if this required
            // not sure why we need this now...
            // each reservation is 1 reservation per unit
            // so why do we need a loop to iterate all reservations.
            //valentin: we need to show every reserved period for this unit by other reservations
            foreach ($unitReservationDetails as $unitReservationDetail) {
                $jsonDisabledPeriods[] = "{
                    start_date: '" . $unitReservationDetail->getStartDatetime($priceSystem->getDateformat(true)) . "',
                    end_date: '" . $unitReservationDetail->getEndDatetime($priceSystem->getDateformat(true)) . "',
                }";
            }

            // other information that is price system specific ie. board_type
            $otherInfo = $priceSystem->getOtherInfo();
            foreach ($otherInfo as $otherName) {
                $othersJson .= $otherName . ": '" . $detail->$otherName . "',";
            }

            // get the other systems
            $otherSystems = RM_Environment::getInstance()->getOthersSystems();
            foreach ($otherSystems as $otherSystem) {
                $oSystems[] = "'" . $otherSystem->name . "'";
            }

            $jsonDetails[] = "{
                unit: {
                    id: '" . $unit->getId() . "',
                    name: '" . addslashes($unit->name) . "',
                },
                reserved_period: {
                    start_date: '" . $detail->getStartDatetime($priceSystem->getDateformat(true)) . "',
                    end_date: '" . $detail->getEndDatetime($priceSystem->getDateformat(true)) . "'
                },
                persons: {
                    adults: '" . $detail->adults . "',
                    children: '" . $detail->children . "',
                    infants: '" . $detail->infants . "',
                },
                othersystems: [" . implode(",", $oSystems) . "],
                pricesystem: '" . $priceSystem->name . "',
                showtime: " . $showTimeFields . ",
                " . $othersJson . "
                blocked_periods: [" . implode(',', $jsonDisabledPeriods) . "]
              }";


            $config = new RM_Config();
            $currencySymbol = $config->getValue('rm_config_currency_symbol');

            $summaryRows = $summaryModel->fetchByReservationDetail($detail)->toArray();
            for ($i = 0; $i < count($summaryRows); $i++) {
                $summaryRows[$i]['total_amount'] = $currencySymbol . RM_Environment::getInstance()->roundPrice($summaryRows[$i]['total_amount']);
            }

            $savedpriceData[] = "{
                unit: {
                    id: '" . $unit->getId() . "',
                    name: '" . addslashes($unit->name) . "',
                },
                total_sub: '" . $currencySymbol . RM_Environment::getInstance()->roundPrice($detail->total_price) . "',
                reserved_period: {
                    start_date: '" . $detail->getStartDatetime() . "',
                    end_date: '" . $detail->getEndDatetime() . "'
                },
                summary_rows: " . Zend_Json::encode($summaryRows) . ",
                showtime: " . $showTimeFields . "
             }
             ";
        }

        $payments = $billing->getPayments($reservation);

        $paymentsData = array();
        foreach ($payments as $payment) {
            $paymentsData[] = "{
                date: '" . $payment->transaction_date . "',
                provider: '" . $payment->provider . "',
                transactionid: '" . $payment->transaction_id . "',
                status: '" . $payment->status . "',
                total_paid: '" . $currencySymbol . RM_Environment::getInstance()->roundPrice($payment->total_paid) . "'
            }";
        }

        $reservationConfigModel = new RM_ReservationConfig();
        $fields = $reservationConfigModel->getAdminEditFields();
        $jsonFields = array();
        foreach ($fields as $field) {
            $jsonFields[] = $field->admin_view_edit;
        }

        $reservationSummaryRows = $summaryModel->fetchByReservation($reservation)->toArray();
        for ($i = 0; $i < count($reservationSummaryRows); $i++) {
            $reservationSummaryRows[$i]['total_amount'] = $currencySymbol . RM_Environment::getInstance()->roundPrice($reservationSummaryRows[$i]['total_amount']);
        }

        // reservation price information                       
        $user = $reservation->findParentUsers();
        $json = "{
            data : " . Zend_Json::encode($reservation->toArray()) . ",
            fields : [" . implode(',', $jsonFields) . "],
            user: {
                id : '" . $user->id . "',
                last_name : '" . addslashes($user->last_name) . "',
                first_name : '" . addslashes($user->first_name) . "',
            },
            details: [
                " . implode(',', $jsonDetails) . "
            ],
            pricedata: {
                prices: [" . implode(',', $savedpriceData) . "],
                reservation_summary_rows: " . Zend_Json::encode($reservationSummaryRows) . ",
                reservationTotal: '" . $currencySymbol . RM_Environment::getInstance()->roundPrice($reservation->getTotalPrice()) . "'
            },
            paymentdata: {
                payments: [" . implode(',', $paymentsData) . "]
            }
        }";

        // set the read marker
        $reservation->is_read = 1;
        $reservation->save();

        return array(
            'data' => $json,
            'encoded' => true
        );
    }

    public function unitselectionJsonAction() {
        $reservationConfigModel = new RM_ReservationConfig();
        $fields = $reservationConfigModel->getFields();
        foreach ($fields as $field) {
            $jsonFields[] = $field->admin_view_wizard;
        }

        $json = "{        
            fields : [" . implode(',', $jsonFields) . "]
        }";

        return array(
            'data' => $json,
            'encoded' => true
        );
    }

    /*
     * Returns the JSON for our basic GANTT chart allowing a period to be viewed
     * and days to be selected
     *
     * @deprecated since RC2
     */

    public function getganttJsonAction() {
        $ids = explode(",", $this->_getParam('ids')); // id's of units
        $startdatetime = new RM_Date(strtotime($this->_getParam('start_datetime', date(RM_Config::MYSQL_DATEFORMAT_SHORT))));
        $enddatetime = new RM_Date(strtotime($this->_getParam('end_datetime', date(RM_Config::MYSQL_DATEFORMAT_SHORT))));

        // 1 get all the days between the start and end period
        $RM_PeriodObj = new RM_Reservation_Period($startdatetime, $enddatetime);
        $days = $RM_PeriodObj->getDays();
        $numberofDays = count($days);

        // 2 cycle through each unit specified and check if the date is reserved or not
        $unitModel = new RM_Units();
        $reservationModel = new RM_Reservations;

        $daycount = 0;
        $unitcount = 0;
        $colspan = 0;

        $dateInfo = Array();
        $monthInfo = Array();

        $years = new stdClass;
        $years->startyear = $days[0]->toString("Y"); // set the start year

        foreach ($ids as $id) {
            $unit = $unitModel->get($id);

            $unitInfo = new stdClass;
            $unitInfo->id = $id;
            $unitInfo->unitname = $unit->name;

            $reservationData = array();
            foreach ($days as $day) {
                $dayInfo = new stdClass;
                $dayInfo->id = $id;
                $dayInfo->date = $day->toString(RM_Config::MYSQL_DATEFORMAT_SHORT);
                $dayInfo->reserved = $reservationModel->fetchAllByUnitDate(
                                $id,
                                $day->toString(RM_Config::MYSQL_DATEFORMAT_SHORT)
                        )->count();
                $reservationData[] = $dayInfo; // this holds each of the reservation status for each cell.

                if ($unitcount == 0) {
                    // this information is only collected on the first pass of this loop
                    $dates = new stdClass;
                    $months = new stdClass;
                    $dates->day = $day->toString("d");

                    if (!$lastMonth) {
                        $lastMonth = $day->toString("F"); //set the starting value
                    }
                    $months->month = $lastMonth;
                    $months->colspan = $colspan + 1;

                    if ($day->toString("F") != $lastMonth || $daycount >= $numberofDays - 1) {
                        $colspan = 0;
                        $monthInfo[] = clone($months);
                    } else {
                        $colspan++;
                    }

                    $lastMonth = $day->toString("F");
                    $dateInfo[] = $dates;
                }
                $years->endyear = $day->toString("Y");
                $daycount++; // this will count to the same value as $numberofDays
            }
            $unitcount++; // this will increment for each unit

            $unitInfo->days = $reservationData;
            $unitData[] = $unitInfo; // this is a holding array based on each unit.
        }

        // set the years colspan... the years are just rendered twice start and end years at the top of th page.
        // or once if there is only one year
        if ($years->endyear == $years->startyear) {
            $years->colspan = $numberofDays;
        } else {
            $years->colspan = $numberofDays / 2;
        }

        $json = "{
            years: " . Zend_Json::encode($years) . ",
            months: " . Zend_Json::encode($monthInfo) . ",
            days: " . Zend_Json::encode($dateInfo) . ",
            data: " . Zend_Json::encode($unitData) . "
        }";

        return array(
            'data' => $json,
            'encoded' => true
        );
    }

    /**
     * Creates the new reservation in the reservation temp tables and the json data for the form
     *
     * This generates a new reservation, a new reservation does not use the id value, but the reservation_id.
     * the reservation_id is created at the time this method is called and is not committed to the live
     * reservations (stored in rm_reservations) until save.
     *
     * @return 	json
     * @deprecated since RC2
     */
    public function wizardnewJsonAction() {
        $data = new stdClass;
        $data->id = RM_Reservations::createReservationID();
        $data->user_id = null; // this isn't available until the reservation is saved.
        $data->start_datetime = date("Y-m-d");
        $data->end_datetime = date("Y-m-d");

        // this is the form fields...
        $reservationConfigModel = new RM_ReservationConfig();
        $fields = $reservationConfigModel->getWizardFields();
        foreach ($fields as $field) {
            $jsonFields[] = $field->admin_view_wizard;
        }

        $json = "{
            data : " . Zend_Json::encode($data) . ",
            fields : [" . implode(',', $jsonFields) . "]
            }";

        return array(
            'data' => $json,
            'encoded' => true
        );
    }

    /**
     * Invoked from the New Reservation Wizard form via an AJAX request (wizard.js) and is used to insert the reservation.
     *
     * @param  	request array   All of the reservation data passed via the form submit.
     * @return 	json    boolean (true if success)
     * @deprecated
     */
    public function wizardinsertJsonAction() {
        // all parameters from the new wizard...
        $ids = $this->_getParam('ids');
        $ref = $this->_getParam('bookingref');
        $userid = $this->_getParam('userid');
        $startdatetime = $this->_getParam('start_datetime');
        $enddatetime = $this->_getParam('end_datetime');

        $config = new RM_Config();
        $startDate = $config->convertDates($startdatetime, RM_Config::JS_DATEFORMAT, RM_Config::PHP_DATEFORMAT);
        $endDate = $config->convertDates($enddatetime, RM_Config::JS_DATEFORMAT, RM_Config::PHP_DATEFORMAT);
        $period = new RM_Reservation_Period(
                        new RM_Date(strtotime($startDate)),
                        new RM_Date(strtotime($endDate))
        );

        $unitModel = new RM_Units();

        $unitDetails = array();
        $uids = explode(",", $ids);
        foreach ($uids as $uid) {
            $unitDetails[] = new RM_Reservation_Details(
                            $unitModel->get($uid),
                            $period,
                            new RM_Reservation_Persons()
            );
        }

        $userModel = new RM_Users();
        $user = $userModel->find($userid)->current();

        $reservationModel = new RM_Reservations();
        $reservationModel->insertNewReservation($user, $unitDetails, 0, 1, $ref);

        return array(
            'data' => array('success' => true)
        );
    }

    /**
     * Invoked from Edit Reservations form via an AJAX request (edit.js) and is used to Update the Reservation
     *
     * This copies the data from the temp tables back to the live table and destroys the temp reservation
     * The reason we have a temp table for reservations and reservation details is that the we need to store
     * the ajax requests temporarilly until the user is ready to save and commit the reservation. If they
     * abandon the reservation then the temp data is either re-used when the reservation is re-edited or
     * refreshed. This allows some freedom to the user to change the reservation but to also cancel out if
     * they do not want to commit changes.
     *
     * @param  	request id  the reservation is linked by the id of the reservation (not the reference_id, this is just for the admins/users information)
     * @return 	json    boolean (true if success)
     */
    public function updateJsonAction() {
        $reservationJson = Zend_Json::decode($this->_getParam('reservation', "[]"));
        if (count($reservationJson) == 0) {
            return array('data' => array('success' => false));
        }

        $reservationModel = new RM_Reservations();
        $reservation = $reservationModel->find($reservationJson['id'])->current();
        if ($reservation == null) {
            return array('data' => array('success' => false));
        }

        foreach ($reservationJson as $fieldName => $value) {
            $reservation->$fieldName = $value;
        }
        $reservation->save();

        $reservationDetailsModel = new RM_ReservationDetails();

        //Delete all disabled reservation details
        $deletedUnitIDs = Zend_Json::decode($this->_getParam('deleted_unit_ids', "[]"));
        $unitModel = new RM_Units;
        foreach ($deletedUnitIDs as $unitID) {
            $unit = $unitModel->find($unitID)->current();
            if ($unit == null)
                continue;

            $details = $reservationDetailsModel->fetchAllBy($unit, $reservation);
            foreach ($details as $detail) {
                $detail->delete();
            }
        }

        $detailsJson = Zend_Json::decode($this->_getParam('details', "[]"));
        foreach ($detailsJson as $detailJson) {
            if (in_array($detailJson['unit_id'], $deletedUnitIDs))
                continue;

            $unit = $unitModel->find($detailJson['unit_id'])->current();

            $detail = $reservationDetailsModel->fetchAllBy(
                            $unit,
                            $reservation
                    )->current();


            /**
             * Other info management. This code allows other information to be handled
             * by the price system. Edit.js on save will pass through an array of
             * Other Info items these must be decoded and must replace the 'otherinfo'
             * index in the detail array.
             *
             * otherinfo is defined in the edit.js in the return param's however this
             * is just a generic placeholder to pass the data as this could be anything
             *
             * for example if need to pass board_type which is used by the hospitality
             * then we pass this on the otherinfo param as a json array. leaving the otherinfo
             * index in the array will cause the update to fail as this column will not
             * be found. So we read in the json array then add the keys for the
             * other items then finally remove the other info index.
             *
             * ie: if board_type = 'hb' and extra_bed = '1' are passed these would be
             * in the otherinfo json and will be added to the detail array then the
             * otherinfo value will be removed.
             */
            $otherInfo = Zend_Json::decode($detailJson['otherinfo']); // zend json decode puts the array inside another array so we have to return i0.
            if (!empty($otherInfo)) {
                foreach ($otherInfo[0] as $key => $value) {
                    $detailJson[$key] = $value;
                    unset($detailJson['otherinfo']);
                }
            } else {
                // if it's not passed remove it as some
                // price systems my not have this row
                unset($detailJson['otherinfo']);
            }

            // others systems processing (not to be confused with otherInfo
            $otherPriceTotal = 0;
            $othersSystems = Zend_Json::decode($this->_getParam('other_systems', "[]"));
            foreach ($othersSystems as $system) {
                $systemName = $system['systemClassName']; // this will be the module/plugin class for updating
                $systemClassName = "RM_Module_" . $systemName;
                $lowercaseSystemClassName = strtolower($systemName);

                $dataArray['id'] = $detail->id;
                foreach ($system['data'] as $data) {
                    $dataArray[$data['name']] = $data['value'];
                }

                $otherModel = new $systemClassName;
                $otherModel->updateOtherData($dataArray);

                $othersNewPrice = $otherModel->getPrice($dataArray);
                $reservationSummaryModel = new RM_ReservationSummary();
                $reservationSummaryRows = $reservationSummaryModel->fetchByReservationDetail($detail, $lowercaseSystemClassName)->current();

                $reservationSummaryRows->total_amount = $othersNewPrice['price'];
                $reservationSummaryRows->save();
            }
            // others system complete


            if ($detail == null) {
                $detail = $reservationDetailsModel->createRow($detailJson);
            } else {
                foreach ($detailJson as $key => $value) {
                    $detail->$key = $value;
                }
            }

            $period = new RM_Reservation_Period(
                            new RM_Date(strtotime($detail->start_datetime)),
                            new RM_Date(strtotime($detail->end_datetime))
            );

            $persons = new RM_Reservation_Persons(array("adults" => $detail->adults, "children" => $detail->children, "infants" => $detail->infants));

            // group handling (only used when the groups is enabled)
            $templateUnitID = $unit->isTemplateUnit();
            if ($templateUnitID !== null) {
                if ($templateUnitID !== (int) $unit_id) {
                    // if it's not the main template unit then switch to the main template
                    // unit to get the price information
                    $unit = $unitModel->get($templateUnitID);
                }
            }

            $information = new RM_Prices_Information(
                            $unit,
                            $period,
                            $persons,
                            $otherInfo[0]
            );
            $priceSystem = RM_Environment::getInstance()->getPriceSystem();

            try {
                $detail->total_price = $priceSystem->getTotalUnitPrice($information);
            } catch (Exception $e) {
                $detail->total_price = 0;
            }

            //$detail->total_price += $otherPriceTotal;

            $detail->save();
        }

        //We need to recalculate all information that was saved to reservation
        $reservation->recalculate();

        return array('data' => array('success' => true));
    }

    /**
     * Returns the list data for the Unit Assignment Grid.
     *
     * This creates a JS variable containing the list configuration/setup for
     * the reservation list. This is implicated in list.js
     *
     * @deprecated
     * @param  	request id  the reservation id.
     * @param  	request type    if this is a new or existing reservation.
     * @param    request start   list start used for pagenation
     * @param    request limit   list limit used for pagenation
     * @param    request sort    list sort parameter
     * @param    request dir list direction Asc/desc
     * @param    request filter  an array of any filters applied
     * @return   json    information required to configure unit selection grid.
     */
    public function listunitsJsonAction() {
        $id = $this->_getParam('id');
        $type = $this->_getParam('type');

        $offset = $this->_getParam('start');
        $count = $this->_getParam('limit');
        $sort = $this->_getParam('sort', 'id');
        $direction = $this->_getParam('dir', 'DESC');
        $language = $this->_translate->getAdapter()->getLocale();

        $order = $sort . ' ' . $direction;

        $dao = new RM_ReservationDetails;
        if ($type == "new") {
            $unitTypeDAO = new RM_UnitTypes();
            $unitType = $unitTypeDAO->find('1')->current();

            $details = $dao->getAll(
                            $unitType,
                            $order,
                            $count,
                            $offset,
                            $language,
                            $filters
                    )->toArray();

            $total = $dao->getAll(
                            $unitType,
                            $order,
                            null,
                            null,
                            $language,
                            $filters
                    )->count();
        } else {
            $reservationDAO = new RM_Reservations();
            $reservation = $reservationDAO->find($id)->current();
            $total = $dao->fetchAllByReservation($reservation, $order, null, null, $language)->count();
            $details = $dao->fetchAllByReservation($reservation, $order, $count, $offset, $language)->toArray();
        }

        $json = new stdClass;
        $json->total = $total;
        $json->data = $details;

        return array(
            'data' => $json
        );
    }

    /**
     * Method for price calculation for the reservation edit page
     */
    public function getpriceJsonAction() {

        $unit_id = $this->_getParam('unit_id');
        $periods = $this->_getParam('periods');
        $persons = $this->_getParam('persons');
        $otherinfo = $this->_getParam('otherinfo', array());

        // format the other info data
        $otherinfoArray = Zend_Json::decode($otherinfo);

        $unitModel = new RM_Units();
        $priceSystem = RM_Environment::getInstance()->getPriceSystem();

        $jsonDetails = array();

        $unit = $unitModel->get($unit_id);
        $currentUnit = $unit; // save this information for later
        // group handling (only used when the groups is enabled)
        $templateUnitID = $unit->isTemplateUnit();
        if ($templateUnitID !== null) {
            if ($templateUnitID !== (int) $unit_id) {
                // if it's not the main template unit then switch to the main template
                // unit to get the price information
                $unit = $unitModel->get($templateUnitID);
            }
        }

        $jsonUnit = new stdclass();
        $jsonUnit->name = $currentUnit->name;
        $jsonUnit->id = $currentUnit->id;

        $periodArray = Zend_Json::decode($periods);
        $personArray = Zend_Json::decode($persons);

        $jsonPeriod = new stdclass();
        $config = new RM_Config();
        $jsonPeriod->start = $config->convertDates($periodArray['start'], RM_Config::PHP_DATEFORMAT, RM_Config::JS_DATEFORMAT);
        $jsonPeriod->end = $config->convertDates($periodArray['end'], RM_Config::PHP_DATEFORMAT, RM_Config::JS_DATEFORMAT);

        $json = new stdclass();
        $json->unit = $jsonUnit;
        $json->period = $jsonPeriod;
        $json->otherinfo = $otherinfoArray[0];

        $persons = new RM_Reservation_Persons($personArray);

        $information = new RM_Prices_Information(
                        $unit,
                        new RM_Reservation_Period(
                                new RM_Date(strtotime($periodArray['start'])),
                                new RM_Date(strtotime($periodArray['end']))
                        ),
                        $persons,
                        $otherinfoArray[0]
        );

        $jsonResponce = new stdClass;
        try {
            $totalPrice = $priceSystem->getTotalUnitPrice($information);
            $jsonResponce->success = true;
        } catch (RM_Exception $e) {
            $totalPrice = 0;
            $jsonResponce->error = $e->getMessage();
            $jsonResponce->success = false;
        }

        $config = new RM_Config();
        $currencySymbol = $config->getValue('rm_config_currency_symbol');
        $json->price = $currencySymbol . $totalPrice;

        $jsonDetails[] = $json;
        $jsonResponce->details = $jsonDetails;

        return array(
            'data' => $jsonResponce
        );
    }

    /**
     * Returns the list configuration for the unit assignment.
     *
     * This creates a JS variable containing the list configuration/setup for
     * the reservation list. This is implicated in list.js
     *
     * @param  	request id  the reservation id.
     * @param  	request type    if this is a new or existing reservation.
     * @param  	request start_datetime  the selected start date.
     * @param  	request end_datetime  the selected end date.
     * @return   json    information required to configure unit selection grid.
     */
    public function unitgridJsonAction() {
        $fieldsDAO = new RM_ReservationConfig();
        $configFields = $fieldsDAO->getAdminEdit()->toArray();
        foreach ($configFields as $key => $configField) {
            $metainfo[] = $configField['admin_edit_preferences'];
        }

        $fieldsDAO = new RM_UnitConfig();
        $configFields = $fieldsDAO->getAllReservationFields()->toArray();
        foreach ($configFields as $key => $configField) {
            //We need to check reservations and
            //TODO: this is some kind of hardcode we need to remove this later and create some other GOOD code :)
            if ($configField['column_name'] == 'id') {
                $id = $this->_getParam('id'); //reservation ID
                $type = $this->_getParam('type'); //indicates if this is a new reservation.

                $reservationModel = new RM_Reservations;

                // if the type = new there will not be any reservation information for this
                // so we need to construct some information so that we can return the available units.
                if ($type == 'new') {
                    $reservation->id = $id;
                    $reservation->start_datetime = $this->_getParam('start_datetime');
                    $reservation->end_datetime = $this->_getParam('end_datetime');
                } else {
                    // if this is not new then load the reservation info...

                    $reservation = $reservationModel->find($id)->current();
                }

                $unitModel = new RM_Units();
                $availableUnits = $unitModel->getAllAvailableForReservation(
                                $reservation,
                                $reservationModel
                );

                foreach ($availableUnits as $unit) {
                    $editorStore[] = "['{$unit->id}', '{$unit->id}']";
                }
                $editorStore = "[" . implode(',', $editorStore) . "]";

                if ($type == 'new') {
                    $metainfo[] = str_replace("'---store---'", $editorStore, $configField['admin_new_reservation_preferences']);
                } else {
                    $metainfo[] = str_replace("'---store---'", $editorStore, $configField['admin_edit_reservation_preferences']);
                }
            } else {
                if ($type == 'new') {
                    $metainfo[] = $configField['admin_new_reservation_preferences'];
                } else {
                    $metainfo[] = $configField['admin_edit_reservation_preferences'];
                }
            }
        }
        return array(
            'data' => "{fields:[" . implode(',', $metainfo) . "]}",
            'encoded' => true
        );
    }

    /**
     * Edit JS - Used to load all configuration for Edit Reservation Form
     *
     * This method loads only configuration information required by the edit.js
     * reservation page.
     *
     * @return   js variable used directly by edit.js
     */
    public function edit_Js_Action() {
        $fieldsDAO = new RM_ReservationConfig();
        $configFields = $fieldsDAO->getAdminEdit()->toArray();
        foreach ($configFields as $key => $configField) {
            $metainfo[] = $configField['admin_edit_preferences'];
        }

        $fieldsDAO = new RM_UnitConfig();
        $configFields = $fieldsDAO->getAllReservationFields()->toArray();
        foreach ($configFields as $key => $configField) {
            $metainfo[] = $configField['admin_edit_reservation_preferences'];
        }

        return "RM.Common.Reservations_Edit_Setup([" . implode(',', $metainfo) . "]);";
    }

    /**
     * Return number of unread reservations
     *
     * @return array - with 'unread' count
     */
    public function getunreadJsonAction() {
        $model = new RM_Reservations();
        $newUnreadCount = $model->getUnreadCount();
        return array('data' => array('unread' => $newUnreadCount));
    }

    /**
     * Marks a reservation read
     *
     * @return json
     * @deprecated since 1.2.5
     */
    public function markisreadJsonAction() {
        $id = $this->_getParam('id');
        $model = new RM_Reservations();
        $reservation = $model->find($id)->current();
        $reservation->is_read = 1;
        $reservation->save();
        return array('data' => array('success' => true));
    }

    /**
     * This is used for the reservation list to mark it paid
     */
    public function markpaidJsonAction() {
        $ids = $this->_getParam('ids', array());
        $model = new RM_Reservations();
        foreach ($ids as $id) {
            $reservation = $model->find($id)->current();
            if ($reservation == null || $reservation->isPaid()) {
                continue;
            }
            $model->markPaid($reservation);
        }
        return array(
            'data' => array('success' => true)
        );
    }

    /**
     * This is used for the reservation list to toggle read/unread
     */
    public function readmarkerJsonAction() {

        $ids = $this->_getParam('ids', array());
        $state = $this->_getParam('state');

        $model = new RM_Reservations();

        foreach ($ids as $id) {
            $reservation = $model->find($id)->current();
            if ($reservation == null)
                continue;
            $reservation->is_read = $state;
            $reservation->save();
        }

        return array(
            'data' => array('success' => true)
        );
    }

    public function getreservationsJsonAction() {

        $unit_id = $this->_getParam('unitid');
        $date = $this->_getParam('date');

        // TODO: add admin selected language here:-
        $lang = RM_Environment::getInstance()->getLocale();

        $reservations = new RM_Reservations();
        $reservationDetails = $reservations->fetchAllByUnitDate($unit_id, $date, $lang);
        $jsonReservations = Array();
        $config = new RM_Config();
        $usersObj = new RM_Users;

        foreach ($reservationDetails as $reservation) {
            $jsonData = new stdClass;
            $jsonData->reservation_id = $reservation->reservation_id;
            $jsonData->unit_id = $reservation->unit_id;
            $jsonData->start_date = $config->convertDates($reservation->start_datetime, RM_Config::PHP_DATEFORMAT, RM_Config::JS_DATEFORMAT);
            $jsonData->end_date = $config->convertDates($reservation->end_datetime, RM_Config::PHP_DATEFORMAT, RM_Config::JS_DATEFORMAT);
            $jsonData->total_price = $reservation->total_price;
            $jsonData->unit_name = $reservation->name;
            $jsonData->user_id = $reservation->user_id;

            if ($reservation->confirmed) {
                $confirmed = "<img src='" . RM_Environment::getConnector()->getRootURL() . "/RM/userdata/images/system/small/reservation_confirmed.png' border='0'>";
            } else {
                $confirmed = "<img src='" . RM_Environment::getConnector()->getRootURL() . "/RM/userdata/images/system/small/reservation_unconfirmed.png' border='0'>";
            }

            $jsonData->confirmed = $confirmed;

            $titleArray = str_replace(chr(39), chr(34), $this->_translate->_('Common.JSON', 'Titles'));
            $title = $usersObj->userTitle((int) $reservation->title, $titleArray);
            $jsonData->title = $title;

            $jsonData->first_name = $reservation->first_name;
            $jsonData->last_name = $reservation->last_name;
            $jsonReservations[] = clone $jsonData;
        }

        if (empty($jsonReservations)) {
            return array(
                'data' => array('success' => false)
            );
        }

        $json = "{
            data : " . Zend_Json::encode($jsonReservations) . "
        }";

        return array(
            'data' => $json,
            'encoded' => true
        );
    }

}