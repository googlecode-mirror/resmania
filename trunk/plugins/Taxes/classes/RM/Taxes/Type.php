<?php
/**
 * Parent class for all tax types
 *
 * @access 	public
 * @author 	Valentin
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link	http://developer.resmania.com/api
 * @since  	08-2009
 */
abstract class RM_Taxes_Type {    
    /**
     * Calculate taxes value
     *
     * @param RM_Taxes_Row $taxRow
     * @param float $total Total price amount before taxes
     * @return float
     */
    abstract function calculate(RM_Taxes_Row $taxRow, $amount, $details);

    /**
     * Method to get a string representation of this object
     *
     * @param RM_Taxes_Row $taxRow
     * @param float $amount
     */
    abstract function toString(RM_Taxes_Row $taxRow);

    /**
     * Return all type names
     *
     * todo: this code need to be refactored to implements automatic type class parsing
     * @return array
     */
    public static function getAllNames(){
        $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
        return array(
            'amount' => $translate->_('Admin.Taxes.Type', 'Amount'),
            'percentage' => $translate->_('Admin.Taxes.Type', 'Percentage'),
            'daily' => $translate->_('Admin.Taxes.Type', 'Daily'),
            'dailyperperson' => $translate->_('Admin.Taxes.Type', 'DailyPerPerson')
        );
    }    
}