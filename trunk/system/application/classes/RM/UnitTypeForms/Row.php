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
 * @access      public
 * @author      Rob/Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_UnitTypeForms_Row extends RM_Row {
    /**
     * Returns view state as an array with arrays of panel ID's
     *
     * @return array in format <column number> => array(panelID_1, panelID_2, ...);
     */
    function getState(){
        $formStateJSON = $this->state;
        $formState = Zend_Json::decode($formStateJSON);

        $formPanelModel = new RM_FormPanels();
        $panelColumns = array();
        foreach ($formState as $columnNumber => $column){
            $panelColumns[$columnNumber] = array();
            if (is_array($column) == false) continue;
            foreach ($column as $panel){
                $panelID = RM_Form_Naming_Manager::generatePanelID($panel['xtype']);
                $panel = $formPanelModel->find($panelID)->current();
                $panelColumns[$columnNumber][] = $panel;
            }
        }

        return $panelColumns;
    }

    /**
     * Delete panel with input ID from internal form state
     *
     * @param string $panelID
     * @return bool - TRUE if panel was found and deleted, FALSE if panel was not in the state
     */
    function deletePanel($panelID){
        $formState = Zend_Json::decode($this->state);
        foreach ($formState as $columnNumber => $column) {
            if (is_array($column) == false) continue;
            foreach ($column as $key => $panel){
                $currentPanelID = RM_Form_Naming_Manager::generatePanelID($panel['xtype']);
                if ($currentPanelID == $panelID) {
                    unset($formState[$columnNumber][$key]);
                    $this->state = Zend_Json::encode($formState);
                    return true;
                }
            }
        }
        return false;
    }
}