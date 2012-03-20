<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Locale */
require_once 'Zend/Locale.php';

/** Zend_Translate_Adapter */
require_once 'Zend/Translate/Adapter.php';

/**
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class RM_Translate_Adapter_Text extends Zend_Translate_Adapter
{
    private static $_commonSection = 'Common';
    
    private static $_globalSection = 'RM'; //global section for admin and user end
    
    const FOLDERS = 'folders';

    /**
     * Add translations
     *
     * This may be a new language or additional content for an existing language
     * If the key 'clear' is true, then translations for the specified
     * language will be replaced and added otherwise
     *
     * @param  array|Zend_Config $options Options and translations to be added
     * @throws Zend_Translate_Exception
     * @return Zend_Translate_Adapter Provides fluent interface
     */
    public function addTranslation($options = array())
    {
        $tempOptions = $options;        
        foreach ($tempOptions['content'] as $content) {
            $options['content'] = $content;
            parent::addTranslation($options);
        }
        return $this;
    }

    /**
     * Load translation data
     *
     * @param  string|array  $data
     * @param  string        $locale  Locale/Language to add data for, identical with locale identifier,
     *                                see Zend_Locale for more information
     * @param  array         $options OPTIONAL Options to use
     */
    protected function _loadTranslationData($data, $locale, array $options = array())
    {
        if (isset($this->_data) == false) {
            $this->_data = array();
        }

        if (!file_exists($data)) {
            return $this->_data;
        }
        $inidata = parse_ini_file($data, true);

        $options = array_merge($this->_options, $options);
        if (($options['clear'] == true) ||  !isset($this->_data[$locale])) {
            $this->_data[$locale] = array();
        }

        foreach ($inidata as $section => $data){
            $moduleName = ucfirst(strtolower(RM_Environment::getConnector()->getModule()));
            $sectionChunks = explode('.', $section);
            if ($sectionChunks[0] != $moduleName && 
                $sectionChunks[0] != self::$_commonSection &&
                $sectionChunks[0] != self::$_globalSection) continue;

            foreach ($data as $key => $value) {
                $this->_data[$locale][$section.'.'.$key] = $value;
            }
        }

        return $this->_data;
    }

    /**
     * returns the adapters name
     *
     * @return string
     */
    public function toString()
    {
        return "Text";
    }

    public function _($sectionId, $messageId = null, $locale = null)
    {
        return $this->translate($sectionId, $messageId, $locale);
    }

    /**
     * Translates the given string
     * returns the translation
     *
     * @see Zend_Locale
     * @param  string             $sectionId Section string
     * @param  string             $messageId Translation string
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with
     *                                       locale identifier, @see Zend_Locale for more information
     * @return string
     */
    public function translate($sectionId, $messageId = null, $locale = null){
        if ($messageId == null && $sectionId){
            $messageId = $sectionId;
            $sectionId = self::$_commonSection;            
        }

        $message = $sectionId.'.'.$messageId;
        $translatedMessage = parent::translate($message, $locale);
        if ($translatedMessage == $message) {
            return $messageId;
        }
        return $translatedMessage;
    }

    /**
     * Returns all available translations from this adapter in input section
     * If no locale is given, the actual language will be used
     * If 'all' is given the complete translation dictionary will be returned
     *
     * @param  string $sectionId
     * @param  string|Zend_Locale $locale (optional) Language to return the messages from
     * @return array
     */
    public function getSectionMessages($sectionId, $locale = null)
    {
        if ($locale === 'all') {
            $result = array();
            foreach ($this->_translate as $localeTranslation){
                foreach ($localeTranslation as $key => $value){
                    if (strpos($key, $sectionId) === 0) {
                        $result[$locale][$sectionId][str_replace($sectionId.'.', '', $key)] = $value;
                    }
                }
            }
            return $result;
        }

        if ((empty($locale) === true) or ($this->isAvailable($locale) === false)) {
            $locale = $this->_options['locale'];
        }
        $localeMessages = $this->_translate[$locale];
        $result = array();
        foreach ($localeMessages as $key => $value) {
            if (strpos($key, $sectionId) === 0) {
                $result[str_replace($sectionId.'.', '', $key)] = $value;
            }
        }
        return $result;
    }       
}
