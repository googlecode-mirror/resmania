<?php
/**
 * This is a parent class for every Event handler class.
 */
abstract class RM_EmailNotifications_Event_Handler {
    /**
     * @var mixed
     */
    protected $_eventData;

    /**
     * @var RM_EmailNotifications_Row
     */
    protected $_emailNotification;

    /**
     * Create a name of event handler class from event name (valid Zend autoloader class name).
     * Example:
     * event_name = ReservationCompleteSuccessfully
     * will transform to
     * class_name = RM_EmailNotifications_Event_Handler_ReservationCompleteSuccessfully
     *
     * @param  $eventName
     * @return string
     */
    public static function getClassName($eventName)
    {
        return 'RM_EmailNotifications_Event_Handler_'.$eventName;
    }

    /**
     * Revert class name into Event name
     *
     * @see self::getClassName
     * @param  $className
     * @return string
     */
    public static function getEventName($className)
    {
        return str_replace('RM_EmailNotifications_Event_Handler_', '', $className);
    }

    /**
     * @param RM_EmailNotifications_Row $eventNotification
     * @param mixed $eventData - this is a mixed data, so only an event handler class and the place where we will
     * invoke this event (in core or in modules) should know the structure of this data
     * @return void
     */
    public function __construct(RM_EmailNotifications_Row $emailNotification, $eventData)
    {
        $this->_emailNotification = $emailNotification;
        $this->_eventData = $eventData;
    }

    /**
     * @param  $eventData
     * @return void
     */
    public function notify()
    {
        $iso = $this->_getLocale();
        $message = $this->_emailNotification->$iso;
        if ($message == null) {
            return;
        }
        $dwoo = new Dwoo();
        $template = new Dwoo_Template_String($message);
        $data = new Dwoo_Data();
        $data = $this->_assign($data);
        $parsedMessage = $dwoo->get($template, $data);
        return $this->_send($parsedMessage);
    }

    /**
     * This method could be overriden by child classes 'cause we need to setup locale individually, 
     * not only the current one.
     *
     * @return string - iso code
     */
    protected function _getLocale()
    {
        return RM_Environment::getInstance()->getLocale();
    }

    /**
     * This method will send email to related recipient (depending on various options)
     *
     * @param  $message
     * @return void
     */
    protected function _send($message)
    {
        switch($this->_emailNotification->destination){
            case RM_EmailNotifications::REGULAR_USER:
                $user = $this->_eventData->getUser();
                $this->_doSend($message, $user->email);
                break;
            case RM_EmailNotifications::ADMINISTRATOR:
                $configModel = new RM_Config();
                $adminEmail = $configModel->getValue('rm_config_administrator_email');
                $this->_doSend($message, $adminEmail);
                break;
        }
    }

    protected function _doSend($message, $emailAddress)
    {
        $module = new RM_Module_Emailnotifications();
        $eventName = self::getEventName(get_class($this));
        $subject = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN)->_('User.Emailnotifications.Subjects', $eventName);
        $module->email($emailAddress,$subject,$message);
    }

    /**
     * This method is for assign any of the eventData values (or any environment values) to template
     *
     * @abstract
     * @param Dwoo_Data $data
     * @return void
     */
    abstract protected function _assign(Dwoo_Data $data);
}
 
