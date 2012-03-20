<?php
class RM_Form_Row extends RM_Row {
    /**
     * @var RM_Form_Validator
     */
    protected $_validator;

    /**
     * Standard zend method for abstract row chulds to initialize object while constructor invokes.
     */
    public function init(){
        $validatorClassname = 'RM_Form_Validator_'.ucfirst($this->id);
        $this->_validator = new $validatorClassname($this);        
    }

    /**
     * Validate current form using request information
     *
     * @param Zend_Request_Interface $request
     * @return bool
     */
    public function validate($request){
        return $this->_validator->validate($request);
    }

    /**
     * Returns array of text error messages in selected locale
     *     
     * @return array
     */
    public function getErrors(){
        return $this->_validator->getErrors();
    }

    /**
     * Returns array of translated error messages
     *
     * @param array $errors
     * @param string $locale
     * @return array
     */
    public function getErrorMessages($errors, $locale = null){
        $errorMessages = array();
        $translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_ERRORS);
        if (!is_array($errors)) {
            $errors = array($errors);
        }
        foreach ($errors as $errormsg){
            $errorMessages[] = $translate->_('User.Form.'.ucfirst($this->id).'.Validation', $errormsg, $locale);
        }
        return $errorMessages;
    }

    /**
     * Returns view state as an array with arrays of panel ID's
     *
     * @return array in format <column number> => array(panelID_1, panelID_2, ...);
     */
    function getState($unitTypeID = null){
        return $this->_getForm($unitTypeID)->getState();
    }

    function deletePanel($panelID, $unitTypeID = null){
        return $this->_getForm($unitTypeID)->deletePanel($panelID);
    }

    private function _getForm($unitTypeID = null){
        if ($unitTypeID == null) {
            $unitTypeID = RM_UnitTypes::DEFAULT_TYPE;
        }

        $unitTypesModel = new RM_UnitTypes();
        $unitType = $unitTypesModel->find($unitTypeID)->current();

        $unitTypeFormsModel = new RM_UnitTypeForms();
        $unitTypeForm = $unitTypeFormsModel->fetchBy($this, $unitType);

        return $unitTypeForm;
    }

    public function getForm($unitTypeID = null){
        return $this->_getForm($unitTypeID);
    }
}