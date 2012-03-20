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
class RM_Form_Validator_Summary extends RM_Form_Validator
{
    /**
     * List of all panel ids that need to be validated
     * @var array
     */
    private $_neeedToBeValidatedPanelsIDs = array(
        'terms_summary'
    );

    /**
     * @var Zend_Request_Interface
     */
    private $_request;

    /**
     * @param RM_Form_Row $form
     */
    function __construct($form)
    {
        parent::__construct($form);
    }

    /**
     * Validate information from reservation manager object, 'cause we are on the final step
     * before payment process.
     *
     * @param Zend_Request_Interface $request
     * @return bool
     */
    function validate($request)
    {
        $this->_request = $request;
        $valid = true;

        $state = $this->_form->getState();
        foreach ($state as $column) {
            foreach ($column as $panel) {
                if (in_array($panel->id, $this->_neeedToBeValidatedPanelsIDs)){
                    $methodName = '_'.$panel->id.'_validate';
                    $valid &= $this->$methodName();
                }
            }
        }

        $manager = RM_Reservation_Manager::getInstance();
        $savedDetails = $manager->getAllDetails();
        $unitModel = new RM_Units();
        foreach ($savedDetails as $details){
            $valid &= $unitModel->isAvailableUnitbyDate($details->getUnit(), $details->getPeriod());
        }

        return $valid;
    }

    private function _terms_summary_validate()
    {
        $validatorChain = new RM_Validate('Terms');
        $result = $validatorChain->addValidator(new Zend_Validate_NotEmpty())->isValid($this->_request->getParam('terms', null));
        if (!$result) {
            $this->_errors = array_merge($this->_errors, $validatorChain->getErrors());
        }
        return $result;
    }
}