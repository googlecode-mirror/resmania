<?php
/**
 * Model class for taxes
 *
 * @access 	public
 * @author 	Valentin
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link	http://developer.resmania.com/api
 * @since  	08-2009
 */
class RM_Taxes extends RM_Model_Multilingual
{
    protected $_name = 'rm_taxes';
    protected $_rowClass = 'RM_Taxes_Row';

    /**
     * Returns all enabled taxes
     *
     * @return Zend_Db_Table_Rowset
     */
    function getEnabled()
    {
        return $this->fetchAll('enabled=1');
    }

    /**
     * Returns all enabled taxes for a unit that should be assigned
     *
     * @param RM_Unit_Row $unit
     * @return Zend_Db_Table_Rowset with RM_Taxes_Row objects
     */
    function getByUnit(RM_Unit_Row $unit)
    {
        return $this->fetchAll(
            $this->select()
                ->union(array(
                    $this->select()->setIntegrityCheck(false)
                        ->from(array('c' => 'rm_taxes'))
                        ->where('enabled=1')
                        ->joinInner(array('uc' => 'rm_unit_taxes'), 'uc.tax_id = c.id AND uc.unit_id = '.$unit->id, array()),
                    $this->select()
                        ->from(array('d' => 'rm_taxes'))
                        ->where('global=1')
                        ->where('enabled=1')
                ))
        );
    }

    /**
     * Returns all taxes
     *
     * @param string $order
     * @param int $count
     * @param int $offset     
     * @return Zend_Db_Table_Rowset
     */
    function getAll($order, $count, $offset){
        $select = $this->select();        
        $select->order($order);

        if ($count !== null){
            $select->limit($count, $offset);
        }

        return $this->fetchAll($select);
    }
}