<?php
/**
 * ResMania - Reservation System Framework http://resmania.com
 * Copyright (C) 2011  ResMania Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_ReservationDetails extends RM_Model {
    protected $_name = 'rm_reservation_details';
    protected $_rowClass = 'RM_Reservation_Details_Row';    
    protected $_referenceMap    = array(
        'Reservations' => array(
            'columns'           => 'reservation_id',
            'refTableClass'     => 'RM_Reservations',
            'refColumns'        => 'id'
        ),
        'Units' => array(
            'columns'           => 'unit_id',
            'refTableClass'     => 'RM_Units',
            'refColumns'        => 'id'
        )
    );        

    public function deleteByUnit(RM_Unit_Row $unit){
        return $this->delete('unit_id='.$unit->getId());
    }        

    /**
     * Returns all reservation details by unit and reservation objects.
     * As long as we could create multiple reservation detail for one unit for one reservation 
     * only with different dates.
     *
     * @param RM_Unit_Row $unit
     * @param RM_Reservation_Row $reservation optional
     * @return Zend_Db_Table_Rowset
     */
    public function fetchAllBy(RM_Unit_Row $unit, RM_Reservation_Row $reservation = null)
    {
        $sql = 'unit_id="'.$unit->getId().'"';
        if ($reservation !== null) {
            $sql.= ' AND reservation_id="'.$reservation->id.'"';
        }
        return $this->fetchAll($sql);
    }    

    /**
     * Return all reservation details that are reserved input unit
     *
     * @param RM_Unit_Row $unit
     * @param array $exceptReservations array with RM_Reservation_Row objects that we don't need in the result set
     * @return Zend_Db_Table_Rowset_Abstract with objects RM_Reservation_Details_Row
     */
    public function findByUnit($unit, $exceptReservations = array())
    {
        $select = $this->select()->where('unit_id = '.$unit->getId());
        if (count($exceptReservations) > 0) {
            foreach ($exceptReservations as $reservation) {
                $select->where('reservation_id != "'.$reservation->id.'"');
            }
        }
        return $this->fetchAll($select);
    }

    public function getAllByReservation(RM_Reservation_Row $reservation){
        return $this->fetchAll($this->select()->where('reservation_id = ?', $reservation->id));
    }

    /**
     * @deprecated
     */
    public function fetchAllByReservation($reservation, $order = null, $count = null, $offset = null, $language = null)
    {
        if ($language == null) {
            $language = RM_Environment::getInstance()->getLocale();
        }

        if ($reservation == null) {
            return null;
        }        

        //1. we need all fields from reservation_config that have admin_edit_preferences!=""
        $reservationConfigModel = new RM_ReservationConfig();
        $reservationDetailColumns = $reservationConfigModel->getAdminEdit();
        
        //2. we need all fields from unit config that have admin_reservation=1 and language = 0
        $unitConfigModel = new RM_UnitConfig();
        $unitColumns = $unitConfigModel->getReservationFields();

        //3. we need all fields from unit config that have admin_reservation=1 and language = 1
        $unitLanguageColumns = $unitConfigModel->getReservationLanguageFields();

        //4. create SQL
        $fieldsNames = array();
        foreach ($reservationDetailColumns as $field) {
            $fieldsNames[] = 'rd.'.$field->name.' AS '.$field->name;
        }

        foreach ($unitColumns as $field) {
            $fieldsNames[] = 'rm_units.'.$field->column_name.' AS '.$field->column_name;
        }
       
        foreach ($unitLanguageColumns as $field) {
            $fieldsNames[] = 'rm_unit_language_details.'.$field->column_name.' AS '.$field->column_name;
        }

        if ($offset === null){
            $offset = 0;
        }

        $sql = "
            SELECT
                ".implode(',', $fieldsNames)."
            FROM
                {$this->_name} rd
            INNER JOIN
                rm_units ON rm_units.id = rd.unit_id
            LEFT OUTER JOIN
                rm_unit_language_details ON rm_unit_language_details.unit_id=rm_units.id AND rm_unit_language_details.iso = '$language'
            WHERE
                rd.reservation_id = '$reservation->id' ";

        if ($order !== null) {
            $sql.= " ORDER BY $order ";
        }

        if ($count !== null) {
            $sql.= " LIMIT $offset, $count ";
        }

        return $this->_getBySQL($sql);        
    }

    /**
     * Return how many reservations start (only starts) 
     * in input reservation period
     *
     * @param RM_Unit_Row $unit
     * @param RM_Reservation_Period $period
     * @return int
     */
    public function getReservationCount(RM_Unit_Row $unit, RM_Reservation_Period $period){
        $sql = "
            SELECT
                *
            FROM
                rm_reservation_details
            WHERE
                (
                UNIX_TIMESTAMP(start_datetime)<=UNIX_TIMESTAMP('".$period->getEnd()->toMySQL()."')
                    AND
                UNIX_TIMESTAMP(start_datetime)>=UNIX_TIMESTAMP('".$period->getStart()->toMySQL()."')
                )
                    AND
                unit_id = '".$unit->getId()."'";
        
        $value = $this->_getBySQL($sql)->count();
        return $value;
    }

    public function getAll($order, $count, $offset, $filters = array())
    {
        $select = $this->select()->from(array('rd' => 'rm_reservation_details'))->setIntegrityCheck(false);
        foreach ($filters as $filter){
            //TODO: another hardocode
            //'cause rm_reservation_details and rm_unit_language_details has same columns: unit_id
            if ($filter['field'] == 'unit_id') {
                $filter['field'] = 'rd.unit_id';
            }
            $filterContidions = $this->_getConditions($filter);
            foreach ($filterContidions as $condition){
                $select = $select->where($condition);
            }
        }

        $fieldsDAO = new RM_ReservationConfig();
        $configFields = $fieldsDAO->getAdminList()->toArray();
        $columns = array();
        foreach ($configFields as $configField) {
            //TODO: omg this is so hardcode
            if ($configField['details'] == 0 &&
                $configField['name'] !== 'total_price' && 
                $configField['name'] !== 'first_name' &&
                $configField['name'] !== 'last_name') {
                $columns[] = $configField['name'];
            }
        }        
        //$columns[] = 'user_id';
        //$columns[] = 'confirmed';
        //$columns[] = 'creation_datetime';
        //$columns[] = 'is_read';
        $select = $select->join(array('r' => 'rm_reservations'), 'r.id = rd.reservation_id', $columns);

        $columns = array();
        $columns[] = 'first_name';
        $columns[] = 'last_name';
        $select = $select->joinLeft(array('u' => 'rm_users'), 'r.user_id = u.id', $columns);

        // This breaks the reservation list, if a unit is removed then reservations for that unit will not be return.
        // if a unit is removed then the unit_language_details becomes empty and so there is no link to the uld and the reservation details.        
        $lang = RM_Environment::getInstance()->getLocale();
        $columns = array();
        $columns[] = 'name';
        $select = $select->joinLeft(array('uld' => 'rm_unit_language_details'), 'rd.unit_id = uld.unit_id AND uld.iso = "'.$lang.'"', $columns);

        if ($count !== null){
            $select->limit($count, $offset);
        }

        $select->order($order);

        $reservations = $this->fetchAll($select);
        return $reservations;
    }

}