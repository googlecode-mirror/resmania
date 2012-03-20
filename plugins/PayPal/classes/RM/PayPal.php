<?php
/**
 * Model class for PayPal
 *
 * @access 	public
 * @author 	Rob
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link	http://developer.resmania.com/api
 * @since  	11-2009
 */
class RM_PayPal extends RM_Model {
    protected $_name = 'rm_paypal';

    /**
     * Returns paypal config
     *
     * @return Zend_Db_Table_Rowset
     */
    function getSettings(){
        return $this->fetchAll("id='1'");
    }

}