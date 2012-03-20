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
class RM_UnitDailyPricesConfig extends RM_Model
{
    protected $_name = 'rm_unit_daily_prices_config';

    /**
     * Delete all unit price config preferences
     *
     * @param int $unitID
     * @return int          The number of rows deleted.
     */
    function deleteByUnit($unitID)
    {
        return $this->delete("unit_id='$unitID'");
    }


    /**
     * Returns rows that are EXACTLY in rm_unit_daily_prices_config table assigned for input unit
     *
     * @param RM_Unit_Row $unit
     * @return void
     */
    function getByUnit(RM_Unit_Row $unit)
    {
        return $this->fetchAll($this->select()->where('unit_id=?', $unit->id));
    }

    /**
     * Return all information about unit price config, if there is no value
     * for some field it will return the default one
     *
     * @param $unitID Int - unit primary key value
     */
    function fetchByUnit($unitID)
    {
        $sql = "
            SELECT                
                pc.name as name,
                pc.value as default_value,
                pc.metainfo as metainfo,
                upc.value as unit_value,
                '$unitID' as unit_id
            FROM
                rm_daily_prices_config pc
            LEFT OUTER JOIN
                rm_unit_daily_prices_config upc ON upc.name=pc.name AND upc.unit_id=$unitID
        ";

        $result = $this->_getBySQL($sql);
        return $result;
    }

    /**
     * Fetch units by config value
     */
    function fetchUnitsByValue($value){
        $sql = "
            SELECT
                unit_id, value
            FROM
                rm_unit_daily_prices_config
            WHERE
                name='$value'
        ";
        $result = $this->_getBySQL($sql);
        return $result;
    }

    /**
     * Returns unit config value by name.
     *
     * @param int $unitID - unit primary key
     * @param string $name - config name
     * @return mixed - if unit value set, returns unit value. Otherwise default value.
     */
    function fetchValueByUnit($unitID, $name)
    {
        $sql = "
            SELECT                
                pc.value as default_value,
                upc.value as unit_value
            FROM
                rm_daily_prices_config pc
            LEFT OUTER JOIN
                rm_unit_daily_prices_config upc ON upc.name=pc.name AND upc.unit_id=$unitID
            WHERE
                pc.name='$name'
        ";

        $result = $this->_getBySQL($sql)->current();
        if (isset($result->unit_value) && $result->unit_value !== null) {
            return $result->unit_value;
        } elseif (isset($result->default_value)) {
            return $result->default_value;
        }
    }

    /**
	 * Check is input day is weekend.
	 *
     * @param Zend_Db_Table_Row
	 * @param RM_Date $day
	 * @return bool
	 */
	public function isWeekend($unit, $day){
        $dayNumber = $day->getWeekday();     
        $weekend = $this->fetchValueByUnit($unit->id, 'weekend_days');
        return RM_DailyPricesConfig::isWeekend($weekend, $dayNumber);
	}
}
