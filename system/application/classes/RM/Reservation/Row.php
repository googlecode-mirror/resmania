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
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Reservation_Row extends RM_Row
{
    public function isPaid()
    {
        return ($this->getTotalPaid() >= $this->getTotalPrice());
    }

    /**
     * Returns total paid for this reservation
     *
     * @return float
     */
    public function getTotalPaid()
    {
        $billingModel = new RM_Billing();
        $billingTotal = $billingModel->getPaymentsTotal($this);
        return $billingTotal;
    }

    /**
     * Recalculate all reservation extras information. Important!!!
     * this method need to be extended if we will add some other system that are affected reservation price. 
     */
    public function recalculate()
    {
        $extrasSystems = RM_Environment::getInstance()->getExtrasSystems();
        foreach ($extrasSystems as $extrasSystem) {
            $extrasSystem->recalculate($this);
        }
        $othersSystems = RM_Environment::getInstance()->getOthersSystems();
        foreach ($othersSystems as $othersSystem) {
            $othersSystem->recalculate($this);
        }
        $discountsSystems = RM_Environment::getInstance()->getDiscounts();
        foreach ($discountsSystems as $system) {
            $system->recalculate($this);
        }
        RM_Environment::getInstance()->getTaxSystem()->recalculate($this);
    }

    /**
     * Returns all saved reservation detail rows
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getDetails(){
        $model = new RM_ReservationDetails();
        return $model->getAllByReservation($this);
    }

    /**
     * Returns total price for details without any taxes/extras/discount/coupons etc modifications.
     *
     * @return float
     */
    public function getDetailsPrice()
    {
        $total = 0;
        $details = $this->getDetails();
        foreach ($details as $detail) {
            $total += $detail->total_price;
        }
        return $total;
    }

    /**
     * Return total price for reservation,
     * in the time when this reservation was made
     *
     * @return float
     */
    public function getTotalPrice()
    {
        $total = 0;
        $total += $this->getTotalDetailsPrice();
        $total += $this->_getTotalPrice();        
        return $total;
    }    

    /**
     * Returns total price for reservation with only options that are
     * connected to reservation itself. It could be only '-' if there is only a discount for example
     *
     * @return float
     */
    private function _getTotalPrice()
    {
        $total = 0;
        $summaryModel = new RM_ReservationSummary();
        $summaryRows = $summaryModel->fetchByReservation($this);
        foreach ($summaryRows as $row) {
            $total += $row->total_amount;
        }

        return $total;                
    }

    /**
     * Return total number of days reserved for every units in the reservation
     *
     * @return int
     */
    public function getTotalNumberOfDays()
    {
        $total = 0;

        //1. calculate all reservation detail prices
        $details = $this->getDetails();
        foreach ($details as $detail){
            $total += $detail->getTotalDays();
        }

        return $total;
    }

    /**
     * Return total number of days reserved for every units in the reservation
     *
     * @return int
     */
    public function getTotalNumberOfSeconds()
    {
        $total = 0;
        $details = $this->getDetails();
        foreach ($details as $detail){
            $total += (int)$detail->getEndDatetime('U') - (int)$detail->getStartDatetime('U');
        }
        return $total;
    }

    /**
     * Returns total price for every reservation details connected to 
     * reservation
     *
     * @return float
     */
    public function getTotalDetailsPrice()
    {
        $total = 0;

        $details = $this->getDetails();
        foreach ($details as $detail){
            $total += $detail->getTotalPrice();
        }

        return $total;
    }

    /**
     * Checks if this reservation assigned to input user.
     *
     * @param RM_User_Row $user
     * @return bool
     */
    public function isAssignedTo(RM_User_Row $user)
    {
        return ($this->user_id == $user->id);
    }

    /**
     * Returns user object that reservation is assigned to.
     *
     * @return RM_User_Row
     */
    public function getUser()
    {
        return $this->findParentRow('RM_Users', 'User');
    }
}