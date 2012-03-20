<?php
class RM_Controller_Action_Helper_ViewRenderer extends Zend_Controller_Action_Helper_ViewRenderer
{
    /**
     * Get module directory
     *
     * @throws Zend_Controller_Action_Exception
     * @return string
     */
    public function getModuleDirectory()
    {
        $module    = $this->getModule();
        $moduleDir = $this->getFrontController()->getControllerDirectory($module);
        if ((null === $moduleDir)) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception('ViewRenderer cannot locate module directory');
        }
        $this->_moduleDir = dirname($moduleDir[0]);
        return $this->_moduleDir;
    }
    
    public function getInflector()
    {
        if (null === $this->_inflector) {
            /**
             * @see Zend_Filter_Inflector
             */
            require_once 'Zend/Filter/Inflector.php';
            /**
             * @see Zend_Filter_PregReplace
             */
            require_once 'Zend/Filter/PregReplace.php';
            /**
             * @see Zend_Filter_Word_UnderscoreToSeparator
             */
            require_once 'Zend/Filter/Word/UnderscoreToSeparator.php';
            $this->_inflector = new Zend_Filter_Inflector();
            $this->_inflector->setStaticRuleReference('moduleDir', $this->_moduleDir) // moduleDir must be specified before the less specific 'module'
                 ->addRules(array(
                     ':module'     => array('StringToLower'),
                     ':controller' => array(new Zend_Filter_Word_UnderscoreToSeparator('/'), new Zend_Filter_PregReplace('/\./', '-')),
                     ':action'     => array(new Zend_Filter_PregReplace('#[^a-z0-9' . preg_quote('/', '#') . ']+#i', '-'))
                 ))
                 ->setStaticRuleReference('suffix', $this->_viewSuffix)
                 ->setTargetReference($this->_inflectorTarget);
        }

        // Ensure that module directory is current
        $this->getModuleDirectory();

        return $this->_inflector;
    }
}
