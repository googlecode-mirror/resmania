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
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_FormPanels extends RM_Model
{
    protected $_name = 'rm_form_panels';
    protected $_rowClass = 'RM_Formpanel_Row';
    protected $_referenceMap    = array(
        'Form' => array(
            'columns'           => 'form_id',
            'refTableClass'     => 'RM_Forms',
            'refColumns'        => 'id'
        )
    );    

    function fetchByForm($formid){
        return $this->fetchAll($this->select()->where('form_id=?', $formid));
    }

    function unsetFromForm($panelID)
    {
        $panel = $this->find($panelID)->current();
        $panelXtype = RM_Form_Naming_Manager::generatePanelXType($panelID);

        $model = new RM_Forms();
        $form = $model->find($panel->form_id)->current()->getForm();
        $formState = Zend_Json::decode($form->state);
        foreach ($formState as $columnKey => $column) {
            foreach ($column as $key => $panel) {
                if ($panel['xtype'] == $panelXtype) {
                    unset($formState[$columnKey][$key]);
                    $form->state = Zend_Json::encode($formState);
                    $form->save();
                    return true;
                }
            }
        }
        return false;
    }
}