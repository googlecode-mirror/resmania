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
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
include_once RM_Environment::getConnector()->getRootPath() . DIRECTORY_SEPARATOR . 'RM' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'recaptcha-php-1.10' . DIRECTORY_SEPARATOR . 'recaptchalib.php';

/**
 * Class bridge between 'recaptchalib' and RM_Captcha_Interface.
 */
class RM_Captcha_Recaptcha implements RM_Captcha_Interface {

    /**
     * Enabled
     *
     * Sets if reCaptcha should be used.
     */
    private $_enabled = true;
    /**
     * Default public key for reCaptcha service
     * @var string
     */
    private $_publickey;
    /**
     * Default private key for reCaptcha service
     * @var string
     */
    private $_privatekey;
    /**
     * Should the request be made over ssl? Default is false.
     * @var bool
     */
    private $_useSSL = false;
    /**
     * Last error string from
     * @var string
     */
    private $_lastError = null;

    /**
     * Contructor
     *
     */
    public function __construct() {
        // get settings
        $config = new RM_Config();
        $this->_enabled = $config->getValue('rm_config_recaptcha_enabled');
        $this->_useSSL = $config->getValue('rm_config_recaptcha_ssl');
        $this->_publickey = $config->getValue('rm_config_recaptcha_publickey');
        $this->_privatekey = $config->getValue('rm_config_recaptcha_privatekey');
    }

    /**
     * Generate and returns HTML code of captcha to show on the page
     * @return string - HTML code
     */
    function getHTML() {
        //TODO: later we need to implement code of getting this error string if the first attemp
        $error = "";
        return recaptcha_get_html($this->_publickey, $error, $this->_useSSL);
    }

    /**
     * Validate captcha code
     * @return bool
     */
    function validate() {
        if (isset($_POST["recaptcha_challenge_field"]) == false) {
            return false;
        }
        if (isset($_POST["recaptcha_response_field"]) == false) {
            return false;
        }

        $response = recaptcha_check_answer(
                        $this->_privatekey,
                        $_SERVER["REMOTE_ADDR"],
                        $_POST["recaptcha_challenge_field"],
                        $_POST["recaptcha_response_field"]
        );

        return (bool) $response->is_valid;
    }

}