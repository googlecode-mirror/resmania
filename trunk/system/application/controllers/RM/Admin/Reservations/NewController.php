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
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_Reservations_NewController extends RM_Controller {

    public function updateJsonAction() {
        $data = Zend_Json::decode($this->_getParam('data'));

        // we don't use the id as this is a shortened version of the booking ref
        // we use title as this contains the full booking reference.
        $reservationID = $data['title'];
        if ($reservationID == null) {
            return array('data' => array('success' => false, "message" => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Reservations.Edit', 'ReservationIDMissed')));
        }

        $unitID = $data['cid'];
        if ($unitID == null) {
            return array('data' => array('success' => false, "message" => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Reservations.Edit', 'UnitIDMissed')));
        }

        $reservationModel = new RM_Reservations();
        $reservation = $reservationModel->find($reservationID)->current();
        if ($reservation == null) {
            return array('data' => array('success' => false, "message" => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Reservations.Edit', 'ResDataInvalid')));
        }

        if ($data['uid'] !== $reservation->user_id) {
            // if the assigned user is changed update the user id.
            $reservation->user_id = $data['uid'];
            $reservation->save();
        }


        $unitModel = new RM_Units();
        $unit = $unitModel->find($unitID)->current();
        if ($unit == null) {
            return array('data' => array('success' => false, "message" => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Reservations.Edit', 'UnitDataInvalid')));
        }

        $reservationDetailsModel = new RM_ReservationDetails();
        $detail = $reservationDetailsModel->fetchAllBy($unit, $reservation)->current();
        if ($detail == null) {
            return array('data' => array('success' => false, "message" => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Reservations.Edit', 'ResDataInvalid')));
        }

        $startDate = $data['start'];
        if ($startDate == null) {
            return array('data' => array('success' => false, "message" => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('StartDate')));
        }

        $endDate = $data['end'];
        if ($endDate == null) {
            return array('data' => array('success' => false, "message" => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('EndDate')));
        }

        $persons = new RM_Reservation_Persons(array("adults" => 1, "children" => 0, "infants" => 0));

        $period = new RM_Reservation_Period(
                        new RM_Date(strtotime($startDate)),
                        new RM_Date(strtotime($endDate))
        );
        $information = new RM_Prices_Information(
                        $unit,
                        $period,
                        $persons
        );

        $priceSystem = RM_Environment::getInstance()->getPriceSystem();
        $detail->start_datetime = $startDate;
        $detail->end_datetime = $endDate;

        try {
            $detail->total_price = $priceSystem->getTotalUnitPrice($information);
        } catch (Exception $e) {
            $detail->total_price = 0;
        }

        $detail->save();

        $reservation->recalculate();

        $idArray = explode("-", $reservationID);
        $id = (int) $idArray[1];

        return array(
            'data' => array(
                "success" => true,
                "message" => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN)->_('Admin.Reservations.Edit', "LoadedData"),
                "data" => array(
                    "id" => $id,
                    "cid" => $unitID,
                    "title" => $reservationID,
                    "start" => $startDate,
                    "end" => $endDate,
                    "url" => "RM.Pages.Functions.Reservations_EditJson_Request('" . $period->id . "')",
                    "ad" => true
                )
            )
        );
    }

    public function unitsJsonAction() {
        $unitModel = new RM_Units();
        $units = $unitModel->getAll(new RM_Unit_Search_Criteria());

        $unitsJson = array();
        foreach ($units as $unit) {
            $unitJson = new stdClass();
            $unitJson->Id = $unit->getId();
            $unitJson->Name = $unit->name;
            $unitJson->Color = $unit->color;
            $unitJson->ShowTime = (bool) RM_Prices_Manager::getInstance()->getRealPriceSystem($unit)->getShowTime($unit);
            $unitsJson[] = $unitJson;
        }
        return array(
            'data' => $unitsJson
        );
    }

    /**
     * Delete a block
     *
     * @return JSON
     */
    public function deleteJsonAction() {

        $reservationID = $this->_getParam('reservationID');
        if ($reservationID == null) {
            return array('data' => array('success' => false));
        }

        $reservationModel = new RM_Reservations();
        $reservation = $reservationModel->find($reservationID)->current();
        if ($reservation == null) {
            return array('data' => array('success' => false));
        }

        $result = (bool) $reservation->delete();
        return array(
            'data' => array(
                'success' => true,
                'message' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN)->_('Admin.Reservations.Edit', 'DeletedOK')
            )
        );
    }

    /**
     * saves the new reservation selection
     *
     * @return JSON
     */
    public function insertJsonAction() {

        // get the data
        $data = Zend_Json::decode($this->_getParam('data'));
        $unitID = $data['cid'];
        $userID = $data['uid'];
        $start = $data['start'];
        $end = $data['end'];

        // get the unit object
        $unitModel = new RM_Units();
        $unit = $unitModel->get($unitID);

        // convert the date selection to a period object
        $periodObj = new RM_Reservation_Period(
            new RM_Date(strtotime($start)),
            new RM_Date(strtotime($end))
        );

        // check if the dates are allowed
        $reservationModel = new RM_ReservationDetails();
        $currentReservationCount = $reservationModel->getReservationCount($unit, $periodObj);

        if ( $currentReservationCount > 0 ) {
            return array(
                'data' => array(
                    'success' => false,
                    'message' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN)->_('Admin.Reservations.Edit', 'InvalidSelection')
                )
            );
            die();
        }

        $unitDetails = null; // reset the unit array for safe keeping
        $unitDetails = array(new RM_Reservation_Details(
                    $unit,
                    $periodObj,
                    new RM_Reservation_Persons()
                ));

        $userModel = new RM_Users();
        $user = $userModel->find($userID)->current(); // get the "system user"
        // get a reservation ID
        $reservationID = RM_Reservations::createReservationID();
        $reservationModel = new RM_Reservations();
        $result = $reservationModel->insertNewReservation($user, $unitDetails, 0, 1, $reservationID);

        if (!$result) {
            return array(
                'data' => array(
                    'success' => false,
                    'message' => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)->_('Admin.Reservation.Edit', 'ServerError')
                )
            );
        } else {

            // mark the new block as paid
            $reservation = $reservationModel->find($reservationID)->current();

            if ($userID === ""){
                $reservationModel->markPaid($reservation);
            }

            $idArray = explode("-", $reservationID);
            $id = (int) $idArray[1];

            // check time
            $ad = true; // all day
            $starttime = explode(" ",$start);
            if ($starttime[1] !== "00:00:00"){ $ad = false; }
            $endtime = explode(" ",$end);
            if ($endtime[1] !== "00:00:00"){ $ad = false; }

            return array(
                'data' => array(
                    "success" => true,
                    "message" => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN)->_('Admin.Reservation.Edit', "LoadedData"),
                    "data" => array(
                        "id" => $id,
                        "cid" => $unitID,
                        "uid" => (int) $userID,
                        "title" => $reservationID,
                        "start" => $start,
                        "end" => $end,
                        "ad" => $ad
                    )
                )
            );
        }
    }

    /**
     * gets the calendar details (names, colors etc).
     *
     * @return JSON
     */
    public function getcalendarsJsonAction() {

        // get all units
        $unitModel = new RM_Units();
        $units = $unitModel->getAll(new RM_Unit_Search_Criteria());

        $calArray = array();

        foreach ($units as $unit) {
            $calArray[] = array(
                "id" => $unit->id,
                "title" => $unit->name,
                "color" => $unit->id
            );
        }
        return array(
            'data' => array("calendars" => $calArray)
        );
    }

    /**
     * Gets the calendar Data for the read function
     *
     * @return JSON
     */
    public function getdataJsonAction() {

        $start = $this->_getParam('start');
        $end = $this->_getParam('end');

        return array(
            'data' => $this->_getdata($start, $end)
        );
    }

    /**
     * Gets the calendar Data
     *
     * @return JSON
     */
    private function _getdata($startRange = false, $endRange = false) {

        if (!$startRange || !$endRange) {
            $startRange = date("Y-m-d");
            $endRange = date("Y-m-d");
        }

        $unitModel = new RM_Units();
        $units = $unitModel->getAll(new RM_Unit_Search_Criteria());

        $unitModel = new RM_Units();
        $reservationModel = new RM_Reservations();
        $config = new RM_Config();
        $evtsArray = array();

        // add 6 months to either side of the reservation
        $RMDate = new RM_Date();
        $start = $RMDate->dateSub($startRange, 180);
        $end = $RMDate->dateAdd($endRange, 180);

        $filter = array();
        $filter['data']['type'] = 'date';
        $filter['field'] = 'start_datetime';
        $filter['data']['comparison'] = 'gt';
        $filter['data']['value'] = $start;
        $filterArray[] = $filter;

        $filter = array();
        $filter['data']['type'] = 'date';
        $filter['field'] = 'end_datetime';
        $filter['data']['comparison'] = 'lt';
        $filter['data']['value'] = $end;
        $filterArray[] = $filter;

        $filter = array();
        $filter['data']['type'] = 'numeric';
        $filter['field'] = 'in_progress';
        $filter['data']['comparison'] = 'eq';
        $filter['data']['value'] = 0;
        $filterArray[] = $filter;

        foreach ($units as $unit) {
            $unit = $unitModel->get($unit->id);
            $reservations = $reservationModel->fetchAllByUnit($unit, $filterArray);
            foreach ($reservations as $period) {

                $idArray = explode("-", $period->id);
                $id = (int) $idArray[1];

                // check time
                $ad = true; // all day
                $starttime = explode(" ",$period->start_datetime);
                if ($starttime[1] !== "00:00:00"){ $ad = false; }
                $endtime = explode(" ",$period->end_datetime);
                if ($endtime[1] !== "23:30:00"){ $ad = false; }

                $evtsArray[] = array(
                    "id" => $id,
                    "cid" => $unit->id,
                    "uid" => (int) $period->user_id,
                    "title" => "<span style='text-align:center;width:70%;'>".$period->id. "&nbsp;&nbsp;&nbsp;" . $unit->name . "(".$unit->id.")</span>",
                    "start" => $period->start_datetime,
                    "end" => $period->end_datetime,
                    "ad" => $ad
                );
            }
        }

        return array(
            "success" => true,
            "message" => RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN)->_('Admin.Reservation.Edit', "LoadedData"),
            "data" => $evtsArray
        );
    }


    public function getdefaulttimesJsonAction(){

        $unitID = $this->_getParam('uid');
        $unitModel = new RM_Units();
        $unit = $unitModel->find($unitID)->current();

        // price system
        $priceSystem = RM_Environment::getInstance()->getPriceSystem()->getRealPriceSystem($unit);
        $configClassName = "RM_Unit".$priceSystem->name."Config";
        $priceConfigObject = new $configClassName;

        $defaultStartTime = $priceConfigObject->fetchValueByUnit($unit->id, 'default_start_time');
        $defaultEndTime = $priceConfigObject->fetchValueByUnit($unit->id, 'default_end_time');

        return array(
            'data' => array("defaultstarttime" => $defaultStartTime, "defaultendtime" => $defaultEndTime)
        );

    }

}