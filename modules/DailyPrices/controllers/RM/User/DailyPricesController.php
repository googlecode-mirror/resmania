<?php
/**
* User Prices Controller
*
* This handles all AJAX requests from the User GUI.
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
class RM_User_DailyPricesController extends RM_Controller
{

    /**
     * gets prices from for a specified unit.
     *
     * @param   request id  unit id
     * @return 	json    json array information in format:
     */
    function getpricesfromAction(){
        $uid = $this->_getParam('id');
        $unitsDAO = new RM_Units();
        
        $taxSystem = RM_Environment::getInstance()->getTaxSystem();
        $tax = 0;

        $unitObj = $unitsDAO->get($uid);

        $stardateObj = new RM_Date(strtotime(date("Y-m-d"))); // start date of now

        // get the last price date entered....
        $unitPrices = new RM_UnitDailyPrices;
        $end_datetime = $unitPrices->getLastPriceBandDate($uid);

        $enddateObj = new RM_Date(strtotime($end_datetime));

        $periodObj = new RM_Reservation_Period($stardateObj , $enddateObj);

        $persons = new RM_Reservation_Persons(array("adults"=>1,"children"=>0,"infants"=>0));

        $information = new RM_Prices_Information($unitObj, $periodObj, $persons);

        $priceSystem = RM_Environment::getInstance()->getPriceSystem();
        $subtotal = $priceSystem->getLowestUnitPrice($information);

        // tax to be added...
        $tax = $taxSystem->calculateTotalTax($unitObj, $subtotal);

        $total = $subtotal + $tax;       
    }

    function listallAction(){
        ob_clean();
        $uid = $this->_getParam('uid');

        $unitModel = new RM_Units();
        $language = RM_Environment::getInstance()->getLocale();
        $unit = $unitModel->get($uid, $language);
        
        $priceObj = new RM_UnitDailyPrices;
        $prices = $priceObj->getCurrent($unit)->toArray();
        $this->view->unit = $unit;
        $this->view->prices = $prices;

        echo $this->view->render('DailyPrices/listall.phtml');
        die();
    }

    function datepickerJsAction(){
        return "var RM_DailyPrices_DatepickerWindow = {width: 400, height: 300}; ";
    }

    function datepickerAction(){
        ob_clean();
        $unitID = $this->_getParam('unit_id', null);
        $unitModel = new RM_Units();
        $unit = $unitModel->get($unitID);
        $reservationModel = new RM_Reservations();
        $reservations = $reservationModel->fetchAllForUnitCalendar($unit);
        $config = new RM_Config();
        $RMdate = new RM_Date();

        $jsonDisabledPeriods = new stdClass();
        $jsonDisabledPeriods->start = array();
        $jsonDisabledPeriods->end = array();        
        foreach ($reservations as $period) {
            $jsonPeriod = new stdClass;

            $jsonPeriod->start = $config->convertDates($period->start_datetime, RM_Config::JS_DATEFORMAT, RM_Config::PHP_DATEFORMAT);
            $jsonPeriod->end = $config->convertDates($period->end_datetime, RM_Config::JS_DATEFORMAT, RM_Config::PHP_DATEFORMAT);

            // store the start date picker blocked periods
            $jsonDisabledPeriods->start[] = clone $jsonPeriod;

            $jsonPeriod->start = $RMdate->dateAdd($config->convertDates($period->start_datetime, RM_Config::JS_DATEFORMAT, RM_Config::PHP_DATEFORMAT),1);
            $jsonPeriod->end = $RMdate->dateAdd($config->convertDates($period->end_datetime, RM_Config::JS_DATEFORMAT, RM_Config::PHP_DATEFORMAT),1);
            $jsonDisabledPeriods->end[] = clone $jsonPeriod;
        }

        $json = Zend_Json::encode($jsonDisabledPeriods);
        $this->view->calendardata = $json;
        $this->view->unit_id = $unitID;
        echo $this->view->render('DailyPrices/datepicker.phtml');
        die();
    }
}