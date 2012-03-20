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
 * Admin Unit Controller.
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_UnitsController extends RM_Controller {

    // TODO: need to add code to control Unit Types Available.
    private $_defaultUnitType = 1;

    public function listJsAction() {
        //$unitTypeDAO = new RM_UnitTypes();
        //$unitType = $unitTypeDAO->find($this->_defaultUnitType)->current();

        $fieldsDAO = new RM_UnitConfig();
        //$configFields = $fieldsDAO->getAdminList($unitType->id)->toArray();
        $configFields = $fieldsDAO->fetchAll()->toArray();

        foreach ($configFields as $key => $configField) {
            if ($configField['admin_list_preferences'] !== '') {
                $metainfo[] = $configField['admin_list_preferences'];
            }
        }

        // unit types translation from numbers to text
        $rmUnitTypes = new RM_UnitTypes();
        $language = RM_Environment::getInstance()->getLocale();
        $unittypes = $rmUnitTypes->getAll();

        $type = array();
        foreach ($unittypes as $unittype) {
            $type[$unittype->id] = $unittype->$language;
        }
        $type = Zend_Json::encode($type);


        $retVar = "RM.Common.Units_List_Setup([" . implode(',', $metainfo) . "]);";
        $retVar .= "RM.Common.Units_Types_Translation = " . $type . ";";
        return $retVar;
    }

    public function listJsonAction() {

        $criteria = new RM_Unit_Search_Criteria(array(), true);

        $dao = new RM_Units;
        $filters = $this->_getParam('filter', array());

        $isSelector = $this->_getParam('selector', false);

        $showOnlyAvailable = $this->_getParam('showOnlyAvailable', false);
        if ($showOnlyAvailable) {
            $reservationPeriod = new RM_Reservation_Period(
                            new RM_Date(strtotime($this->_getParam('start_datetime'))),
                            new RM_Date(strtotime($this->_getParam('end_datetime')))
            );
            $unitsReserved = $dao->getReservedUnits($reservationPeriod);

            $unitArray = array();
            foreach ($unitsReserved as $unit) {
                $unitArray[] = $unit->getId();
            }

            if (count($unitArray) > 0) {
                $filters[] = array(
                    'field' => "id",
                    'data' => array(
                        'type' => "listin",
                        'value' => implode(",", $unitArray),
                        'comparison' => 'notin'
                    )
                );
            }
        }

        if ($this->_getParam('reservationID') !== null) {
            $reservationID = $this->_getParam('reservationID');
            $reservationModel = new RM_Reservations();
            $reservation = $reservationModel->find($reservationID)->current();
            if ($reservation !== null) {
                $detailsModel = new RM_ReservationDetails();
                $details = $detailsModel->getAllByReservation($reservation);
                $reservedUnitIDs = array();
                foreach ($details as $detail) {
                    $reservedUnitIDs[] = $detail->unit_id;
                }
                if (count($reservedUnitIDs) > 0) {
                    $filters[] = array(
                        'field' => "id",
                        'data' => array(
                            'type' => "listin",
                            'value' => implode(",", $reservedUnitIDs),
                            'comparison' => 'notin'
                        )
                    );
                }
            }
        }
        $criteria->filters = $filters;
        $units = $dao->getAll($criteria)->toArray();

        $criteria->offset = $this->_getParam('start');
        $criteria->order = $this->_getParam('sort', 'id') . ' ' . $this->_getParam('dir', 'DESC');
        $criteria->count = $this->_getParam('limit');
        $total = $dao->getAll($criteria)->count();

        $json = new stdClass;
        $json->total = $total;
        $json->data = $units;

        return array(
            'data' => $json
        );
    }

    /**
     * Create a copy of all input units. Copy will be exactly the same object with just
     * a different primary key value from main rm database table.
     *
     * @param ids - array from REQUEST
     * @return array
     */
    public function copyJsonAction() {
        $unitIDs = $this->_getParam('ids', array());

        $model = new RM_Units();
        $total = $model->fetchAll()->count();

        $trueCopy = array();
        $falseCopy = array();
        foreach ($unitIDs as $unitID) {
            $unit = $model->find($unitID)->current();
            if ($unit !== null) {
                try {
                    $model->copyRow($unit);
                    $trueCopy[] = $unitID;
                } catch (RM_Exception $e) {
                    $falseCopy[$unitID] = $e->getMessage();
                }
            }
        }

        if (count($falseCopy) == 0) {
            $success = true;
            $message = $this->_translate->_('Admin.Units.List', 'CopySuccess');
        } else {
            $success = false;
            $message = $this->_translate->_('Admin.Units.List', 'CopyResult');
            if (count($trueCopy) > 0) {
                $message.= " " . $this->_translate->_('Success') . "(ID) : " . implode(', ', $trueCopy) . " .";
            }
            $message.= " " . $this->_translate->_('Failed') . " : ";
            foreach ($falseCopy as $unitID => $errorMessage) {
                $message.= $unitID . "(ID) - " . $errorMessage;
            }
        }

        return array('data' => array(
                'success' => $success,
                'message' => $message
        ));
    }

    public function deleteJsonAction() {
        $dao = new RM_Units();
        $unitIDs = $this->_getParam('ids', array());

        foreach ($unitIDs as $uid) {
            $unit = $dao->find($uid)->current();
            if ($unit !== null) {
                $dao->deleteRow($unit);
            }
        }

        return array(
            'data' => array('success' => true)
        );
    }

    public function newJsonAction() {


        // create the search criteria object
        $criteria = new RM_Unit_Search_Criteria(array(), true);

        // get the total number of units already added to the system.
        $dao = new RM_Units;

        // TODO: we need to hide these queries if possible.
        //$unitTypesDAO = new RM_UnitTypes();
        //$criteria->type = $unitTypesDAO->find($this->_defaultUnitType)->current();

        $total = (int) $dao->getAll($criteria)->count();

        $iso = $this->_getParam('iso', RM_Environment::getInstance()->getLocale());
        $config = new RM_UnitConfig();
        $fields = $config->getNewForm($this->_defaultUnitType);

        foreach ($fields as $field) {
            $jsonFields[] = $field->view_preferences;
        }

        $json = "{ fields : [" . implode(',', $jsonFields) . "], language: '" . $iso . "'}";
        return array(
            'data' => $json,
            'encoded' => true
        );
    }

    public function insertJsonAction() {

        $unit = $this->_getParam('new_unit');
        $unit['color'] = ltrim($this->_getParam('color'),"#");

        if (!isset($unit['description'])){
            $unit['description'] = "";
        }

        $dao = new RM_Units();
        $id = $dao->insert(
            $unit,
            $unit['iso'],
            true
        );

        return array(
            'data' => array('success' => true, 'id' => $id)
        );
    }

    /**
     * @param   request id  unit id
     * @see unit_calendar.js
     * @return 	json    json array information in format:
     * [{start_date: <name>, end_date: <value>}, ...]
     */
    function editcalendarJsonAction() {
        $unitID = $this->_getParam('unit_id', '');
        $unitModel = new RM_Units();
        $unit = $unitModel->get($unitID);

        $filters = array();
        $reservationModel = new RM_Reservations();
        $reservations = $reservationModel->fetchAllByUnit($unit, $filters);

        $jsonDisabledPeriods = array();
        foreach ($reservations as $period) {
            $jsonPeriod = new stdClass;
            $jsonPeriod->start_date = $period->start_datetime;
            $jsonPeriod->end_date = $period->end_datetime;
            $jsonPeriod->text = $this->_translate->_('User.HourlyPrices.Datepicker', 'Reserved');
            $jsonPeriod->_text_style = 'background-color: #' . $unit->color . ';';
            $jsonDisabledPeriods[] = clone $jsonPeriod;
        }

        return array(
            'data' => Zend_Json::encode($jsonDisabledPeriods),
            'encoded' => true
        );
    }

    public function editJsonAction() {
        $json = new stdClass;

        $id = $this->_getParam('id');
        $iso = $this->_getParam('iso', RM_Environment::getInstance()->getLocale());

        $unitModel = new RM_Units();
        $unit = $unitModel->get($id, $iso);

        $config = new RM_UnitConfig();
        $fields = $config->getEditFormByUnit($unit);

        $config = new RM_Config();

        // view_preferences_1 provides non html editors, just raw editors
        foreach ($fields as $field) {
            if ($config->getValue('rm_config_editor') == "text" && $field->view_preferences_1 !== ""){
                $jsonFields[] = $field->view_preferences_1;
            } else {
                $jsonFields[] = $field->view_preferences;
            }
        }

        $reservationModel = new RM_Reservations();
        $reservations = $reservationModel->fetchAllByUnit($unit);

        $jsonReservations = array();
        

        /*
         * the reservation information required to add events to the calendar must include
         * the start and end date but also the unit color.
         */
        foreach ($reservations as $reservation) {
            $jsonReservation = new stdClass;
            $jsonReservation->start_date = $config->convertDates($reservation->start_datetime, RM_Config::MYSQL_DATEFORMAT, RM_Config::MYSQL_DATEFORMAT_SHORT);
            $jsonReservation->end_date = $config->convertDates($reservation->end_datetime, RM_Config::MYSQL_DATEFORMAT, RM_Config::MYSQL_DATEFORMAT_SHORT);
            $jsonReservation->color = $unit->color; // unit color
            $jsonReservations[] = $jsonReservation;
        }

        $priceSystems = RM_Environment::getInstance()->getPriceSystem()->getAllPriceSystems();
        $jsonPriceSystems = array();
        foreach ($priceSystems as $system) {
            $jsonPriceSystems[] = $system->name;
        }

        $priceSystem = RM_Environment::getInstance()->getPriceSystem()->getRealPriceSystem($unit);

        // group handling (only used when the groups is enabled)
        $isGroupTemplate = 0;
        if ($unit->isTemplateUnit() === (int) $unit->id) {
            $isGroupTemplate = 1;
        } elseif ($unit->isTemplateUnit() === null || $unit->isTemplateUnit() === 0) {
            // if this unit is not in a group then we set the isGroupTemplate true
            // as this is really the same as a template for the GUI
            $isGroupTemplate = 1;
        }

        $json = "{ unit : " . Zend_Json::encode($unitModel->convertToGUI($unit->toArray())) . ", isgrouptemplate: '" . $isGroupTemplate . "', fields : [" . implode(',', $jsonFields) . "], periods: " . Zend_Json::encode($jsonReservations) . ", language: '" . $iso . "', price: '" . $priceSystem->name . "', prices: " . Zend_Json::encode($jsonPriceSystems) . "}";
        return array(
            'data' => $json,
            'encoded' => true
        );
    }

    public function updateJsonAction() {
        $unit = $this->_getParam('edit_unit');
        $unit['color'] = ltrim($this->_getParam('color'),"#");

        $dao = new RM_Units();

        // update the unit record
        $dao->updateUnit($unit);

        if (isset($unit['group_id'])){
            // make sure that sub units are converted to the same unit type
            if (class_exists(RM_Groups)){
                $groups = new RM_Groups();

                // get the unit object
                $unitObject = $dao->get($unit['id'], null, array('description','summary'));

                if (!$groups->isMain($unitObject)){
                    // if it's not a main unit, then update the unit to the same unit type as the main unit

                    $allGroupedUnits = $groups->getGroupUnitsByMain($unitObject);

                    // find the main unit id...
                    foreach($allGroupedUnits as $groupUnit){
                        if ($groups->isMain($groupUnit)){
                            $mainUnitID = $groupUnit->id;
                        }
                    }

                    // get the main unit object
                    $mainUnitObject = $dao->get($mainUnitID, null, array('description','summary'));

                    // get the type for the main unit
                    $rmUnitTypes = new RM_UnitTypes();
                    $typeInfo = $rmUnitTypes->getByUnit($mainUnitObject)->toArray();

                    // update the subunit to the same type
                    $unit['type_id'] = (int)$typeInfo['id'];
                    $dao->updateUnit($unit);
                }
            }
        }

        return array(
            'data' => array('success' => true, 'msg' => '')
        );
    }

}