<?php
/**
 * Tax type "singleton" class.
 *
 * This is simple type: daily tax tyoe
 *
 * @access 	public
 * @author 	Rob
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link	http://developer.resmania.com/api
 * @since  	08-2009
 */
class RM_Taxes_Type_DailyPerPerson extends RM_Taxes_Type {
    /**
     * This object will be the only one object in application
     *
     * @var RM_Taxes_Type_Amount
     */
    protected static $_instance;

    /**
     * Static method to get an object, this method is the only way to get an object of this class.
     *
     * @return RM_Taxes_Type_Amount
     */
    public static function getInstance(){
        if (self::$_instance == null) {
            self::$_instance = new RM_Taxes_Type_DailyPerPerson();
        }
        return self::$_instance;
    }

    /**
     * This is a private constructor, use getInstance instead
     */
    protected function  __construct() {}

    /**
     * Calculate taxes value
     *
     * @param RM_Taxes_Row $taxRow
     * @param float $amount Total price amount before taxes
     * @return float
     */
    public function calculate(RM_Taxes_Row $taxRow, $amount, $detail){
        $adults = $detail->getPersons()->getAdults();
        $children = $detail->getPersons()->getChildren();
        $Infants = $detail->getPersons()->getInfants();
        $totalPersons = $adults + $children + $Infants;
        $dayCount = count($detail->getPeriod()->getDays());
        return ($taxRow->amount * $dayCount) * $totalPersons;
    }

    /**
     * Method to get a string representation of this object
     *
     * @param RM_Taxes_Row $taxRow
     * @param float $amount
     */
    public function toString(RM_Taxes_Row $taxRow){
        $config = new RM_Config();
        return $config->getValue('rm_config_currency_symbol').$taxRow->amount;
    }
}