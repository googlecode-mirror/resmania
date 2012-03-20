<?php
/**
 * Model class
 *
 * @access 	public
 * @author 	Valentin
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @deprecated
 * @link		http://developer.resmania.com/api
 * @since  	05-2009
 */
class RM_UnitDailyPricesColumns extends RM_Model
{
    protected $_name = 'rm_unit_daily_prices_columns';

    /**
     * Returns all enabled columns for the unit
     *
     * @param Zend_Db_Table_Row $unit
     * @return Zend_Db_Table_Rowset
     */
    function getByUnit($unit)
    {
        $sql = "SELECT
            pc.*
        FROM
            {$this->_name} upc
        INNER JOIN
            rm_daily_prices_columns pc ON upc.name=pc.name
        WHERE
            upc.unit_id='{$unit->id}' AND upc.enabled='1'
        ORDER BY pc.order
        ";

        return $this->_getBySQL($sql);
    }
}