<?php
/**
 * Enum class for all available midweek options
 *
 * @access 	public
 * @author 	Rob
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.2
 * @link
 * @since  	05-2009
 */
class RM_DailyPrices_Midweek {

    /**
     *
     * @param array $midweek
     * @param string $weekDay
     * @return bool
     */
    static function isMidWeek($midweek, $weekDay)
    {
        if (in_array($weekDay, $midweek)){
            return true;
        }
        return false;
    }
}
