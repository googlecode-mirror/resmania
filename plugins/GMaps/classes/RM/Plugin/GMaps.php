<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class RM_Plugin_GMaps extends RM_Plugin implements RM_Unit_Details_Tray_Interface, RM_Unit_List_Tray_Interface, RM_Search_Advanced_Interface
{   
    /**
     * Public constructor
     */
    public function  __construct()
    {
        $this->name = 'GMaps';
    }    

    public function getNode(){
        return null;
    }

    /**
     * Returns all unit ids that are passed criteria option
     *
     * @param RM_Unit_Search_Criteria $criteria
     * @return array, false
     */
    function getAdvancedSearchUnitIDs(RM_Unit_Search_Criteria $criteria){
        if (!$criteria->map) return false;

        $locationsModel = new RM_UnitLocations();
        $locations = $locationsModel->fetchAll();
        $unitIDs = array();
        foreach ($locations as $location) {
            $unitIDs[] = $location->unit_id;
        }

        return $unitIDs;
    }

    /**
     * This method will invoke while plugin installation process.
     */
    public function install(){
        parent::install();
        
        //1. move template for advanced search panel
        //the standard algorithm already moves userdata/view templates into plugin separate direstory thats why we need to move this file into main template directory
        $rootPath = RM_Environment::getConnector()->getRootPath();
        $pluginFolderPath = implode(DIRECTORY_SEPARATOR, array(
            $rootPath,
            'RM',
            'userdata',
            'plugins',
            $this->name,
            'views',
            'user',
            'scripts',
            'Search',
            'advanced',
            'map_advanced.phtml'
        ));
        $userdataFolderPath = implode(DIRECTORY_SEPARATOR, array(
            $rootPath,
            'RM',
            'userdata',            
            'views',
            'user',
            'scripts',
            'Search',
            'advanced',
            'map_advanced.phtml'
        ));                
        return rename($pluginFolderPath, $userdataFolderPath);
    }

    /**
     * This method will invoke while plugin installation process.
     */
    public function uninstall(){
        parent::uninstall();

        //1. remove template for advanced search panel
        $rootPath = RM_Environment::getConnector()->getRootPath();       
        $file = implode(DIRECTORY_SEPARATOR, array(
            $rootPath,
            'RM',
            'userdata',
            'views',
            'user',
            'scripts',
            'Search',
            'advanced',
            'map_advanced.phtml'
        ));
        RM_Filesystem::deleteFile($file);

        //2. remove information about this panel from database in form->state field
        $formModel = new RM_Forms();
        $form = $formModel->find('advancedsearch')->current();
        $deleted = $form->deletePanel('map_advancedsearch');
        if ($deleted) {
            $form->save();
        }
    }

    public function getHTML(RM_Unit_Row $unit){
        //We need to check does Unit have an address or lan/lat to show a map or not
        $locationsModel = new RM_Locations();
        if ($locationsModel->fetchByUnit($unit->id)->current() == null) return "";

        $icon = RM_Environment::getConnector()->getRootURL().'RM/userdata/plugins/GMaps/images/tray_icon.gif';
        return "<a href='javascript:void(0)' onclick=\"RM_doShadowBox('".RM_Environment::getInstance()->getRouter()->_('GMaps', 'map', array("unit_id"=>$unit->id, "page" => "map.phtml"))."', '', 500, 400, 'iframe' )\" ><img src='".$icon."' border='0'></a>";
    }

    public function getListHTML(RM_Unit_Row $unit){
        //We need to check does Unit have an address or lan/lat to show a map or not
        $locationsModel = new RM_Locations();
        if ($locationsModel->fetchByUnit($unit->id)->current() == null) return "";

        $icon = RM_Environment::getConnector()->getRootURL().'RM/userdata/plugins/GMaps/images/tray_icon.gif';
        return "<a href='javascript:void(0)' onclick=\"RM_doShadowBox('".RM_Environment::getInstance()->getRouter()->_('GMaps', 'map', array("unit_id"=>$unit->id, "page" => "map.phtml"))."', '', 500, 400, 'iframe' )\" ><img width='30px' height='30px' src='".$icon."' border='0'></a>";
    }
}