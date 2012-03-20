<?php
/**
 * Taxes plugin class
 *
 * Main class for all taxes manipulations
 * @access 	public
 * @author 	Valentin
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link	http://developer.resmania.com/api
 * @since  	08-2009
 */
class RM_Plugin_Property extends RM_Plugin {
    /**
     * This method will return node object for main admin menu tree.
     * Every child classes could overload this method to return any of the node object.
     * If there is no need to present a plugin in the main admin tree overloaded method should return NULL
     *
     * @return stdClass | null
     */
    public function getNode(){
        return null;
    }

    public function addLanguage($iso)
    {
        //we don't have any language constants
        return true;
    }

    /**
     * Invokes after user delete language, make some changes for price module
     *
     * @param string $iso
     */
    public function deleteLanguage($iso)
    {
        //we don't have any language constants
        return true;
    }

    /**
     * This method will return node object for configuration admin menu tree.
     * Every child classes could overload this method to return any of the node object.
     * If there is no need to present a plugin in the configuration admin tree overloaded method should return NULL
     *
     * @return stdClass | null
     */
    public function getConfigNode(){
        return null;
    }    

    public function uninstall() {
        parent::uninstall();
        $unitModel = new RM_Units();
        $unitModel->update(
            array(
                'type_id' => RM_UnitTypes::DEFAULT_TYPE,
                'published' => 0
            ),
            'type_id=2'
        );
    }
}
