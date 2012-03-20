<?php

/**
 * Class for handle all information aboult price caculation.
 *
 * @access 	public
 * @author 	Valentin
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link		http://developer.resmania.com/api
 * @since  	05-2009
 */
class RM_DailyPrices_Calculator {

    /**
     * @var RM_UnitDailyPrices
     */
    protected $_unitPricesModel;
    /**
     * @var RM_UnitDailyPricesConfig
     */
    protected $_unitPricesConfigModel;
    /**
     * @var Zend_Translate
     */
    protected $_translate;

    /**
     * Constructor
     *
     * @param RM_UnitDailyPrices $unitPricesModel
     * @param RM_UnitDailyPricesConfig $unitPricesConfigModel
     * @param Zend_Translate $translate
     */
    public function __construct($unitPricesModel, $unitPricesConfigModel, $translate) {
        $this->_unitPricesModel = $unitPricesModel;
        $this->_unitPricesConfigModel = $unitPricesConfigModel;
        $this->_translate = $translate;
    }

    /**
     * Calculate total price for one unit
     *
     * @param RM_Prices_Information $information
     * @param bool $byStep - if true, the result will be in array (0 => array('step' => RM_Date, 'price' => float))
     */
    public function getTotalUnitPrice($information, $byStep = false) {
        $days = $information->getPeriod()->getDays();

        $total = 0;
        $totalDays = array();

        $daysNumber = count($days);
        foreach ($days as $day) {
            $price = $this->getDayPrice(
                            $day,
                            $daysNumber,
                            $information->getUnit(),
                            $information->getPersons(),
                            $information->getPeriod()->getStart()
            );

            if ($byStep) {
                $totalDays[] = array('step' => clone $day, 'price' => RM_Environment::getInstance()->roundPrice($price));
            } else {
                $total += $price;
            }
        }

        if ($byStep) {
            return $totalDays;
        } else {
            return RM_Environment::getInstance()->roundPrice($total);
        }
    }

    /**
     * Return price for one day. Based on weekday rate or weekend rate or week price (substracting by 7)
     *
     * @param RM_Date $day
     * @param int $daysNumber Total number of days for reservation
     * @param Zend_Db_Table_Row_Abstract $unit Unit that belong to reservation
     * @param int $persons Number of persons that will live in unit
     * @param date $startdatetime   the startdate of the reservation (date object)
     * @return float
     */
    public function getDayPrice($day, $daysNumber, $unit, $persons, $startdatetime = null) {
        $model = $this->_unitPricesModel;
        $priceRow = $model->getByDay($unit, $day, $persons);
        if ($priceRow == null) {
            return 0;
        }

        // min stay == max stay
        if ((int)$priceRow->min_stay !==0 && (int)$priceRow->max_stay !==0 && ((int)$priceRow->min_stay === (int)$priceRow->max_stay)){
            if ($daysNumber > (int)$priceRow->max_stay || $daysNumber < (int)$priceRow->min_stay){
                $msg = str_replace("[XX]", $priceRow->max_stay, $this->_translate->_('RM.Module.DailyPrices', 'DaysRetrictedTo'));
                throw new RM_Exception($msg);
            }
        }

        // min stay check
        if ($daysNumber < (int)$priceRow->min_stay) {
            $msg = str_replace("[XX]", $priceRow->min_stay, $this->_translate->_('RM.Module.DailyPrices', 'DaysNumberIsSmallerThanMin'));
            throw new RM_Exception($msg);
        }

        // max stay check
        if ((int)$priceRow->max_stay !== 0 && (((int)$priceRow->max_stay >= (int)$priceRow->min_stay)  && ($daysNumber > (int)$priceRow->max_stay))) {
            $msg = str_replace("[XX]", $priceRow->max_stay, $this->_translate->_('RM.Module.DailyPrices', 'DaysNumberIsGreaterThanMax'));
            throw new RM_Exception($msg);
        }

        // period restrictions
        if ((int)$priceRow->period_restriction !== 0 && (!is_int($daysNumber/((int)$priceRow->period_restriction)))) {
            $msg = str_replace("[XX]", $priceRow->period_restriction, $this->_translate->_('RM.Module.DailyPrices', 'PeriodRestrictionMsg'));
            throw new RM_Exception($msg);
        }

        // handle if an object or string is passed for the date.
        if (is_object($startdatetime)) {
            $selectedStartDay = date("w", $startdatetime->toString("U"));
        } else {
            $selectedStartDay = date("w", strtotime($startdatetime));
        }
        // $priceRow->week_startday = -1 means that weekday restriction is disabled
        if ($priceRow->week_startday != -1) {
            
            // get the day names into an array
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
            $dayJson = $translate->_('Common.JSON', 'WeekDaysLongNumbered');
            $dayJson = str_replace(chr(39), chr(34), $dayJson); //Zend_Json::decode does not support single quotes so replace these with doubles
            $daysArray = Zend_Json::decode($dayJson);

            if ($priceRow->week_startday1 != -1) {

                if ($priceRow->week_startday == $selectedStartDay || $priceRow->week_startday1 == $selectedStartDay){
                    // no code here
                } else {
                    $msg = str_replace("[DAY]", $daysArray[$priceRow->week_startday][1].'/'.$daysArray[$priceRow->week_startday1][1], $this->_translate->_('RM.Module.DailyPrices', 'StartDayNotAllowed'));
                    throw new RM_Exception($msg);
                } 
            } else {
                if ($priceRow->week_startday != $selectedStartDay ) {
                    $msg = str_replace("[DAY]", $daysArray[$priceRow->week_startday][1], $this->_translate->_('RM.Module.DailyPrices', 'StartDayNotAllowed'));
                    throw new RM_Exception($msg);
                }
            }
        }

        // end day restriction...
        // $priceRow->week_startday = -1 means that weekday restriction is disabled
        if ($priceRow->week_endday != -1) {

            $rmDate = new RM_Date();
            $enddatetime = $rmDate->dateAdd(date("Y-m-d",$startdatetime->toString("U")), $daysNumber);
            $selectedEndDay = date("w", strtotime($enddatetime));

            // get the day names into an array
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
            $dayJson = $translate->_('Common.JSON', 'WeekDaysLongNumbered');
            $dayJson = str_replace(chr(39), chr(34), $dayJson); //Zend_Json::decode does not support single quotes so replace these with doubles
            $daysArray = Zend_Json::decode($dayJson);

            if ($priceRow->week_endday1 != -1) {

                if ($priceRow->week_endday == $selectedEndDay || $priceRow->week_endday1 == $selectedEndDay){
                    // no code here
                } else {
                    $msg = str_replace("[DAY]", $daysArray[$priceRow->week_endday][1].'/'.$daysArray[$priceRow->week_endday1][1], $this->_translate->_('RM.Module.DailyPrices', 'EndDayNotAllowed'));
                    throw new RM_Exception($msg);
                }
            } else {
                if ($priceRow->week_endday != $selectedEndDay ) {
                    $msg = str_replace("[DAY]", $daysArray[$priceRow->week_endday][1], $this->_translate->_('RM.Module.DailyPrices', 'EndDayNotAllowed'));
                    throw new RM_Exception($msg);
                }
            }
        }


        // get config values
        $priceConfigModel = $this->_unitPricesConfigModel;
        $weekendPriceEnabled = (int)$priceConfigModel->fetchValueByUnit($unit->id, 'weekend_price_enabled');
        $dailyPriceEnabled = (int)$priceConfigModel->fetchValueByUnit($unit->id, 'daily_price_enabled');
        $monthlyPriceEnabled = (int)$priceConfigModel->fetchValueByUnit($unit->id, 'monthly_price_enabled');
        $midweekDays = explode(",",$priceConfigModel->fetchValueByUnit($unit->id, 'midweek_days'));
        $weekendDays = explode(",",$priceConfigModel->fetchValueByUnit($unit->id, 'weekend_days'));

        // perform the calculation rules...

        // the rules are cascaded starting from weekly price to daily...
        
        // if month or above...
        if ($monthlyPriceEnabled === 1 && $daysNumber >= 30) {
            $dayrate = $priceRow->monthly_rate / 30;
            return $dayrate;
        }

        // if weekly or over
        if ($daysNumber >= 7) {
            $dayrate = $priceRow->weekly_rate / 7;
            return $dayrate;
        }

        // if weekend
        if ($weekendPriceEnabled === 1 && RM_DailyPrices_Weekend::isWeekend($weekendDays, $day->toString('w'))) {
            return $priceRow->weekend_price;
        }

        // if midweek days
        if ($midweekDays !== 0 && RM_DailyPrices_Midweek::isMidWeek($midweekDays, $day->toString('w'))) {
            return $priceRow->midweek_price;
        }

        // if daily
        if ($dailyPriceEnabled){
            return $priceRow->weekday_price;
        }


        // fallback to weekly price if no other rule have caught this
        return $priceRow->weekly_rate / 7;
        
    }

}