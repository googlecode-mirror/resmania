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
 * @author      Rob/Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Unit_Sorter
{
    /**
     * @var Zend_Db_Table_Rowset
     */
    private $_units;

    /**
     * @var RM_Unit_Search_Criteria
     */
    private $_criteria;

    /**
     * @var Zend_Db_Table_Rowset
     */
    private $_sortedUnits;

    /**
     * @var RM_Reservation_Period
     */
    private $_period;

    /**
     * @var bool
     */
    private $_isDefaultPeriod = true;

    /**
     * @var RM_Reservation_Persons
     */
    private $_persons;

    /**
     * @var bool
     */
    private $_showPriceWithTax;

    /**
     * @var RM_Taxes_Interface
     */
    private $_taxSystem = null;

    public function __construct(RM_Unit_Search_Criteria $criteria, Zend_Db_Table_Rowset $units)
    {
        $this->_units = $units;
        $this->_criteria = $criteria;
        $this->_init();
    }

    protected function _init()
    {
        $this->_period = RM_Reservation_Period::getDefault();
        if ($this->_criteria->start_datetime && $this->_criteria->end_datetime) {
            $this->_isDefaultPeriod = false;
            $this->_period = new RM_Reservation_Period(
                new RM_Date(strtotime($this->_criteria->start_datetime)),
                new RM_Date(strtotime($this->_criteria->end_datetime))
            );
        }

        $this->_persons = new RM_Reservation_Persons(array(
            "adults" => $this->_criteria->adults,
            "children" => $this->_criteria->children,
            "infants" => $this->_criteria->infants
        ));

        $config = new RM_Config();
        $this->_showPriceWithTax = (bool)$config->getValue('rm_config_prices_with_tax');
        if ($this->_showPriceWithTax) {
            $this->_taxSystem = RM_Environment::getInstance()->getTaxSystem();
        }
    }

    public static function cmp($a, $b)
    {
        if ($a['price'] == 0 && $b['price'] != 0) {
            return 1;
        }

        if ($a['price'] != 0 && $b['price'] == 0) {
            return -1;
        }

        if ($a['price'] == $b['price']) {
            return 0;
        }
        return ($a['price'] < $b['price']) ? -1 : 1;
    }

    public function sort()
    {
        $data = $this->_units->toArray();
        $data = $this->_calculateAllPrices($data);
        usort($data, array('RM_Unit_Sorter', 'cmp'));        
        $config = array(
            'table' => $this->_units->getTable(),
            'data' => $data,
            'rowClass' => 'RM_Unit_Row',
            'stored' => true
        );
        $this->_sortedUnits = new Zend_Db_Table_Rowset($config);
    }

    protected function _calculateAllPrices(array $data)
    {
        foreach ($data as $key => $row) {
            $data[$key]['price'] = $this->_calculatePrice($row);
        }
        return $data;
    }

    /**
     * @param array $row
     * @return floar
     */
    private function _calculatePrice($row)
    {
        $model = new RM_Units();
        $unit = $model->find($row['id'])->current();

        $information = new RM_Prices_Information($unit, $this->_period, $this->_persons);
        $priceSystem = RM_Environment::getInstance()->getPriceSystem();

        $price = $priceSystem->getLowestUnitPrice($information);
        if ($this->_isDefaultPeriod == false) {
            try {
                $price = $priceSystem->getTotalUnitPrice($information);
            } catch (Exception $e) {
                $price = 0;
            }                      
        }

        if ($price != 0 && $this->_showPriceWithTax) {
            $price += $this->_taxSystem->calculateTotalTax($unit, $price);
        }

        return $price;
    }


    /**
     * @return Zend_Db_Table_Rowset
     */
    public function getResult()
    {
        return $this->_sortedUnits;
    }
}
