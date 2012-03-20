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
class RM_User_SearchController extends RM_User_Controller {
    public function advancedAction()
    {        
        $formModel = new RM_Forms();
        $form = $formModel->find('advancedsearch')->current();

        $this->view->state = $form->getState();
        $this->view->form = $form;
        
        if ($this->_getParam('from') == 'search') {
            ob_clean();            
            echo $this->_helper->layout->render();
            echo $this->view->render('Search/advanced.phtml');
            die();
        }
    }

    public function advancedvalidateJsonAction()
    {
        $this->_withoutView();
        $formModel = new RM_Forms();
        $form = $formModel->find('advancedsearch')->current();

        $formvalid = $form->validate($this->getRequest());
        $data = $this->_getParam('search', array());

        // count records to be returned
        $criteria = new RM_Unit_Search_Criteria($data);
        $criteria->publishedOnly = true;
        
        $unitModel = new RM_Units();
        $totalUnits = $unitModel->getAll($criteria)->count();
        // count records end

        if ($formvalid == false) {
            $errors = $form->getErrors();
            $returnData = array(
                'data' => array(
                    'success' => false,
                    'errors' => $errors,
                    'count' => $totalUnits
                    )
                );
        } else {
            $errors = $form->getErrors();
            $returnData = array(
                'data' => array(
                    'success' => true,
                    'errors' => null,
                    'count' => $totalUnits
                    )
                );
        }

        return $returnData;
    }

    public function resetlistcriteriaAction(){
        $criteria = new RM_Unit_Search_Criteria($data);
        $criteria->publishedOnly = true;
        RM_Reservation_Manager::getInstance()->resetCriteria()->setCriteria($criteria);
        $this->_redirect('Unit', 'list');
    }
}