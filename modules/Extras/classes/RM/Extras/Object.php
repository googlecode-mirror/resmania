<?php
class RM_Extras_Object implements RM_Extras_Object_Interface
{
    /**
     * @var RM_Extras_Row
     */
    protected $_extraRow;
    protected $_value;
    protected $_price = 0;
    protected $_tax = 0;

    public function __construct(RM_Extras_Row $extraRow, $price, $value, $tax = 0)
    {
        $this->_extraRow = $extraRow;
        $this->_price = $price;
        $this->_value = $value;
        $this->_tax = $tax;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function getTax()
    {
        return $this->_tax;
    }

    public function getName($iso = null)
    {
        return $this->_extraRow->getName($iso);
    }

    public function getPrice()
    {
        return $this->_price;
    }

    public function getID()
    {
        return $this->_extraRow->id;
    }

    public function toArray($iso = null)
    {
        $array = $this->_extraRow->toArray($iso);
        $array['price'] = $this->getPrice();
        $array['value'] = $this->getValue();
        $array['tax'] = $this->getTax();
        return $array;
    }
}
