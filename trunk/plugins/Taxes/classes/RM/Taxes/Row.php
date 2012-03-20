<?php
/**
 * Tax row class
 *
 * This row class uses in the model to represent a tax.
 *
 * @access 	public
 * @author 	Valentin
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link	http://developer.resmania.com/api
 * @since  	08-2009
 */
class RM_Taxes_Row extends RM_Row
{
    /**     
     * @var RM_Taxes_Type
     */
    protected $_type;

    public function init()
    {
        $typeClassName = 'RM_Taxes_Type_'.ucfirst(strtolower($this->type));
        $this->_type = call_user_func(array($typeClassName, 'getInstance'));
    }

    /**
     * Calculate taxes
     *
     * @param float $amount total amount before taxes applyed
     * @return float total amount of tax
     */
    public function calculate($amount, $detail)
    {
        $total = $this->_type->calculate($this, $amount, $detail);
        return RM_Environment::getInstance()->roundPrice($total);
    }

    /**
     * Return name of the extra
     *
     * @param string $iso ISO code
     * @return string
     */
    public function getName($iso = null)
    {
        if ($iso == null) {
            $iso = RM_Environment::getInstance()->getLocale();
        }
        return $this->$iso;
    }

    /**
     * Returns the column/value data as an array and parse all assigned units
     * in csv format in 'units' field.
     *
     * @return array
     */
    public function toArray()
    {
        $dataRow = parent::toArray();
        if ($this->global == 1) {
            $dataRow['units'] = array(0);
        } else {
            $unitTaxesModel = new RM_UnitTaxes();
            $unitIDs = array();
            $unitTaxes = $unitTaxesModel->getByTax($this);
            foreach ($unitTaxes as $unitTax){
                $unitIDs[] = $unitTax->unit_id;
            }
            $dataRow['units'] = implode(',', $unitIDs);
        }
        return $dataRow;
    }

    /**
     * Method to get a string representation of this object
     *
     * @param RM_Taxes_Row $taxRow
     * @param float $amount
     */
    public function toString(){
        return $this->_type->toString($this);
    }
}