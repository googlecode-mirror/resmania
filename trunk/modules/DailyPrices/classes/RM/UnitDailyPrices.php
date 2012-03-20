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
class RM_UnitDailyPrices extends RM_Model_Flexible
{
    protected $_name = 'rm_unit_daily_prices';

    protected $_referenceMap    = array(
        'Units' => array(
            'columns'           => 'unit_id',
            'refTableClass'     => 'RM_Units',
            'refColumns'        => 'id'
        ),
    );

    /**
     * Returns all prices for unit
     *
     * @param RM_Unit_Row $unit
     * @param string $order
     * @param int $count
     * @param int $offset
     * @param bool $toGUI OPTIONAL
     * @return array|Zend_Db_Table_Rowset - if $toGUI=true array, else Zend_Db_Table_Rowset
     */
    public function getByUnit($unit, $order = 'id', $count, $offset, $toGUI = false)
    {
        $select = $this->select();
        $select->where("unit_id='{$unit->id}'");
        $select->order($order);

        if ($count !== null){
            $select->limit($count, $offset);
        }

        $result = $this->fetchAll($select);

        if ($toGUI == false) {
            return $result;
        }

        $converted = array();
        foreach ($result as $row) {            
            $converted[] = $row->toArray();
        }
        return $converted;
    }

    /**
     * Get row by day
     *
     * @param $unit
     * @param RM_Date $day
     * @param int $persons
     * @return unknown_type
     */
    public function getByDay($unit, $day, $persons, $otherInfo = null){

    	$datetime = $day->toString('Y-m-d');

    	$sql = "
    		SELECT
    			*
    		FROM
    			$this->_name
    		WHERE
    			number_persons='".$persons->getAdults()."'
    			AND unit_id='$unit->id'
    			AND UNIX_TIMESTAMP(start_datetime) <= UNIX_TIMESTAMP('$datetime')
    			AND UNIX_TIMESTAMP(end_datetime) >= UNIX_TIMESTAMP('$datetime')";

    	return $this->_getBySQL($sql)->current();
    }

    /**
     * Returns unit that are not that have a min stay on this period more than this period
     * or don't have a prices at all for this period
     *
     * @param RM_Date $day
     * @param int $persons
     * @param int $length - period length in days
     * @return array - array with
     */
    private function _getByMinPeriod($day, $persons, $length){
        $datetime = $day->toString('Y-m-d');
    	$sql = "
    		SELECT
    			$this->_name.unit_id as unit_id
    		FROM
    			$this->_name
    		WHERE
    			number_persons='".$persons->getAdults()."'
                AND min_stay > $length
    			AND UNIX_TIMESTAMP(start_datetime) <= UNIX_TIMESTAMP('$datetime')
    			AND UNIX_TIMESTAMP(end_datetime) >= UNIX_TIMESTAMP('$datetime')";

    	$rowsTemp = $this->_getBySQL($sql);
        $notUnitIDs = array();
        foreach ($rowsTemp as $row){
            $notUnitIDs[] = $row->unit_id;
        }
        return $notUnitIDs;
    }

    /**
     * Returns unit that are not that have a min stay on this period more than this period
     * or don't have a prices at all for this period
     * 
     * @param RM_Reservation_Period $period
     * @return array array with unit ids
     */
    public function getByMinPeriod(RM_Reservation_Period $period){
        $days = $period->getDays();
        $persons = new RM_Reservation_Persons(array("adults"=>1,"children"=>0,"infants"=>0));
        $length = count($days);
        $unitIDs = array();
        foreach ($days as $day) {
            $unitIDs[] = $this->_getByMinPeriod($day, $persons, $length);            
        }        
        if (count($unitIDs) == 0) return array();
        $result = $unitIDs[0];
        for ($i = 1; $i < count($unitIDs); $i++){
            if (count($unitIDs[$i]) == 0) return array();
            $result = array_intersect($result, $unitIDs[$i]);
        }
        return $result;
    }

    /**
     * This method will returns all prices that is in date range with daily price in range
     *
     * @param float $from
     * @param float $to
     * @param RM_Reservation_Period $period
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getByPriceRange($from, $to, $period = null){
        $sql = "
    		SELECT
    			*
    		FROM
    			$this->_name
    		WHERE
                weekday_price >= $from AND weekday_price <= $to
        ";

        if ($period !== null) {
            $sql .= " AND
                ((
                    UNIX_TIMESTAMP(start_datetime)<UNIX_TIMESTAMP('".$period->getEnd()->toMySQL()."')
                        AND
                    UNIX_TIMESTAMP(end_datetime)>=UNIX_TIMESTAMP('".$period->getEnd()->toMySQL()."')
                ) OR (
                    UNIX_TIMESTAMP(start_datetime)>UNIX_TIMESTAMP('".$period->getStart()->toMySQL()."')
                        AND
                    UNIX_TIMESTAMP(end_datetime)<=UNIX_TIMESTAMP('".$period->getStart()->toMySQL()."')
                ) OR (
                    UNIX_TIMESTAMP(start_datetime)>UNIX_TIMESTAMP('".$period->getStart()->toMySQL()."')
                        AND
                    UNIX_TIMESTAMP(end_datetime)<=UNIX_TIMESTAMP('".$period->getEnd()->toMySQL()."')
                ))
            ";
        }

    	return $this->_getBySQL($sql);
    }

    /**
     * Get all current prices for a unit.    
     *
     * @param $unit RM_Unit_Row
     * @return Zend_Db_Table_Rowset with results
     */
    public function getCurrent($unit){
        $period = RM_Reservation_Period::getDefault(); // we use default period
        $sql = "
    		SELECT
    			*
    		FROM
    			$this->_name
    		WHERE
    			unit_id='$unit->id'
    			AND UNIX_TIMESTAMP(end_datetime) >= UNIX_TIMESTAMP('".$period->getStart()->toMySQL()."')
                AND UNIX_TIMESTAMP(start_datetime) <= UNIX_TIMESTAMP('".$period->getEnd()->toMySQL()."')
        ";
    	return $this->_getBySQL($sql);
    }

    public function getLastPriceBandDate($unit_id){

        $sql = "SELECT `end_datetime` FROM `rm_unit_daily_prices` WHERE `unit_id`=1 ORDER BY `end_datetime` DESC LIMIT 1";
        return $this->_getBySQL($sql)->current();

    }

    public function getHighestPrice(){
        $sql = "
            SELECT                
                MAX(weekday_price) as greatest
            FROM
                rm_unit_daily_prices";

        $price = $this->_getBySQL($sql)->current()->greatest;
        return $price;
    }

    /**
     * Returns the lowest price of a unit over a given period. If a period don't present than
     * lowest price will be searching for the future time (any past price periods will be ignored)
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
    function getLowestPrice($information) {
        $sql = "
            SELECT
                *
            FROM
                rm_unit_daily_prices
            WHERE
                unit_id = '".$information->getUnit()->id."'
                AND number_persons = '".$information->getPersons()->getAdults()."' ";

        if ($information->getPeriod() !== null) {
            $startDatetime = $information->getPeriod()->getStart()->toMySQL();
            $endDatetime = $information->getPeriod()->getEnd()->toMySQL();
            $sql.= "
                AND (
                    (UNIX_TIMESTAMP(start_datetime) <= UNIX_TIMESTAMP('$startDatetime') && UNIX_TIMESTAMP(end_datetime) >= UNIX_TIMESTAMP('$startDatetime') )
                     OR
                    (UNIX_TIMESTAMP(start_datetime) <= UNIX_TIMESTAMP('$endDatetime') && UNIX_TIMESTAMP(end_datetime) >= UNIX_TIMESTAMP('$endDatetime') )
                     OR
                    (UNIX_TIMESTAMP(start_datetime) >= UNIX_TIMESTAMP('$startDatetime') && UNIX_TIMESTAMP(end_datetime) <= UNIX_TIMESTAMP('$endDatetime') )
                )
            ";
        } else {
            $sql.= "
                AND UNIX_TIMESTAMP(end_datetime) >= ".time()."
            ";
        }
        
        $rows = $this->_getBySQL($sql);
        $lowest = 999999;
        foreach ($rows as $row) {
            if ($row->weekday_price != 0 && (float)$row->weekday_price < $lowest) {
                $lowest = $row->weekday_price;
            }
            if ($row->weekend_price != 0 && $row->weekend_price < $lowest) {
                $lowest = $row->weekend_price;
            }
            if ($row->weekly_rate != 0 && $row->weekly_rate < $lowest) {
                $lowest = $row->weekly_rate;
            }
        }
        if ($lowest == 999999) {
            $lowest = 0;
        }
        return $lowest;
    }

    
}