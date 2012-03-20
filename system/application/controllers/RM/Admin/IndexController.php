<?php
/**
 * ResMania - Reservation System Framework http://resmania.com
 * Copyright (C) 2011  ResMania Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 *
 * Index Controller - handles home page
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_IndexController extends RM_Controller {

    /**
     * this logs to the sytem log the application load time
     * NOTE: this code runs once at boot time so it is the ideal place to implecate
     * things that need a run-once at startup
     *
     * @return array that will be encoded to JSON
     */
    public function logloadingtimeJsonAction(){
        RM_Log::toLog('Admin end loading time: '.$this->_getParam('time'), RM_Log::INFO);

        // update load status
        $model = new RM_System();
        $data = $model->setRunStatus(1);

        // check for new modules or plugins
        $extensions = RM_Environment::getInstance()->getOutOfDateExtensions();
        
        // make a string of modules that have updates available
        $moduleNames = false;
        if (!empty($extensions['modules'])){
            foreach ($extensions['modules'] as $module){
                $moduleNames[]=$module;
            }
            $moduleNames = implode(",", $moduleNames);
        }

        // make a string of plugins that have updates available
        $pluginNames = false;
        if (!empty($extensions['plugins'])){
            foreach ($extensions['plugins'] as $plugins){
                $pluginsNames[]=$plugins;
            }
            $pluginsNames = implode(",", $pluginsNames);
        }

        $config = new RM_Config();
        $licenceKey = false;
        if ($config->getValue('rm_config_licensekey') !== ""){ $licenceKey = true; }

        return array('data' => array('success' => true, 'moduleupdates'=>$moduleNames, 'pluginupdates'=>$pluginNames, 'licensekey'=>$licenceKey));
    }

    /*
     * This provides a keep alive to the server and also runs any GUI updates
     * One such function is the Un-read Reservation Count.
     *
     */
    public function pingJsonAction(){

        // get the reservation count
        $model = new RM_Reservations();
        $UnReadCount = $model->getUnreadCount();
        if ($UnReadCount>0) {
            $UnReadCount = "&nbsp;<span class='RM_Menu_Badge_Count'><div class='RM_Menu_Badge_Count_text'>".$UnReadCount."</div></span><span class='RM_Menu_Badge_Count_right'></span>";
        } else {
            $UnReadCount = "";
        }

        // return the JSON Data
        return array('data'=>
            array ('success' => true,
            'time' => time(),
            'count' => $UnReadCount,
            'encoded' => true
            )
        );
    }

    // Home page functions
    public function homepageJsonAction() {
        return array(
        'data' => array('success' => true)
        );
    }


    /**
     * Load the Help Text
     *
     * @return JSON
     */
    public function helpJsonAction() {
        $text = $this->_getParam('text');
        $chunks = explode('.', $text);

        $helpText = $chunks[count($chunks) - 1];
        unset($chunks[count($chunks) - 1]);

        $helpTextSection = implode('.', $chunks);

        $helpTranslator = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_HELP);
        $helpTranslatedText = $helpTranslator->_($helpTextSection, $helpText);

        return array(
            'data' => $helpTranslatedText,
            'encoded' => true
        );
    }

    public function indexAction() {}

    public function editAction() {}

    /**
     * Updates the maximised/minimised state of the admin GUI
     *
     * @return JSON
     */
    public function updatemaxminstateJsonAction(){
        $GUIstate = $this->_getParam('GUIstate');

        $model = new RM_Config;
        $fields = $model->fetchAll();

        foreach ($fields as $field){
            if ($field->id=="rm_config_admin_gui_maximised"){
                $field->value = $GUIstate;
                $result = $field->save();
            }
        }

        if ($result){

            if($GUIstate){
                $url = RM_Environment::getInstance()->getRouter()->_('', '', array("index3"=>true));
            } else {
                $url = RM_Environment::getInstance()->getRouter()->_('', '', array());
            }

            return array(
                'data' => array('success' => true, 'url' => $url )
            );
        } else {
            return array(
                'data' => array('success' => false)
            );
        }
    }    
}