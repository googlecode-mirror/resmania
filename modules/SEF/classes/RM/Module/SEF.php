<?php
class RM_Module_SEF extends RM_Module implements RM_SEF_Manager_Interface
{
    public function  __construct() {
        $this->name = 'SEF';
    }

    public function getNode(){
        return null;
    }
    
    public function getConfigNode()
    {
        return null;
    }

    public function getRouter(){        
        $plugins = $this->getAllPlugins(true);
        if (count($plugins) == 0) return null;

        foreach ($plugins as $plugin) {
            $pluginObject = RM_Plugin_Manager::getPlugin($plugin->name);
            $router = $pluginObject->getRouter();
            if ($router !== null) {
                return $router;
            }
        }

        return null;
    }
}