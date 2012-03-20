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
class RM_Discounts_Type_Amount extends RM_Discounts_Type {
    /**
     * Calculate value
     *
     * @param RM_Discounts_Row $row
     * @param float $amount
     * @return float
     */
    public function calculate(RM_Reservation_Details $detail){
        return RM_Environment::getInstance()->roundPrice($this->_discount->amount);
    }

    public function toString(){
        $config = new RM_Config();
        return $config->getValue('rm_config_currency_symbol').$this->_discount->amount;
    }
}