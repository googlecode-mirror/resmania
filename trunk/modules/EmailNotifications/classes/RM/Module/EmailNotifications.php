<?php
class RM_Module_Emailnotifications extends RM_Module implements RM_Notifications_Observer
{
    public function  __construct()
    {
        $this->name = 'EmailNotifications';        
    }    

    public function deleteLanguage($iso)
    {
        parent::deleteLanguage($iso);
        $model = new RM_EmailNotifications;
        $model->deleteLanguage($iso);
    }

    public function addLanguage($iso)
    {
        parent::addLanguage($iso);
        $model = new RM_EmailNotifications;
        $model->addLanguage($iso);
    } 


    public function getNode()
    {
        $std = new stdClass;

        $std->id = $this->name.'_ListJson_NoAjax';
        $std->text = $this->getName();

        $std->iconCls = 'RM_modules_leaf_icon';
        $std->leaf = 'true';
        return $std;
    }
    
    public function getConfigNode()
    {
        return null;
    }

    /**
     * Main function to fire event defined in RM_Notifications_Observer interface
     *
     * @param string $eventName
     * @param mixed $eventData
     */
    public function notify($eventName, $eventData)
    {
        $emailNotificationsModel = new RM_EmailNotifications();
        $emailNotifications = $emailNotificationsModel->fetchByName($eventName);

        $className = RM_EmailNotifications_Event_Handler::getClassName($eventName);
        if (class_exists($className) == false){
            return;
        }

        foreach ($emailNotifications as $emailNotification) {
            if (!$emailNotification->enabled) {
                continue;
            }
            $handler = new $className($emailNotification, $eventData);
            $handler->notify();
        }
    }      

    /**
     * Send email to user
     *
     * @param string $emailAddress
     * @param string $subject   the subject string
     * @param string $message   the message string
     * @return  object/boolean  if failed will return false otherwise boolean false will be returned.
     * @example
     *          $e = new RM_Module_Emailnotifications();
     *          $result = $e->email(RM_User_Row $user, "This is the email subject", "This is the message body");
     *
     */
    public function email($emailAddress, $subject, $message)
    {
        $configModel = new RM_Config();

        $mail = new Zend_Mail('UTF-8');
        $mail->setType(Zend_Mime::MULTIPART_MIXED);
        $mail->addTo($emailAddress);
        $mail->setFrom(
            $configModel->getValue('rm_config_email_settings_mailfrom'),
            $configModel->getValue('rm_config_email_settings_fromname')
        );
        $mail->setBodyHtml($message);
        $mail->setSubject($subject);

        $emailType = $configModel->getValue('rm_config_email_settings_mailer');

        try {
            if ($emailType == 'PHP') {
                return $mail->send();
            } else {
                $smtpConfig = array(
                    'auth' => 'Login',
                    'username' => $configModel->getValue('rm_config_email_settings_smtpuser'),
                    'password' => $configModel->getValue('rm_config_email_settings_smtppass'),
                    'port' => $configModel->getValue('rm_config_email_settings_smtpport')
                );
                if ($configModel->getValue('rm_config_email_settings_smtpsecure') != "") {
                    $smtpConfig['ssl'] = strtolower($configModel->getValue('rm_config_email_settings_smtpsecure'));
                }
                return $mail->send(new Zend_Mail_Transport_Smtp(
                    $configModel->getValue('rm_config_email_settings_smtphost'),
                    $smtpConfig
                ));
            }
        } catch (Zend_Mail_Exception $e) {
            RM_Log::toLog("Notification error: ".$e->getMessage());
            return false;
        }        
    }

    public function install()
    {
        parent::install();

        $languageModule = new RM_Languages();
        $languages = $languageModule->fetchAll();
        require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'EmailNotifications.php');
        $model = new RM_EmailNotifications();
        foreach ($languages as $language){
            if ($language->iso !== 'en_GB') {
                $model->addLanguage($language->iso);                
            }
        }

        //Currently we just added extra columns and need to clean cache
        Zend_Db_Table_Abstract::getDefaultMetadataCache()->clean();

        //Copy en_GB default value to all other languages
        $emailNotification = $model->fetchByName('ReservationCompleteSuccessful', RM_EmailNotifications::REGULAR_USER);
        foreach ($languages as $language){
            $iso = $language->iso;
            if ($iso !== 'en_GB') {
                $emailNotification->$iso = $emailNotification->en_GB;
            }
        }
        $emailNotification->save();
    }

    /**
     * Send email notification to System Admin email
     *
     * @deprecated
     * @param string    $fromAddress  the email from address
     * @param string    $fromName     the email from name
     * @param string    $subject      the subject text
     * @param string    $message      the message text
     * @return  object/boolean  if failed will return false otherwise boolean false will be returned.
     */
    public function AdminNotify($fromAddress, $fromName, $subject, $message)
    {
        $configModel = new RM_Config();

        $mail = new Zend_Mail('UTF-8');
        $mail->addTo($configModel->getValue('rm_config_administrator_email'));
        $mail->setFrom(
            $fromAddress,
            $fromName
        );
        $mail->setBodyText($message);
        $mail->setSubject($subject);

        $emailType = $configModel->getValue('rm_config_email_settings_mailer');

        try {
            if ($emailType == 'PHP') {
                return $mail->send();
            } else {
                $smtpConfig = array(
                    'auth' => 'Login',
                    'username' => $configModel->getValue('rm_config_email_settings_smtpuser'),
                    'password' => $configModel->getValue('rm_config_email_settings_smtppass'),
                    'port' => $configModel->getValue('rm_config_email_settings_smtpport')
                );
                if ($configModel->getValue('rm_config_email_settings_smtpsecure') != "") {
                    $smtpConfig['ssl'] = strtolower($configModel->getValue('rm_config_email_settings_smtpsecure'));
                }
                return $mail->send(new Zend_Mail_Transport_Smtp(
                    $configModel->getValue('rm_config_email_settings_smtphost'),
                    $smtpConfig
                ));
            }
        } catch (Zend_Mail_Exception $e) {
            RM_Log::toLog("Notification error: ".$e->getMessage());
            return false;
        }
    }
}
