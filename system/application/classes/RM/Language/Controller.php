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
 *
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Language_Controller extends RM_Controller
{    
    /**
     * This method for internal use, for checking translation
     * @example
     * administrator/index.php?option=com_resmania&act=Language&task=testJson&main=en&test=ru&type=rm_translate_main
     * it will combine 2 language no admin end and will check what language constants are in 'en' but not in 'ru' translation
     * the same method is also for user end
     */
    public function testJsonAction()
    {
        ob_clean();
        $type = $this->_getParam('type',RM_Environment::TRANSLATE_MAIN);
        $adapter = RM_Environment::getInstance()->getTranslation($this->_getParam('type',$type))->getAdapter();
        $enMessages = $adapter->getMessages($this->_getParam('main','en_GB'));
        $ruMessages = $adapter->getMessages($this->_getParam('test','ru_RU'));

        $diffMessages = array();
        foreach ($enMessages as $key => $value)
        {
            list($name, $section) = $this->_parseKey($key);

            if (isset($ruMessages[$key]) == false || $ruMessages[$key] == $enMessages[$key]) {
                if (isset($diffMessages[$section]) == false) {
                    $diffMessages[$section] = array();
                }
                $diffMessages[$section][$name] = $value;
            }
        }

        foreach ($diffMessages as $section => $messages)
        {
            echo "[".$section."] <br/>";
            foreach ($messages as $key => $value) {
                echo "$key=\"\" ;".$value." <br/>";
            }
            echo "<br/>";
        }
        die();
    }

    private function _parseKey($key)
    {
        $chunks = explode('.', $key);
        $name = $chunks[count($chunks) - 1];
        unset($chunks[count($chunks) - 1]);
        return array($name, implode('.', $chunks));
    }
}
