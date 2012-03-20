<?php
/**
 * Fasade class for the price modules
 *
 * @access 	public
 * @author 	webformatique
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link        http://developer.resmania.com/api
 * @since  	05-2009
 */
class RM_Module_Payments extends RM_Module implements RM_Payments_Interface
{
    public function  __construct() {
        $this->name = 'Payments';        
    }

    public function isRestricted(RM_Plugin_Row $plugin)
    {
        if ($plugin->module_name != $this->name) {
            return false;
        }

        $currentActive = $this->getAllPlugins(true)->count();
        if ($plugin->enabled == 1 && $currentActive <= 1) {
            throw new RM_Exception(RM_Environment::getInstance()->getTranslation()->_('Admin.Payments.Restriction', 'LastError'));    
        }

        return false;
    }

    public function getControllerName(){
        return $this->name;
    }

    public function getNode(){
        return null;
    }

    public function getConfigNode()
    {        
        return null;
    }
}