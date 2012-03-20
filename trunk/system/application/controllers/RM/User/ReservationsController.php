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
 *
 * User Reservation Controller
 *
 * This handles all AJAX requests from the Admin GUI Language Section.
 * These methods will create an AJAX response containing JSON data. The JSON
 * data is read by the JS code and rendered into interface.
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.3
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_User_ReservationsController extends RM_User_Controller {

    function addtocartJsonAction() {

        $criteria = RM_Reservation_Manager::getInstance()->getCriteria();
        if ($criteria == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('User.Unit.List', 'NoDatesSelected')));
        }

        if (($criteria->start_datetime == null) || ($criteria->end_datetime == null)) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('User.Unit.List', 'NoDatesSelected')));
        }

        $unitID = $this->_getParam('master_id', null);

        $criteria->adults = $this->_getParam('adults', 1);
        $criteria->children = $this->_getParam('children', 0);
        $criteria->infants = $this->_getParam('infants', 0);
        $criteria->otherinfo = $this->_getParam("otherInfo", array());

        $quantity = ($criteria->quantity >1) ? $criteria->quantity : 1;

        if ($unitID == null) {
            return array('data' => array('success' => false, 'error' => $this->_translate->_('User.Unit.List', 'WrongUnitID')));
        }

        $unitModel = new RM_Units();

        $unit = $unitModel->get($unitID, RM_Environment::getInstance()->getLocale(),array("summary","description"));

        $period = new RM_Reservation_Period(
            new RM_Date(strtotime($criteria->start_datetime)),
            new RM_Date(strtotime($criteria->end_datetime))
        );

        $persons = new RM_Reservation_Persons(array("adults" => $criteria->adults, "children" => $criteria->children, "infants" => $criteria->infants));

        // set a temporary session variable this is used in the groups row class
        $_SESSION["returnAllUnits"] = true;

        $units = array();
        $subunits = array();
        $selectedCount = 0; 

        if ($unit->isGroup()){
            
            // Check for items of the same group ID already saved to the instance
            // this is prevention for duplication in the cart when using groups
            $manager = RM_Reservation_Manager::getInstance();
            $existingDetails = $manager->getAllDetails();

            foreach($existingDetails as $detail){
                $groupUnit = $detail->getUnit();
                if ($groupUnit->getGroupId()===$unit->getGroupId()){
                    // if it's already selected add one to the selection count.
                    $selectedCount +=1;
                }
            }

            $availableSubUnits = $unit->getAllSubUnits($criteria, $unit);

            // loop through the quantity of units we require and save these to the reservation
            foreach($availableSubUnits as $potentialUnit){

                    if ($selectedCount >= $quantity) { break;}
                    $details = new RM_Reservation_Details($potentialUnit, $period, $persons, $otherinfo);
                    RM_Reservation_Manager::getInstance()->addDetails($details);
                    $selectedCount +=1;
            }
        

            // if the number of units saved to the reservation are not equal to the quantity also add the master unit
            if ($selectedCount<$quantity){
                // add the master unit also
                $details = new RM_Reservation_Details($unit, $period, $persons, $otherinfo);
                RM_Reservation_Manager::getInstance()->addDetails($details);
            }

        } else {

            $details = new RM_Reservation_Details($unit, $period, $persons, $otherinfo);
            RM_Reservation_Manager::getInstance()->addDetails($details);
        }
        
        return array(
            'data' => array('success' => true)
        );
    }

    function cartAction() {
        $formModel = new RM_Forms();
        $form = $formModel->find('cart')->current();
        $this->view->state = $form->getState();
        $this->view->form = $form;
    }

    function emptycartAction() {
        $criteria = new RM_Unit_Search_Criteria();
        $criteria->publishedOnly = true;
        RM_Reservation_Manager::getInstance()->resetCriteria()->setCriteria($criteria);
        RM_Reservation_Manager::getInstance()->resetAllDetails();
        $this->_redirect('Unit', 'list');
    }

    function deletecartoptionAction() {
        $unitID = $this->_getParam('unit_id', null);
        $this->_deleteUnit($unitID, 'cart');
    }

    function deletesummaryoptionAction() {
        $unitID = $this->_getParam('unit_id', null);
        $this->_deleteUnit($unitID, 'summary');
    }

    private function _deleteUnit($unitID, $action) {
        if ($unitID === null) {
            $this->_redirect('Reservations', $action);
        }

        $unitModel = new RM_Units();
        $unit = $unitModel->get($unitID);
        if ($unit === null) {
            $this->_redirect('Reservations', $action);
        }

        $manager = RM_Reservation_Manager::getInstance();
        $manager->deleteDetails($unit);
        $this->_redirect('Reservations', $action);
    }

    function purchasecartAction() {
        $user = RM_Reservation_Manager::getInstance()->getUser();
        if ($user === null) {
            $this->_redirect('User', 'userdetails');
        }
        $this->_redirect('Reservations', 'summary');
    }

    function summaryAction() {
        $details = RM_Reservation_Manager::getInstance()->getAllDetails();
        if (count($details) == 0) {
            $this->_redirect('Unit', 'list');
        }

        $formModel = new RM_Forms();
        $form = $formModel->find('summary')->current();
        $this->view->state = $form->getState();
        $this->view->form = $form;
    }

    /*
     * summaryvalidateAction
     *
     * Validates the summary page selection, data and passes to the next phase
     * (usually the payment modules) for processing.
     *
     */

    function summaryvalidateAction() {
        $formModel = new RM_Forms();
        $form = $formModel->find('summary')->current();

        $valid = $form->validate($this->getRequest());
        if ($valid == false) {
            RM_Reservation_Manager::getInstance()
                    ->setFormErrors('summary', $form->getErrors())
                    ->save();
            $this->_redirect('Reservations', 'summary');
        }

        //Apply extras
        $extrasSystems = RM_Environment::getInstance()->getExtrasSystems();
        if (count($extrasSystems) !== 0) {
            $details = RM_Reservation_Manager::getInstance()->getAllDetails();
            foreach ($details as $detail) {
                foreach ($extrasSystems as $extrasSystem) {
                    $newDetail = $extrasSystem->applySelection($this->getRequest(), $detail);
                    if (false === $newDetail) {
                        RM_Reservation_Manager::getInstance()
                                ->setFormErrors('summary', array('ExtrasSelectionIsWrong'))
                                ->save();
                        $this->_redirect('Reservations', 'summary');
                    }
                }
                RM_Reservation_Manager::getInstance()->addDetails($newDetail);
            }
        }

        //Apply others
        $othersSystems = RM_Environment::getInstance()->getOthersSystems();
        if (count($othersSystems) !== 0) {
            $details = RM_Reservation_Manager::getInstance()->getAllDetails();
            foreach ($details as $detail) {
                foreach ($othersSystems as $othersSystem) {
                    $newDetail = $othersSystem->applySelection($this->getRequest(), $detail);
                    if (false === $newDetail) {
                        RM_Reservation_Manager::getInstance()
                                ->setFormErrors('summary', array('OthersSelectionIsWrong'))
                                ->save();
                        $this->_redirect('Reservations', 'summary');
                    }
                }
                RM_Reservation_Manager::getInstance()->addDetails($newDetail);
            }
        }

        // Create the User
        $manager = RM_Reservation_Manager::getInstance();

        if ($manager->getCriteria() === null){
            $this->_redirect('Reservations', 'sessiontimedout');
        }

        $user = $manager->getUser(); // this is the resmania user instance
        $loginStatus = RM_Environment::getConnector()->getLoginStatus(); // get the Host CMS login status.

        if (isset($user)){
            if (!$loginStatus) { // check if the user is logged into the CMS
                $userPassword = $user->password; //This in unencrypted one, we need to login to cms.
                $registered = $user->isRegistered();

                if ($registered == false) { // if we are not logged in and not registered, register the user
                    $config = new RM_Config();
                    if ($config->getValue('rm_config_enable_cms_user_creation')) {
                        $user->setTable(RM_Environment::getConnector()->getUsersModel());
                    } else {
                        $user->setTable(new RM_Users());
                    }
                    if ($user->group_id == null) {
                        $user->group_id = RM_UserGroups::REGULAR;
                    }
                    $user->save();
                }

                // login
                RM_Environment::getConnector()->authenticate(
                        $user->username,
                        $userPassword
                );
            }
        }

        // save the reservation with the in_progress flag set as true.
        // this manse the reservation will not be visable until the processing is complete.

        $reservationModel = new RM_Reservations();
        // we need to check is there a reservation in status progress in database with the same
        // id as we have here.
        $inProgressReservation = $reservationModel->find($manager->getReservationID())->current();
        if ($inProgressReservation !== null) {
            if ($inProgressReservation->in_progress == 1) {
                $inProgressReservation->delete();
            } else {
                //we already have a full stored reservation in database so we need reset Manager and go to the first page
                $manager->reset();
                $this->_redirect('Unit', 'list');
            }
        }

        $reservationModel->insertNewReservation(
                $manager->getUser(),
                $manager->getAllDetails(),
                1,
                0,
                $manager->getReservationID()
        );

        RM_Log::toLog("New Reservation Created with the ID: " . $manager->getReservationID());

        // direct the process to the payment provider form...
        $this->_forward('form', RM_Environment::getInstance()->getPaymentSystem()->getControllerName());
    }

    function successAction() {

        // this is the where the user is directed to when the payment provider has issued a success.

        $manager = RM_Reservation_Manager::getInstance();
        $reservationID = $manager->getReservationID();

        // update the the in_progress flag
        // this is also completed in the payment notification for the payment
        // module, but we do it here to make sure that reservations that are
        // sent to the successAction they are visible and no longer hidden.
        $model = new RM_Reservations();
        $reservation = $model->find($reservationID)->current();

        $model->inProgressComplete($reservation);
        RM_Log::toLog("InProgress Marker Updated");

        // fire the events
        RM_Notifications_Manager::getInstance()->fire(
                'ReservationCompleteSuccessful',
                $manager
        );
        RM_Notifications_Manager::getInstance()->fire(
                'ReservationPostbookingMessage',
                $manager
        );
        RM_Notifications_Manager::getInstance()->fire(
                'ReservationNewReservationAlert',
                $manager
        );

        RM_Log::toLog("Reservation Complete ID: " . $reservationID);

        $content = $this->_getCompletePageContent($manager);
        if ($content === null) {
            $this->view->content = '';
        } else {
            $this->view->content = $content;
        }

        $manager->reset();
        $manager->setPaymentStatus(RM_Payments_Status::NO_TRANSACTION);
    }

    private function _getCompletePageContent(RM_Reservation_Manager $manager) {
        $model = new RM_Templates();
        $template = $model->find('ReservationComplete')->current();
        if ($template === null) {
            return null;
        }
        $iso = RM_Environment::getInstance()->getLocale();
        return $this->_parseCompleteTemplate($manager, $template->$iso);
    }

    /*
      Example of template:

      Reservation ID => {$reservation.id}
      Total => {$reservation.total}
      Paid => {$reservation.paid}
      Total Due => {$reservation.due}
      Reservation Secure (Confirmed) => {$reservation.confirmed} (Yes/No)
      {foreach $details detail}
      Unit ID => {$detail.unit.id}
      Unit Name => {$detail.unit.name} with selected locale
      Rental Start => {$detail.period.start}
      Rental End => {$detail.period.end}
      Adults => {$detail.persons.adults}
      Children => {$detail.persons.children}
      Infants => {$detail.persons.infants}
      Address => {$detail.location.address1}
      Address Continued => {$detail.location.address2}
      City => {$detail.location.city}
      State/County/Region => {$detail.location.state}
      Postcode => {$detail.location.postcode}
      Directions => {$detail.location.directions}
      Latitude => {$detail.location.latitude}
      Longitude => {$detail.location.longitude}
      Subtotal => {$detail.subtotal}
      {foreach $summary info}
      Summary ID => {$info.row_id}
      Summary Name => {$info.name}
      Summary Price Total => {$info.total_amount}
      {/foreach}
      {/foreach}
     */

    /**
     * Replace all placeholders with information from current made reservations
     *
     * @param RM_Reservation_Manager $manager
     * @param string $template
     * @return
     */
    private function _parseCompleteTemplate(RM_Reservation_Manager $manager, $template) {
        $data = new Dwoo_Data();

        $reservationModel = new RM_Reservations();
        $reservation = $reservationModel->find($manager->getReservationID())->current();

        $summaryModel = new RM_ReservationSummary();
        $unitModel = new RM_Units();
        $details = $reservation->getDetails();


        $arrayDetails = array();
        foreach ($details as $detailRow) {

            $detail = $detailRow->transform();

            $unitArray = $detail->getUnit()->toArray();
            $periodArray = $detail->getPeriod()->toArray();
            $periodArrayWithTime = $detail->getPeriod()->toArray(true);
            $personsArray = $detailRow->getPersons();

            //$unitID = $detail->getUnit()->getId();
            $unitID = $unitArray['id'];
            $unit = $unitModel->get($unitID);

            $locationModel = new RM_Locations();
            $location = $locationModel->fetchByUnit($unitID)->current();

            $arrayDetails[] = array(
                'unit' => $unit->toArray(),
                'period' => $periodArray,
                'periodtime' => $periodArrayWithTime,
                'persons' => $personsArray,
                'summary' => $summaryModel->fetchByReservationDetail($detailRow)->toArray(),
                'location' => ($location !== null) ? $location->toArray() : $locationModel->createRow()->toArray()
            );
        }
        $data->assign('details', $arrayDetails);
        $data->assign('user', $manager->getUser()->toArray());

        $reservationArray = $reservation->toArray();
        $reservationArray['confirmed'] = $reservation->isPaid() ? $this->_translate->_('MessageYes') : $this->_translate->_('MessageNo');
        $reservationArray['total'] = $reservation->getTotalPrice();
        $reservationArray['paid'] = $reservation->getTotalPaid();
        $reservationArray['due'] = $reservationArray['total'] - $reservationArray['paid'];
        $data->assign('reservation', $reservationArray);

        $dwooTemplate = new Dwoo_Template_String($template);
        $dwoo = new Dwoo();
        return $dwoo->get($dwooTemplate, $data);
    }

    function notcompleteAction() {
        $model = new RM_Templates();
        $template = $model->find('ReservationFailed')->current();
        if ($template !== null) {
            $iso = RM_Environment::getInstance()->getLocale();
            $this->view->template = $template->$iso;
        } else {
            $this->view->template = '';
        }

        // fire the not completed event
        $manager = RM_Reservation_Manager::getInstance();
        RM_Notifications_Manager::getInstance()->fire(
                'ReservationCompleteUnsuccessful',
                $manager
        );
    }

    /**
     * Session Timed out Handler
     */
    public function sessiontimedoutAction(){
        $criteria = new RM_Unit_Search_Criteria();
        $criteria->publishedOnly = true;
        RM_Reservation_Manager::getInstance()->resetCriteria()->setCriteria($criteria);
    }

    function setdatecriteriaJsonAction() {
        $this->_withoutView();
        $unitID = $this->_getParam('unit_id', null);
        $unitModel = new RM_Units();
        $unit = $unitModel->get($unitID, RM_Environment::getInstance()->getLocale(),array("summary","description"));

        if ($unit == null) {
            return array(
                'data' => array(
                    'success' => false,
                    'error' => $this->_translate->_('User.DatePicker', 'SelectedUnitDoesNotExists')
                )
            );
        }

        //TODO: we need to check dates for null
        $startdate = $this->_getParam('startdate', null);
        $enddate = $this->_getParam('enddate', null);
        $quantity = (int)$this->_getParam('qty', 1);

        //TODO: we need to check dates for rigth format to parse them into objects
        //check if start date is after end date
        $startDateObject = new RM_Date(strtotime($startdate));
        $endDateObject = new RM_Date(strtotime($enddate));
        if ($endDateObject->isEarlier($startDateObject)) {
            return array(
                'data' => array(
                    'success' => false,
                    'error' => $this->_translate->_('User.DatePicker', 'StartAfterEnd')
                )
            );
        }

        // check if the dates are available...
        $period = new RM_Reservation_Period(
            $startDateObject,
            $endDateObject
        );

        $adults = $this->_getParam("adults", 1);
        $children = $this->_getParam("children", 0);
        $infants = $this->_getParam("infants", 0);

        $persons = new RM_Reservation_Persons(array("adults" => $adults, "children" => $children, "infants" => $infants));

        // get otherinfo - this is non-standard price system information.
        $otherinfo = Zend_Json::decode($this->_getParam("otherinfo", "{}"));

        // while we don't need the price here, getTotalUnitPrice will also check the
        // period selected is valid.
        $information = new RM_Prices_Information($unit, $period, $persons, $otherinfo);
        $priceSystem = RM_Environment::getInstance()->getPriceSystem();

        try {
            $calculatedTotalPrice = $priceSystem->getTotalUnitPrice($information);
        } catch (RM_Exception $e) {
            return array(
                'data' => array('success' => false, 'error' => $e->getMessage())
            );
        }

        // get the default start/end times...
        $startArray = explode(" ",$startdate);
        $endArray = explode(" ",$enddate);

        $realPriceSystem = $priceSystem->getRealPriceSystem($unit);
        $configObjectName = "RM_Unit".$realPriceSystem->name."Config"; // ie: RM_UnitDailyPricesConfig
        $priceConfigObject = new $configObjectName;
        $recreatePeriod = false;

        // if the start time is null or 00:00:00 load the default price system times
        if (!isset($startArray[1]) || $startArray[1] ==="" || $startArray[1] ==="00:00:00"){
            try{
                // placed in a try so that if the price module returns an exception for default_end_time it does not crash this
                $defaultStartTime = $priceConfigObject->fetchValueByUnit($unitID, 'default_start_time');
                $startdate = $startArray[0]." ".$defaultStartTime;
                $startDateObject = new RM_Date(strtotime($startdate));
                $recreatePeriod = true;
            } catch (Exception $e){}
        }
        // if the end time is null or 00:00:00 load the default price system times
        if (!isset($endArray[1]) || $endArray[1] === "" || $endArray[1] === "23:30:00"){
            try{
                // placed in a try so that if the price module returns an exception for default_end_time it does not crash this
                $defaultEndTime = $priceConfigObject->fetchValueByUnit($unitID, 'default_end_time');
                $enddate = $endArray[0]." ".$defaultEndTime;
                $endDateObject = new RM_Date(strtotime($enddate));
                $recreatePeriod = true;
            } catch (Exception $e){}
        }

        // if the times have been added re-create the period object with the times...
        if ($recreatePeriod){
            $period = new RM_Reservation_Period($startDateObject, $endDateObject);
        }

        // get the unit setting for the setting availablity_check
        // this could fail if the price system doesn't have this setting so it is wrapped in a try
        try{
            $availabilityCheck = $priceConfigObject->fetchValueByUnit($unitID, 'availablity_check');
        } catch (Exception $e){
            $availabilityCheck = "1";
        }

        // check availability
        if ($availabilityCheck == "1"){
            $isAvailable = $unitModel->isAvailableUnitbyDate($unit, $period);
            if (!$isAvailable) {
                return array(
                    'data' => array(
                        'success' => false,
                        'error' => $this->_translate->_('User.DatePicker', 'ReselectDates')
                    )
                );
            }
        }

        $data = array();
        $data['start_datetime'] = $startdate;
        $data['end_datetime'] = $enddate;
        $data['adults'] = $adults;
        $data['children'] = $children;
        $data['infants'] = $infants;
        $data['otherinfo'] = $otherinfo;
        $data['quantity'] = $quantity;

        $criteria = new RM_Unit_Search_Criteria($data);
        $criteria->publishedOnly = true;
        RM_Reservation_Manager::getInstance()->resetCriteria()->setCriteria($criteria);

        // handle groups
        $isGroup = $unit->isGroup();
        $groupID = $unit->getGroupId();

        // if this is a group we need to get an available unit from the 'pool'
        if ($isGroup) {

            // get available sub units
            $AllavailableSubUnits = $unit->getAllSubUnits($criteria, $unit);
            foreach($AllavailableSubUnits as $subunit){
                if ($subunit->getGroupId() === $unit->getGroupId()){
                    $availableSubUnits[] = $subunit;
                }
            }

            // set a temporary session variable this is used in the groups row class
            $_SESSION["returnAllUnits"] = true;

            $units = array();
            $subunits = array();
            $selectedCount = 0;

            // if there is a unit available take the first one from the returned units...
            foreach($availableSubUnits as $potentialUnit){

                    if ($selectedCount >= $quantity) { break;}
                    $details = new RM_Reservation_Details($potentialUnit, $period, $persons, $otherinfo);
                    RM_Reservation_Manager::getInstance()->addDetails($details);

                    $selectedCount +=1;
            }

            // if the number of units saved to the reservation are not equal to the quantity also add the master unit
            if ($selectedCount<$quantity){
                // add the master unit also
                $details = new RM_Reservation_Details($unit, $period, $persons, $otherinfo);
                RM_Reservation_Manager::getInstance()->addDetails($details);
            }
        } else {

            $details = new RM_Reservation_Details($unit, $period, $persons);
            RM_Reservation_Manager::getInstance()->addDetails($details);
        }

        // remove the temporary session variable
        unset($_SESSION["returnAllUnits"]);

        return array(
            'data' => array('success' => true)
        );
    }

    public function reservationsJsonAction() {

        $unitID = $this->_getParam('unit_id', null);
        $startdate = $this->_getParam('startdate', null);
        $enddate = $this->_getParam('enddate', null);

        // unit dao
        $unitsDAO = new RM_Units();
        $unitObject = $unitsDAO->find($unitID)->current();

        $filter[] = array(
            'field' => 'start_datetime',
            'data' => array(
                'type' => 'date',
                'value' => $startdate,
                'comparison' => 'gt'
            )
        );
        $filter[] = array(
            'field' => 'end_datetime',
            'data' => array(
                'type' => 'date',
                'value' => $enddate,
                'comparison' => 'lt'
            )
        );
        $filters[] = array(
            'field' => 'confirmed',
            'data' => array(
                'type' => 'numeric',
                'value' => 1,
                'comparison' => 'eq'
            )
        );

        // reservation data
        $reservationsDAO = new RM_Reservations();
        $reservations = $reservationsDAO->fetchAllByUnit($unitObject, $filter);

        foreach ($reservations as $reservation) {
            $detailJson = new stdClass();
            $detailJson->Id = $reservation->id;
            $detailJson->ResourceId = $unitID;
            $detailJson->StartDate = $reservation->start_datetime;
            $detailJson->Color = 'red';
            $detailJson->EndDate = $reservation->end_datetime;
            $detailsJson[] = $detailJson;
        }

        return array(
            'data' => $detailsJson
        );
    }
}