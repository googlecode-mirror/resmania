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
 * Price Controller.
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class RM_Admin_PricesController extends RM_Controller {

    function getpriceJsonAction() {

        $unitIDs = $this->_getParam('ids');
        $start_datetime = $this->_getParam('start_datetime');
        $end_datetime = $this->_getParam('end_datetime');
        $adults = $this->_getParam("adults", 1);
        $children = $this->_getParam("children", 0);
        $infants = $this->_getParam("infants", 0);

        $persons = new RM_Reservation_Persons(array("adults" => $adults, "children" => $children, "infants" => $infants));

        $unitsDAO = new RM_Units();

        $stardateObj = new RM_Date(strtotime($start_datetime));
        $enddateObj = new RM_Date(strtotime($end_datetime));
        $periodObj = new RM_Reservation_Period($stardateObj, $enddateObj);

        $priceSystem = RM_Environment::getInstance()->getPriceSystem();

        $units = explode(",", $unitIDs);

        $taxSystem = RM_Environment::getInstance()->getTaxSystem();
        $tax = 0;

        foreach ($units as $uid) {
            $unitObj = $unitsDAO->get($uid);
            $information = new RM_Prices_Information($unitObj, $periodObj, $persons);
            try {
                $subtotal = $subtotal + $priceSystem->getTotalUnitPrice($information);
            } catch (Exception $e) {
                // no return needed
            }
            $tax += $taxSystem->calculateTotalTax($unitObj, $subtotal);
        }

        // get currency symbol
        $config = new RM_Config();
        $currency_symbol = $config->get('rm_config_currency_symbol');


        // calculate the total
        $total = $subtotal + $tax;

        return array(
            'data' => '{ data: [{
                                info: "Subtotal", value: "' . $currency_symbol['rm_config_currency_symbol'] . ' ' . $subtotal . '"
                        },{
                                info: "Tax", value: "' . $currency_symbol['rm_config_currency_symbol'] . ' ' . $tax . '"
                        },{
                                info: "Total", value: "' . $currency_symbol['rm_config_currency_symbol'] . ' ' . $total . '"
                        }]
                    }',
            'encoded' => true
        );
    }

}