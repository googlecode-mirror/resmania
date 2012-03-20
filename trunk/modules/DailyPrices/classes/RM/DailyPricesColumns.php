<?php
/**
 * Model class
 *
 * @access 	public
 * @author 	Valentin
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link		http://developer.resmania.com/api
 * @since  	05-2009
 */
class RM_DailyPricesColumns extends RM_Model
{
    protected $_name = 'rm_daily_prices_columns';

    /**
     * Returns all enabled columns
     *
     * @param bool $weekendEnabled
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchAllEnabled($configObject){

        $select = $this->select()->where('enabled=1');
        
        if (!$configObject->dailyEnabled) {
            $select->where('name!=?', "weekday_price");
        }
        if (!$configObject->midweekEnabled) {
            $select->where('name!=?', "midweek_price");
        }
        if (!$configObject->monthlyEnabled) {
            $select->where('name!=?', "monthly_rate");
        }
        if (!$configObject->weekendEnabled) {
            $select->where('name!=?', "weekend_price");
        }


        return $this->fetchAll($select->order("order ASC"));
    }
}