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
 *
 *
 * Class for handling all DAO method for reservation billing.
 * This table extends by various modules to handle all extra reservation prices
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Billing extends RM_Model {

    protected $_name = 'rm_billing';
    protected $_primary = 'id';
    protected $_referenceMap = array(
        'Reservations' => array(
            'columns' => 'reservation_id',
            'refTableClass' => 'RM_ReservationDetails',
            'refColumns' => 'reservation_id'
        ),
        'Units' => array(
            'columns' => 'unit_id',
            'refTableClass' => 'RM_ReservationDetails',
            'refColumns' => 'unit_id'
        )
    );

    /**
     * this returns the saved price information for a reservation
     *
     * @param  	string  $reservationID
     * @return 	object	a complete price breakdown.
     */
    public function getPrice($reservationID) {
        // TODO: we will need to add code to dynamically check modules and plugins that
        // have code that adds prices/charges in order to dynamcially check any saved
        // prices that have been charged to customers
        if (!isset($reservationID)){
            return false;
        }

        $reservationModel = new RM_Reservations();
        $reservation = $reservationModel->find($reservationID)->current();

        $priceObj = new stdClass();
        $priceObj->tax = RM_Environment::getInstance()->getTaxSystem()->getTotalTaxes($reservation);
        $priceObj->total = $reservation->getTotalPrice();
        $priceObj->subtotal = $priceObj->total - $priceObj->tax;
        return $priceObj;
    }

    public function getPaymentsTotal(RM_Reservation_Row $reservation) {
        $payments = $this->getPayments($reservation);
        $total = 0;
        foreach ($payments as $payment) {
            $total += $payment->total_paid;
        }
        return $total;
    }

    /**
     * Gets the payment information from the DB.
     *
     * @param string $reservationID
     * @return Object
     */
    public function getPayments(RM_Reservation_Row $reservation) {
        $sql = "
            SELECT
                *
            FROM
                rm_billing b
            INNER JOIN
                rm_billing_payments AS bp ON b.id=bp.id
            WHERE
                b.reservation_id='{$reservation->id}'
            ";
        return $this->_getBySQL($sql);
    }

    /**
     * Get all billing items
     */
    public function getAll($status = null) {
        $sql = "
            SELECT
                *
            FROM
                rm_billing b
            INNER JOIN
                rm_billing_payments AS bp ON b.id=bp.id
            ";

        switch ($status) {
            case "paid":
                $sql.=" WHERE bp.total > 0";
                break;
            case "unpaid":
                $sql.=" WHERE bp.total = 0";
                break;
        }
        return $this->_getBySQL($sql);
    }

}