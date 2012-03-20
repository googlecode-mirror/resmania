<?php
/**
 * Enum class for all available weekend options
 *
 * @access 	public
 * @author 	Rob
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.2
 * @link        http://docs.resmania.com/api
 * @since  	05-2009
 */
class RM_DailyPrices_Weekend {
    /**
     * Check is the input day is a weekend
     *
     * @param array $weekend Config weekend value
     * @param string $weekDay weekday number
     * @return bool
     */
    static function isWeekend($weekend, $weekDay)
    {
        if (in_array($weekDay, $weekend)){
            return true;
        }
        return false;
    }
}