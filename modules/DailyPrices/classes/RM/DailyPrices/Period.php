<?php
/**
 * Enum class for all available minimum periods for reservations
 *
 * @access 	public
 * @author 	Valentin
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link		http://developer.resmania.com/api
 * @since  	05-2009
 */
class RM_DailyPrices_Period {
    const flexible = 0;
    const night_1 = 1;
    const nights_2 = 2;
    const nights_3 = 3;
    const nights_4 = 4;
    const nights_5 = 5;
    const nights_6 = 6;
    const week_1 = 7;
    const weeks_2 = 14;
    const weeks_3 = 21;
    const month_1 = 30;
    const months_2 = 60;
    const months_3 = 90;

    public static $periods = array(
    	self::flexible => 'Flexible',
    	self::night_1 => 'OneNight',
    	self::nights_2 => 'TwoNights',
    	self::nights_3 => 'ThreeNights',
    	self::nights_4 => 'FourNights',
    	self::nights_5 => 'FiveNights',
    	self::nights_6 => 'SixNights',
    	self::week_1 => 'OneWeek',
    	self::weeks_2 => 'TwoWeeks',
    	self::weeks_3 => 'ThreeWeeks',
    	self::month_1 => 'OneMonth',
    	self::months_2 => 'TwoMonths',
    	self::months_3 => 'ThreeMonths'
    );

    const languageSection = 'Admin.DailyPrices.Periods';

    /**
     * Returns all translated periods.
     *
     * @param Zend_Translate $translate OPTIONAL If don't provide method will use default one from Zend_Registry
     * @return array
     */
    public static function getAllTranslated($translate = null)
    {
        if ($translate == null) {
            $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
        }
        $section = self::languageSection;
        $result = array();

        foreach (self::$periods as $id => $name){
            $result[$id] = $translate->_($section, $name);
        }
        return $result;
    }    

    /**
     * Return period name (need to be translated using )
     *
     * @param Int $key perido key
     * @return String Thranslate constant
     */
    public static function getPeriodName($key)
    {
    	return self::$periods[$key];
    }
}