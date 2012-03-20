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
class RM_Config_Field_View_Select extends RM_Config_Field_View {
	var $values;
	
	public function getHTML($selectedValue){
		$html = '<select id="'.$this->id.'" name="'.$this->name.'">';
		
		foreach ($this->values as $value => $title) {
			$html.= '<option name="'.$value.'" '. $value == $selectedValue ? 'selected="selected"' : '' .'>'.$title.'</option>';
		}
		
		$html.= '</select>';		
		return $html;
	}		
}
?>