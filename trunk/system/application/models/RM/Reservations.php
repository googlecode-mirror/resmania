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
class RM_Reservations extends RM_Model_Flexible {

    protected $_name = 'rm_reservations';
    protected $_rowClass = 'RM_Reservation_Row';
    protected $_referenceMap = array(
        'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'RM_Users',
            'refColumns' => 'id'
        )
    );

    /**
     * Method that parsed invoice template and returns string
     *
     * All variable that parsed into invoice.pthml template
     * $reservation:
     * $reservation.id
     * $reservation.user_id
     * $reservation.confirmed (Yes, No - translated)
     * $reservation.is_read (Yes, No - translated)
     * $reservation.creation_datetime (In MySQL format: Y-m-d H:m:s)
     * $reservation.modified_datetime (In MySQL format: Y-m-d H:m:s)
     * $reservation.notes
     * $reservation.tax
     * $reservation.paid
     * $reservation.due
     *
     * $customer:
     * $customer.id
     * $customer.title (Translated)
     * $customer.first_name
     * $customer.last_name
     * $customer.address1
     * $customer.address2
     * $customer.state
     * $customer.city
     * $customer.postcode
     * $customer.country
     * $customer.telephone
     * $customer.mobile
     * $customer.email
     * $customer.username
     *
     * $details each detail is an $element:
     * $element.reservation_id
     * $element.unit_id
     * $element.start_datetime (In MySQL format: Y-m-d H:m:s)
     * $element.end_datetime (In MySQL format: Y-m-d H:m:s)
     * $element.total_price
     * $element.unit.id
     * $element.unit.rating (number)
     * $element.unit.published (Yes, No - translated)
     * $element.unit.color (hex color)
     * $element.unit.(all language db field names that are belong to unit type, for example: name, summary, description)
     *
     * $text: all text constants in section 'Admin.Invoice' in languages file, for example $text.BookingReference
     *
     * @param RM_Reservation_Row $reservation
     * @return <type>
     */
    public static function getInvoice(RM_Reservation_Row $reservation) {
        $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);

        $data = new Dwoo_Data();
        $data->assign('invoice', array(
            'date' => date('d/m/Y')
        ));

        $config = new RM_Config();
        $data->assign('currencysymbol', $config->getCurrencySymbol());
        //TODO: resmania - we need to add discounts and coupons here
        $reservationArray = $reservation->toArray();
        $billing = new RM_Billing();
        $priceCharges = $billing->getPrice($reservation->id);
        $reservationArray['tax'] = $priceCharges->tax;
        $reservationArray['paid'] = $billing->getPaymentsTotal($reservation);
        $reservationArray['due'] = abs($priceCharges->total - $billing->getPaymentsTotal($reservation));
        $reservationArray['total'] = $priceCharges->total;
        $reservationArray['confirmed'] = $reservation->confirmed ? $translate->_('MessageYes') : $translate->_('MessageNo');
        $reservationArray['is_read'] = $reservation->is_read ? $translate->_('MessageYes') : $translate->_('MessageNo');
        $data->assign('reservation', $reservationArray);

        $text = $translate->getSectionMessages('Common.Invoice');
        $data->assign('text', $text);

        $userModel = new RM_Users();
        $user = $userModel->getByReservation($reservation);
        if ($user == null) {
            $userArray = array();
        } else {
            $userArray = $user->toArray();
            $userArray['title'] = $user->getTitle();
        }
        $data->assign('customer', $userArray);

        $reservationDetailsModel = new RM_ReservationDetails();
        $summaryModel = new RM_ReservationSummary();
        $details = $reservationDetailsModel->getAllByReservation($reservation);
        $arrayDetails = array();
        foreach ($details as $detail) {
            $arrayDetail = $detail->toArray();
            $unit = $detail->findUnit();
            $unitArray = $unit->toArray();
            $unitArray['id'] = $unit->getId();
            $unitArray['published'] = $unitArray->published ? $translate->_('MessageYes') : $translate->_('MessageNo');

            // format the start/end dates
            $arrayDetail['start_datetime'] = $config->convertDates($arrayDetail['start_datetime'], RM_Config::PHP_DATEFORMAT, RM_Config::JS_DATEFORMAT);
            $arrayDetail['end_datetime'] = $config->convertDates($arrayDetail['end_datetime'], RM_Config::PHP_DATEFORMAT, RM_Config::JS_DATEFORMAT);

            // extras
            $reservationDetailsExtra = $summaryModel->fetchByReservationDetail($detail)->toArray();
            foreach ($reservationDetailsExtra as $extra) {
                if ($extra['value'] == 0)
                    $extra['value'] = "";
                $unitArray['extras'][] = array("name" => $extra['name'], "value" => $extra['value'], "total_amount" => $extra['total_amount']);
            }

            $arrayDetail['unit'] = $unitArray;
            $arrayDetails[] = $arrayDetail;
        }
        $data->assign('details', $arrayDetails);

        $templateFile = implode(DIRECTORY_SEPARATOR, array(
                    RM_Environment::getConnector()->getRootPath(),
                    'RM',
                    'userdata',
                    'views',
                    'admin',
                    'scripts',
                    'templates',
                    'invoice.phtml'
                ));
        $template = new Dwoo_Template_File($templateFile);
        $dwoo = new Dwoo();
        return $dwoo->get($template, $data);
    }

    public function insertNewReservation($user, $unitDetails, $inprogres = 0, $confirmed = 1, $bookingRef = null) {
        //1. add information into rm_reservation

        if ($user->id){
            $userID = $user->id;
        } else {
            // at this point the user does not exist in ResMania let's add it...
            $userData = array();
            foreach($user as $key=>$value){
                if ($key == 'name'){
                    $nameArray = explode(" ", $value);
                    $userData['first_name'] = $nameArray[0];
                    $userData['last_name'] = $nameArray[1]." ".$nameArray[2];
                }
                $userData[$key]=$value;
            }
            $userData['group_id'] = 0;
            unset($userData['id']);
            $rmUsers = new RM_Users();
            $userID = $rmUsers->insert($userData);
        }

        $reservation = $this->createRow();
        $reservation->user_id = $userID;
        $reservation->confirmed = $confirmed;
        $reservation->in_progress = $inprogres; // this will hide the reservation until we have completed the process
        if ($bookingRef !== null) {
            $reservation->id = $bookingRef;
        }

        $reservation->creation_datetime = date(RM_Config::MYSQL_DATEFORMAT); //current server datetime
        $reservationID = $reservation->save();

        //2. add information into rm_reservation_details
        $detailsModel = new RM_ReservationDetails();
        $priceSystem = RM_Environment::getInstance()->getPriceSystem();

        foreach ($unitDetails as $unitDetail) {
            $detail = $detailsModel->createRow();

            $selectedUnit = $unitDetail->getUnit();

            // get the master unit
            if (class_exists("RM_Groups")){

                $groupsObject = new RM_Groups();
                $isMaster = $groupsObject->isMain($selectedUnit);

                if (!$isMaster){

                    $unitModel = new RM_Units();

                    $group = $groupsObject->getByUnit($selectedUnit);
                    if ($group != null) {
                        try {
                            $groupID = $group->main_unit_id;
                            $selectedUnit = $unitModel->get($groupID, RM_Environment::getInstance()->getLocale());
                        } catch (Exception $e){}
                    }
                }
            }

            $information = new RM_Prices_Information(
                            $selectedUnit,
                            $unitDetail->getPeriod(),
                            $unitDetail->getPersons(),
                            $unitDetail->getOtherInfo()
            );

            try {
                $detail->total_price = $priceSystem->getTotalUnitPrice($information);
            } catch (Exception $e) {
                $detail->total_price = 0;
            }

            $detail->reservation_id = $reservationID;
            $detail->unit_id = $unitDetail->getUnit()->getId();
            $detail->start_datetime = $unitDetail->getPeriod()->getStart()->toMySQL();
            $detail->end_datetime = $unitDetail->getPeriod()->getEnd()->toMySQL();
            $detail->adults = ($unitDetail->getPersons()->getAdults() == 0 ? 1 : $unitDetail->getPersons()->getAdults());
            $detail->children = $unitDetail->getPersons()->getChildren();
            $detail->infants = $unitDetail->getPersons()->getInfants();

            // process other information (this allows the price system to manage
            // other data i.e: board_types for the hospitality price module
            $otherInfo = $unitDetail->getOtherInfo();
            if ($otherInfo) {
                foreach ($otherInfo as $key => $value) {
                    $detail->$key = $value;
                }
            }

            $detail->save();

            $this->_insertDetailExtraData($unitDetail, $detail);
        }

        return $reservationID;
    }

    /**
     * Insert extra reservation data - tax
     *
     * @todo we need to think about all of this
     * @param RM_Reservation_Row $reservation
     * @param RM_Reservation_Details $detail
     * @param RM_Reservation_Details_Row $detailRow
     * @return bool
     */
    public function _insertDetailExtraData(RM_Reservation_Details $detail, RM_Reservation_Details_Row $detailRow) {
        $discountSystems = RM_Environment::getInstance()->getDiscounts();
        foreach ($discountSystems as $discountSystem) {
            $discountSystem->save($detail, $detailRow);
        }

        $taxSystem = RM_Environment::getInstance()->getTaxSystem();
        if ($taxSystem !== null) {
            $taxSystem->assign($detail, $detailRow);
        }

        $extrasSystems = RM_Environment::getInstance()->getExtrasSystems();
        foreach ($extrasSystems as $extrasSystem) {
            $extrasSystem->assign($detail, $detailRow);
        }

        $othersSystems = RM_Environment::getInstance()->getOthersSystems();
        foreach ($othersSystems as $othersSystem) {
            $othersSystem->assign($detail, $detailRow);
        }
    }

    /**
     * Inserts a new row.
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data) {
        if (isset($data['id']) == false) {
            $data['id'] = RM_Reservations::createReservationID();
        }
        return parent::insert($data);
    }

    function confirm($reservation) {
        //TODO: leter we need to make some email, etc.
        $reservation->confirmed = 1;
        $reservation->in_progress = 0;
        $reservation->save();
    }

    function markPaid(RM_Reservation_Row $reservation) {
        $totalPrice = $reservation->getTotalPrice();

        $billingModel = new RM_Billing();
        $totalPaid = $billingModel->getPaymentsTotal($reservation);

        $total = $totalPrice - $totalPaid;

        $billingID = $billingModel->createRow(array(
                    'reservation_id' => $reservation->id,
                    'total_paid' => $total
                ))->save();

        $billingPaymentsModel = new RM_BillingPayments();
        $billingPaymentsModel->createRow(array(
            'id' => $billingID,
            'provider' => 'Administrator',
            'transaction_id' => '0',
            'status' => 'success',
            'total' => $total,
            'transaction_date' => date('Y-m-d H:i:s')
        ))->save();

        $this->confirm($reservation);
    }

    function unconfirm($reservation) {
        //TODO: leter we need to make some email, etc.
        $reservation->confirmed = 0;
        $reservation->save();
    }

    function inProgressComplete($reservation) {
        $reservation->in_progress = 0;
        $reservation->save();
    }

    /**
     * Should be removed and just use this table name
     *
     * @deprecated
     * @return string
     */
    public function getDependentTableName() {
        return 'rm_reservation_details';
    }

    /**
     * Returns all reservation that contains unit that are in input reservation
     */
    function fetchAllLinkedByUnit($reservation) {
        $sql = "
            SELECT
                r2.*
            FROM
                {$this->_name} r
            LEFT OUTER JOIN
                {$this->getDependentTableName()} rd ON rd.reservation_id=r.id
            LEFT OUTER JOIN
                {$this->getDependentTableName()} rd2 ON rd2.unit_id=rd.unit_id AND rd2.reservation_id!=rd.reservation_id
            INNER JOIN
                {$this->_name} r2 ON rd2.reservation_id=r2.id
            WHERE
                r.id='{$reservation->id}'
        ";
        return $this->_getBySQL($sql);
    }

    public function getAllUnitIDs($reservation) {
        return $reservation->findManyToManyRowset('Units', $this->_dependentTables[0]);
    }

    function fetchAllForUnitCalendar(RM_Unit_Row $unit) {
        $config = new RM_Config();
        $showConfirmed = (bool) (int) $config->getValue('rm_config_calendar_confirmed');
        $showNonConfirmed = (bool) (int) $config->getValue('rm_config_calendar_nonconfirmed');
        if ($showConfirmed == false && $showNonConfirmed == false) {
            return array();
        }
        $filters = array();
        if (($showConfirmed && $showNonConfirmed) === false) {
            $filters[] = array(
                'field' => 'confirmed',
                'data' => array(
                    'type' => 'numeric',
                    'value' => $showConfirmed ? 1 : 0,
                    'comparison' => 'eq'
                )
            );
        }
        return $this->fetchAllByUnit($unit, $filters);
    }

    /**
     * @param RM_Unit $unit
     * @param array $filters array with filters @see RM_Model::_getConditions
     * @return Zend_Db_Table_Rowset
     */
    function fetchAllByUnit($unit, array $filters = array(), $order = "id") {
        $sql = "
            SELECT
                r.*,
                rd.start_datetime,
                rd.end_datetime
            FROM
                {$this->_name} r
            INNER JOIN
                {$this->getDependentTableName()} rd ON rd.reservation_id=r.id
                AND rd.unit_id='" . $unit->getId() . "'
            ";

        if (count($filters) > 0) {
            $filterConditions = array();
            foreach ($filters as $filter) {
                $filterConditions = array_merge($filterConditions, $this->_getConditions($filter));
            }
            $filterSQL = implode(" AND ", $filterConditions);
            $sql.= " WHERE " . $filterSQL;
        }

        $sql.=" ORDER BY " . $order;

        return $this->_getBySQL($sql);
    }

    /**
     * @param $unitid   the unit id we are looking up
     * @param $startdate    the startdate of the reservation
     * @return Zend_Db_Table_Rowset
     */
    function fetchAllByUnitDate($unitid, $selecteddate, $lang = null) {
        if ($lang == null) {
            $lang = RM_Environment::getInstance()->getLocale();
        }

        $sql = "
            SELECT
                rd . * ,
                ud.name,
                r.user_id as user_id,
                r.confirmed as confirmed,
                u.title as title,
                u.first_name as first_name,
                u.last_name as last_name
            FROM
                rm_reservation_details AS rd
            LEFT JOIN
                rm_unit_language_details AS ud ON rd.unit_id = ud.unit_id
            INNER JOIN
                rm_reservations AS r ON r.id=rd.reservation_id
            INNER JOIN
                rm_users AS u ON r.user_id=u.id
            WHERE
                rd.unit_id =  '$unitid'
            AND
                ud.iso =  '$lang'
            AND (
                UNIX_TIMESTAMP( rd.end_datetime ) >= UNIX_TIMESTAMP('$selecteddate')
                AND
                UNIX_TIMESTAMP( rd.start_datetime ) <= UNIX_TIMESTAMP('$selecteddate')
                )
            ";
        return $this->_getBySQL($sql);
    }

    function fetchAllByUserID($customer_id, $temp = false) {
        $sql = "
            SELECT
                rd.*, r.*
            FROM
                {$this->getDependentTableName()} rd
            INNER JOIN
                {$this->_name} r ON rd.reservation_id = r.id
                AND r.user_id = '$customer_id'
        ";
        return $this->_getBySQL($sql);
    }

    public function getAll($order = null, $count = null, $offset = null, $filters = array()) {
        $select = $this->select()->from(array('r' => 'rm_reservations'))->setIntegrityCheck(false);
        foreach ($filters as $filter) {
            $filterContidions = $this->_getConditions($filter);
            foreach ($filterContidions as $condition) {
                $select = $select->where($condition);
            }
        }

        $columns = array();
        $columns[] = 'first_name';
        $columns[] = 'last_name';

        $select = $select->join(array('u' => 'rm_users'), 'r.user_id = u.id', $columns);

        if ($count !== null) {
            $select->limit($count, $offset);
        }

        $reservations = $this->fetchAll($select);
        return $reservations;
    }

    /**
     * This will create a Unique Reservation/Booking Reference Number
     * The format if this reference is YYMMDD-HHSSXXXX
     *
     * * @return 	string	Reservation Unique ID/Reference
     */
    public static function createReservationID() {
        $date = date('ydm');
        $time = date('Hi');
        $rand = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        return $date . "-" . $time . $rand;
    }

    /**
     * get the unread reservation count
     *
     * This will get the un-read reservation count and display on the menu next
     * to Reservations ie: Reservation(10) like unread email messages
     *
     * @return 	int	the count of unread reservations
     */
    public function getUnreadCount() {
        //we also need to check are this reservation have at least one unit detail
        return $this->fetchAll($this
                        ->select()
                        ->setIntegrityCheck(false)
                        ->from(array('r' => 'rm_reservations'))
                        ->where('is_read=?', 0)
                        ->where('in_progress=?', 0)
                        ->join(array('rd' => 'rm_reservation_details'), 'rd.reservation_id = r.id'))->count();
    }

}