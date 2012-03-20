<?php
/**
 * Fasade class for the price modules
 *
 * @access 	public
 * @author 	Valentin
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link		http://developer.resmania.com/api
 * @since  	05-2009
 */
class RM_Module_DailyPrices extends RM_Module implements RM_Prices_Interface, RM_Unit_Copy_Interface
{
    function copyInformation(RM_Unit_Row $original, RM_Unit_Row $copy)
    {
        $unitDailyPricesModel = new RM_UnitDailyPrices();
        $unitDailyPrices = $unitDailyPricesModel->getByUnit($original);
        foreach ($unitDailyPrices as $unitDailyPrice) {
            $unitDailyPriceCopyData = $unitDailyPrice->toArray();
            unset($unitDailyPriceCopyData['id']);
            $unitDailyPriceCopyData['unit_id'] = $copy->id;
            $unitDailyPriceCopy = $unitDailyPricesModel->createRow($unitDailyPriceCopyData);
            $unitDailyPriceCopy->save();
        }

        $unitDailyPricesConfigModel = new RM_UnitDailyPricesConfig();
        $unitDailyPricesConfigs = $unitDailyPricesConfigModel->getByUnit($original);
        foreach ($unitDailyPricesConfigs as $unitDailyPricesConfig) {
            $unitDailyPricesConfigCopyData = $unitDailyPricesConfig->toArray();            
            $unitDailyPricesConfigCopyData['unit_id'] = $copy->id;
            $unitDailyPricesConfigCopy = $unitDailyPricesConfigModel->createRow($unitDailyPricesConfigCopyData);
            $unitDailyPricesConfigCopy->save();
        }
    }

    public function  __construct() {
        $this->name = 'DailyPrices';
    }    

    public function getNode(){
       return null;
    }

    /**
     * Returns PHP dateformat to present reservation start and end date on the UI
     *
     * @param bool $php - internal PHP date format or UI date format, if true - internal PHP date format
     * @return string
     */
    public function getDateformat($php = false){
        if ($php) {
            return RM_Config::MYSQL_DATEFORMAT_SHORT;
        }

        $config = new RM_Config();
        return $config->getJSDateformat();
    }

    /**
     * Returns unit that are not that have a min stay on this period more than this period
     * or don't have a prices at all for this period
     * 
     * @param RM_Reservation_Period $period
     * @return array array with unit ids
     */
    public function getByMinPeriod(RM_Reservation_Period $period){
        $unitIDs = array();        
        $model = new RM_UnitDailyPrices();
        $unitIDs = $model->getByMinPeriod($period);
        return $unitIDs;
    }

    /**
     * Calculates total price for one unit
     *
     * @param RM_Prices_Information $information All needed data for calculate total price
     * @param bool $byStep - if true, the result will be in array (0 => array('step' => RM_Date, 'price' => float))
     * @return float Total price
     */
    public function getTotalUnitPrice(RM_Prices_Information $information, $byStep = false)
    {       
        $calculator = new RM_DailyPrices_Calculator(
            new RM_UnitDailyPrices,
            new RM_UnitDailyPricesConfig,
            RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS)
        );
        
        $returnValue = $calculator->getTotalUnitPrice($information, $byStep);
        return $returnValue;
    }


    /**
     * Returns the lowest price of a unit over a given period
     *
     * @param $information RM_Prices_Information - information object,
     * contains all needed information about unit reservation:
     * unit,
     * start date,
     * end date,
     * number of persons
     * etc.
     *
     * @return float
     */
    public function getLowestUnitPrice(RM_Prices_Information $information){
        $model = new RM_UnitDailyPrices();
        return $model->getLowestPrice($information);
    }

    /**
     * Return max prices among all units for the one default period for price manager
     * @return float
     */
    public function getTotalHighestPrice(){
        $model = new RM_UnitDailyPrices();
        return $model->getHighestPrice();
    }

    /**
     * Returns all unit ids that have price in price range in input period
     *
     * @param float $from
     * @param float $to
     * @param RM_Reservation_Period $period
     * @return array array with unit ids
     */
    public function getByPriceRange($from, $to, RM_Reservation_Period $period = null){
        $model = new RM_UnitDailyPrices();
        $rows = $model->getByPriceRange($from, $to, $period);

        $unitIDs = array();
        foreach ($rows as $row) {
            $unitIDs[] = $row->unit_id;
        }
        return $unitIDs;
    }

    public function getUnitWithAvailabiltyCheckDisabled(){
        $model = new RM_UnitDailyPricesConfig();
        $rows = $model->fetchUnitsByValue('availablity_check');

        $unitIDs = array();
        foreach ($rows as $row) {
            if ($row->value=="0"){
                $unitIDs[] = (array("id"=>$row->unit_id));
            }
        }
        return $unitIDs;
    }

    /**
     * this method returns if the times should be collected for this price module
     *
     * @return boolean
     */
    public function getShowTime(){
        return 0;
    }

    /**
     * Returns the other information associated with this price system.
     *
     * @return array
     */
    public function getOtherInfo(){
        return null;
    }

    /**
     * this method returns a default value for people maximums (used in hospitality and other modules)
     *
     * @return boolean
     */
    public function getPeopleMaximums(){
        return 1;
    }

    /**
     * this method returns a default value for board types
     *
     * @return null 
     */
    public function getBoardTypes(){
        return null;
    }
}