<?php
/**
 * Model class for GMaps
 *
 * @access 	public
 * @author 	Rob
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link	http://developer.resmania.com/api
 * @since  	11-2009
 */
class RM_GMaps extends RM_Model {
    protected $_name = 'rm_gmaps';

    /**
     * Returns GMaps config
     *
     * @return Zend_Db_Table_Rowset
     */
    function getSettings(){
        return $this->fetchAll("id='1'");
    }
}