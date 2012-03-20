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
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_User_UnitController extends RM_User_Controller
{
    static function getDefaultOrder()
    {
        $config = new RM_Config();
        return $config->getValue('rm_config_unit_list_order');
    }

    static function getOrders()
    {
        $orders = RM_Environment::getInstance()->getTranslation()->_('Common.JSON', 'Order');
        $orders = str_replace(chr(39), chr(34), $orders);
        $phpOrders = Zend_Json::decode($orders);
        return $phpOrders;
    }

    /**
     * @param RM_Unit_Search_Criteria $criteria
     * @param Zend_Db_Table_Rowset $units
     * @return Zend_Db_Table_Rowset
     */
    protected function _sortUnitsByPrice(RM_Unit_Search_Criteria $criteria, Zend_Db_Table_Rowset $units)
    {
        $sorter = new RM_Unit_Sorter($criteria, $units);
        $sorter->sort();
        return $sorter->getResult();
    }

    /**
     * ListAction is the main search routine used to list units. Params are sent from the
     * search forms and either submitted data is used or the saved criteria.
     *
     * @param array search  passed in post/get
     * @return  objects to view
     */
    function listAction()
    {

        // reset the returnAllUnits if it's set
        unset($_SESSION["returnAllUnits"]);

        // get the submitted search data
        $data = $this->_getParam('search', array());

        // get the category if set explicitly
        $category = $this->_getParam('category', ""); // if category is not passed then set it to ""

        // if the category data sent in the request has data then add the category to the data array for use to create the criteria
        if ($category !== "true"){
            $data['categories'] = $category;
        }

        // if we are not re-ordering and have search data define a new search criteria
        if (!isset($data['reorder']) && !empty($data)){
            // create a new criteria object
            $criteria = new RM_Unit_Search_Criteria($data);
            // reset the old criteria and save our new criteria
            RM_Reservation_Manager::getInstance()->resetCriteria()->setCriteria($criteria);


        } else {
            $criteria = RM_Reservation_Manager::getInstance()->getCriteria();
        }

        // perform a check to make sure our criteria object is the correct instance
        // if it's not it will cause errors, so we check it and if it isn't we create
        // it as the correct object
        if (!$criteria instanceof RM_Unit_Search_Criteria) {
            $criteria = new RM_Unit_Search_Criteria($data);
        }

        // if the category passed in the request is empty and the criteria has a category set then blank it.
        if ($category === "" && $criteria->categories){
            $criteria->categories = null;
        }


        // only show published units
        $criteria->publishedOnly = true;
        $criteria->groupsOnly = false;

        // define the default page ordering if no page ordering is passed
        if (!$criteria->order){
             $criteria->order = RM_User_UnitController::getDefaultOrder();
        }

        // if page re-ordering is called add this to the criteria and save it
        if (isset($data['reorder'])){

            // if we are just re-odering the list get the saved criteria and use this.
            if (isset($data['order'])){
                $criteria->order = $data['order'];

                // save the ordering to the criteria
                RM_Reservation_Manager::getInstance()->resetCriteria()->setCriteria($criteria);
            }

        }

        // get the default page lengths for the view
        $defaulCountPerPage = RM_Environment::getConnector()->getDefaultUnitListLength();

        $unitModel = new RM_Units();
        $units = $unitModel->getAll($criteria);

        if ($criteria->order == 'price') {
            $units = $this->_sortUnitsByPrice($criteria, $units);
        }

        $formModel = new RM_Forms();
        $form = $formModel->find('unitlist')->current();
        $this->view->form = $form;

        $this->view->criteria = $criteria;

        // get date formatting for the view
        $config = new RM_Config();
        $dateFormat = $config->getJSDateformat();

        if ($dateFormat=="d/m/Y"){
            $lng_dateFormat = "dd/mm/yyyy";
        } else {
            $lng_dateFormat = "mm/dd/yyyy";
        }
        $this->view->dateformat_short = $dateFormat;
        $this->view->dateformat_long = $lng_dateFormat;

        // criteria list header message
        $message = false;
        if ($criteria->start_datetime !== null && $criteria->end_datetime !== null && $criteria->start_datetime !== "" && $criteria->end_datetime !== ""){
            $message.=date($dateFormat, strtotime($criteria->start_datetime))." - ".date($dateFormat, strtotime($criteria->end_datetime)).", ";
        }
        if ((int)$criteria->image === 1) { $message.=$this->_translate->_('User.Unit.List', 'criteriaImage').", ";}
        if ((int)$criteria->flexible === 1) { $message.=$this->_translate->_('User.Unit.List', 'criteriaFlexible').", ";}
        if ((int)$criteria->map === 1) { $message.=$this->_translate->_('User.Unit.List', 'criteriaMap').", ";}
        if ((int)$criteria->prices_from !== 0) { $message.=$this->_translate->_('User.Unit.List', 'criteriaPricesFrom').": ".$criteria->prices_from.", ";}
        if ($criteria->prices_to != "99999999" && $criteria->prices_to) { $message.=$this->_translate->_('User.Unit.List', 'criteriaPricesTo').": ".$criteria->prices_to.", ";}
        if ((int)$criteria->adults >1 ) { $message.=$this->_translate->_('User.Unit.List', 'criteriaAdults').": ".$criteria->adults.", ";}
        if ((int)$criteria->children >0 ) { $message.=$this->_translate->_('User.Unit.List', 'criteriaChildren').": ".$criteria->children.", ";}
        if ((int)$criteria->infants >0) { $message.=$this->_translate->_('User.Unit.List', 'criteriaInfants').": ".$criteria->infants.", ";}

        $this->view->criteriaMessage = rtrim($message,", ");


        $paginator = Zend_Paginator::factory($units);
        $paginator->setDefaultItemCountPerPage($defaulCountPerPage);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $this->view->paginator = $paginator;
    }

    function detailsAction(){

        // reset the returnAllUnits if it's set
        unset($_SESSION["returnAllUnits"]);

        $unitID = $this->_getParam('unit_id', null);
        $isGroup = $this->_getParam('isGroup', 0);

        if ($unitID == null) {
            $this->_redirect('Unit', 'list');
            return;
        }

        $unitModel = new RM_Units();
        $language = RM_Environment::getInstance()->getLocale();
        $availableSubUnits = array();
        $mainFound = false;

        // if this is a group we need to get an available unit from the 'pool'
        if ($isGroup){
            $masterUnit = $unitModel->get($unitID, $language);

            $qty = $this->_getParam('qty', 1); // define how many subunits to pick
            if (!isset($qty) || $qty == 0){ $qty = 1; }

            $criteria = RM_Reservation_Manager::getInstance()->getCriteria();

            if (!$criteria){
                $criteria = new RM_Unit_Search_Criteria();
            }

            if ($criteria->showOptionUnits === "1" || $criteria->showExcursionUnits === "1"){
                $criteria = new RM_Unit_Search_Criteria();
            }

            $groupsModel = new RM_Groups();

            // get available sub units
            $AllavailableSubUnits = $masterUnit->getAllSubUnits($criteria, $masterUnit);

            // the groups module will return a free unit using the method above
            // this means if there are no subunits it will return the available
            // main unit. This causes us a problem as if the unit returned is not
            // the main unit then we need to + one to the available units as the
            // main unit is also available for reservation.
            foreach($AllavailableSubUnits as $subunit){
                if ($subunit->getGroupId() === $masterUnit->getGroupId()){
                    if ($groupsModel->isMain($subunit)){
                        // if it's the main unit set a flag
                        $mainFound = true;
                    }
                    $availableSubUnits[] = $subunit;
                }
            }
            $unit = $masterUnit;

            if (count($availableSubUnits)<$qty){
                $availableSubUnits[] = $masterUnit;
            }


        } else {
            $unit = $unitModel->get($unitID, $language);
        }

        if ($unit == null) {
            $this->_redirect('Unit', 'list');
            return;
        }

        $qtyAvailable = count($availableSubUnits);

        // if the main unit was not found add one to the quantity
        // total as the main unit + the sub unit is available.
        if (!$mainFound){
            $qtyAvailable +=1;
        }

        $this->view->unit = $unit;
        $this->view->subunits = $availableSubUnits;
        $this->view->quantity = $qtyAvailable;


        // set the page title
        RM_Environment::getConnector()->setPageTitle($unit->name);

        $formModel = new RM_Forms();
        $form = $formModel->find('unitdetails')->current();
        $this->view->form = $form;
        $this->view->state = $form->getState($unit->type_id);

        $unitTypeModel = new RM_UnitTypes();
        $unitType = $unitTypeModel->find($unit->type_id)->current();

        $unitTypeFormsModel = new RM_UnitTypeForms();
        $this->view->unitTypeForm = $unitTypeFormsModel->fetchBy($form, $unitType);
    }

    /**
     * Quantity Check, this returns the number of available units
     *
     * @param none
     * @return json
     */
    function quantitycheckJsonAction(){

        $unitID = $this->_getParam('unit_id', null);
        $startdate = $this->_getParam('startdate');
        $enddate = $this->_getParam('enddate');
        $qty = $this->_getParam('quantity', 1);

        $unitModel = new RM_Units();
        $language = RM_Environment::getInstance()->getLocale();
        $availableSubUnits = array();
        $mainFound = false;

        $masterUnit = $unitModel->get($unitID, $language);

        $data = array();
        $data['start_datetime'] = new RM_Date(strtotime($startdate));
        $data['end_datetime'] = new RM_Date(strtotime($enddate));
        $data->adults = $this->_getParam('adults', 1);
        $data->children = $this->_getParam('children', 0);
        $data->infants = $this->_getParam('infants', 0);
        $data->otherinfo = $this->_getParam("otherInfo", array());

        $criteria = new RM_Unit_Search_Criteria($data);
        $groupsModel = new RM_Groups();

        $AllavailableSubUnits = $masterUnit->getAllSubUnits($criteria, $masterUnit);

        foreach($AllavailableSubUnits as $subunit){
            if ($subunit->getGroupId() === $masterUnit->getGroupId()){
                if ($groupsModel->isMain($subunit)){
                    // if it's the main unit set a flag
                    $mainFound = true;
                }
                $availableSubUnits[] = $subunit;
            }
        }

        if (count($availableSubUnits)<$qty){
            $availableSubUnits[] = $masterUnit;
        }

        $qtyAvailable = count($availableSubUnits);

        // if the main unit was not found add one to the quantity
        // total as the main unit + the sub unit is available.
        if (!$mainFound){
            $qtyAvailable +=1;
        }

        return array(
            'data' => array('success' => true, "qty"=>$qtyAvailable)
        );

        
    }


    /**
     * Action for validating unit details form parameters.
     * If some of the parameters are invalid this method will redirect user to previous page with error
     * text messages about every wrong parameter.
     * If all unit detail information is valid this method will save unit information into global
     * reservation manager object and will redirect user to the next step of the reservation process.
     */
    function detailsvalidateAction()
    {
        $this->_withoutView();

        $unitID = $this->_getParam('unit_id', null);
        if ($unitID == null) {
            $this->_redirect('Unit', 'list');
            return;
        }

        // this is the subunits if groups is being implemented
        $selectedUnitIds = json_decode($this->_getParam('selected_unit_ids', "[]"));
        $quantity = $this->_getParam('quantity', 1);

        $formModel = new RM_Forms();
        $form = $formModel->find('unitdetails')->current();

        $valid = $form->validate($this->getRequest());
        if ($valid == false) {
            RM_Reservation_Manager::getInstance()
                ->setFormErrors('unitdetails', $form->getErrors())
                ->save();
            $this->_redirect('Unit', 'details', array('unit_id' => $unitID));
        }

        //We have a priority to use a user selected dates on the page, not from criteria
        if ($this->_request->getParam('rm_calendar_dates', null) != null){
            // get the dates from the calendar selection
            $datesString = $this->_request->getParam('rm_calendar_dates');
            $dates = explode(',', $datesString);
            $startDateMySQL = $dates[0];
            $endDateMySQL = $dates[count($dates) - 1];

            $adults = $this->_getParam("adults",1);
            $children = $this->_getParam("children",0);
            $infants = $this->_getParam("infants",0);
            $persons = new RM_Reservation_Persons(array("adults"=>$adults,"children"=>$children,"infants"=>$infants));

        } else {
            $criteria = RM_Reservation_Manager::getInstance()->getCriteria();
            $startDateMySQL = $criteria->start_datetime;
            $endDateMySQL = $criteria->end_datetime;

            $persons = new RM_Reservation_Persons(array("adults"=>$criteria->adults,"children"=>$criteria->children,"infants"=>$criteria->infants));
        }

        $period = new RM_Reservation_Period(
            new RM_Date(strtotime($startDateMySQL)),
            new RM_Date(strtotime($endDateMySQL))
        );

        $unitModel = new RM_Units();
        $otherinfo = $this->_getParam("otherInfo", array());
        $manager = RM_Reservation_Manager::getInstance();

        // use a temporary session to pass a value to the groups module init
        $_SESSION["returnAllUnits"] = true;

        // get price...
        $unit = $unitModel->get($unitID, RM_Environment::getInstance()->getLocale(),array("summary","description"));
        $information = new RM_Prices_Information($unit, $period, $persons, $otherinfo);
        $priceSystem = RM_Environment::getInstance()->getPriceSystem();

        try {
            $calculatedTotalPrice = $priceSystem->getTotalUnitPrice($information);
        } catch (RM_Exception $e) {
            RM_Reservation_Manager::getInstance()
                ->setFormErrors('unitdetails', array($e->getMessage()))
                ->save();
            $this->_redirect('Unit', 'details', array('unit_id' => $selectedUnitId));
        }

        $selectedCount = 1;

        // loop through the selected units and save these
        foreach ($selectedUnitIds as $selectedUnitId){

            if ($selectedCount >= $quantity) { break;}
            $selectedUnit = $unitModel->get($selectedUnitId, RM_Environment::getInstance()->getLocale());
            $details = new RM_Reservation_Details($selectedUnit, $period, $persons, $otherinfo, $calculatedTotalPrice);
            $manager->addDetails($details);

            $selectedCount +=1;
        }

        $manager->resetFormErrors('unitdetails')->save();
        $details = $manager->getAllDetails();

        // reset the returnAllUnits if it's set
        unset($_SESSION["returnAllUnits"]);

        $cmsUser = RM_Environment::getConnector()->getUser();
        if ($cmsUser->isGuest() == false) {
            $user = $cmsUser->findResmaniaUser();
            if ($user !== null) {
                RM_Reservation_Manager::getInstance()->setUser($user);
                $this->_redirect('Reservations', 'summary');
            }
        } elseif (RM_Reservation_Manager::getInstance()->getUser() !== null){
            $this->_redirect('Reservations', 'summary');
        }
        $this->_redirect('User', 'userdetails');
    }

}