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
 * Global interface for all captcha libraries, that user will be able to choose
 *
 * @access      public
 * @author      Rob/Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 * @todo        add compressing of the produced combined CSS files. Currently these are very small
 * so the priority is very low.
 */
interface RM_Captcha_Interface {

    /**
     * Generate and returns HTML code of captcha to show on the page     
     * @return string - HTML code
     */
    function getHTML();

    /**
     * Validate captcha code. There is no any extra parameter to pass a code,
     * 'cause captcha manager itself should check POST/GET/REQUEST directly
     *     
     * @return bool - TRUE is captcha code is right, FALSE otherwise
     */
    function validate();
}