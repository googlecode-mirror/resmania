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
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Discounts_Type_Percentage extends RM_Discounts_Type {
    /**
     * Calculate
     *
     * @param RM_Discounts_Row $row
     * @param float $amount
     * @return float
     */
    public function calculate(RM_Reservation_Details $detail)
    {
        $discountPeriod = $this->_discount->getPeriod();
        $totalDiscount = 0;
        $discountPercentage = $this->_discount->amount;

        $priceSystem = RM_Environment::getInstance()->getPriceSystem();

        try {
            $priceDays = $priceSystem->getTotalUnitPrice(new RM_Prices_Information($detail->getUnit(), $detail->getPeriod(), $detail->getPersons()), true);
        } catch (RM_Exception $e) {
            $priceDays = 0;
        }
        
        foreach ($priceDays as $day) {
            if ($discountPeriod->isInternal($day['step'])) {
                $discountedPrice = $day['price'];


                $totalDiscount += ($day['price'] / 100) * $discountPercentage;
            }
        }
        return RM_Environment::getInstance()->roundPrice($totalDiscount);
    }

    public function toString(){
        return $this->_discount->amount.'%';
    }
}