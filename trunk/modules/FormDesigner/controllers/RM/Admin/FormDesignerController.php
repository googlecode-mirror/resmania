<?php
class RM_Admin_FormDesignerController extends RM_Controller {
    function editJsonAction(){
        $formID = $this->_getParam('formID');
        $formModel = new RM_Forms();
        $form = $formModel->find($formID)->current();

        // check if there are any units
        $unitDAO = new RM_Units();
        $unitTypeID = $this->_getParam('unitTypeID');
        if ($unitTypeID === null) {
            return array(
                'data' => "{ 'error': true }",
                'encoded' => true
            );
        }

        $unitTypeModel = new RM_UnitTypes();
        $unitType = $unitTypeModel->find($unitTypeID)->current();
        $total = $unitDAO->getAll(new RM_Unit_Search_Criteria(array('type' => $unitType)))->count();

        if ($unitTypeID == RM_UnitTypes::DEFAULT_TYPE && $total == 0) {                        
            $total = $unitDAO->getAll(new RM_Unit_Search_Criteria())->count();
        }

        if ($total == 0) {
            return array(
                'data' => "{ 'error': true }",
                'encoded' => true
            );
        }

        $panelModel = new RM_FormPanels();
        $panels = $panelModel->fetchByForm($formID);

        $jsonPanels = array();
        foreach ($panels as $panel) {
            $jsonPanels[] = '{id : "'.$panel->id.'", name : "'.$panel->name.'"}';
        }

        $unitTypeFormModel = new RM_UnitTypeForms();
        $unitTypeForm = $unitTypeFormModel->fetchBy($form, $unitType);

        $json = "{
            formID: '".$formID."',
            formName: '".$form->name."',
            columns: ".$unitTypeForm->columns.",
            max_columns: ".$form->max_columns.",
            column1width: ".$unitTypeForm->column1width.",
            column2width: ".$unitTypeForm->column2width.",
            column3width: ".$unitTypeForm->column3width.",
            panels: [".implode(',', $jsonPanels)."],
            formState: ".$unitTypeForm->state.",
            unitTypeID: $unitTypeID,
            unitTypeName: '".$unitType->getName(RM_Environment::getInstance()->getLocale())."'
        }";
        
        return array(
            'data' => $json,
            'encoded' => true
        );
    }

    /**
     * Saving form state into database
     *
     * @param formID
     * @param value
     * @return json information for the front-end
     */
    function savestateJsonAction()
    {
        $formID = $this->_getParam('formID');
        $state = $this->_getParam('state');
        $columns = $this->_getParam('columns');
        $column1width = $this->_getParam('column1width');
        $column2width = $this->_getParam('column2width');
        $column3width = $this->_getParam('column3width');
        $unitTypeID = $this->_getParam('unitTypeID', RM_UnitTypes::DEFAULT_TYPE);

        $model = new RM_Forms();
        $form = $model->find($formID)->current();

        $unitTypesModel = new RM_UnitTypes();
        $unitType = $unitTypesModel->find($unitTypeID)->current();
        if ($unitType == null) {
            $unitTypeID = RM_UnitTypes::DEFAULT_TYPE;
            $unitType = $unitTypesModel->find($unitTypeID)->current();
        }

        $unitTypeFormsModel = new RM_UnitTypeForms();        
        if ($unitTypeFormsModel->check($form, $unitType)) {
            $unitTypeForm = $unitTypeFormsModel->fetchBy($form, $unitType);
        } else {
	        //We need to create a new row for this unit type	    
            $unitTypeForm = $unitTypeFormsModel->createRow();
            $unitTypeForm->form_id = $form->id;
            $unitTypeForm->unit_type_id = $unitTypeID;
	    }

        $unitTypeForm->columns = $columns;
        $unitTypeForm->column1width = $column1width;
        $unitTypeForm->column2width = $column2width;
        $unitTypeForm->column3width = $column3width;
        $unitTypeForm->state = $state;
        $result = $unitTypeForm->save();

        $resultJson = false;        
        if ($result) {
            $resultJson = true;
        }

        return array('data' => array('success' => $resultJson));
    }

    /**
     * Returns all needed js functions for naming generation.
     * @return string - HTML code 
     */
    function namingJsAction(){
        return RM_Form_Naming_Manager::getAllJavascriptCode();
    }

    /**
     * Returns all needed js code for formdesigner edit page.
     *
     * @return js string
     */
    function editJsAction(){
        $jsCode = "";        
        $jsCode.= $this->_getFormPanels();        
        return $jsCode;
    }

    /**
     * Returns all javascript for form panels
     *
     * @return string js code
     */
    private function _getFormPanels(){
        $jsCode = "Ext.namespace('Ext.rm', 'Ext.rm.Formdesigner');";

        $model = new RM_FormPanels();
        $panels = $model->fetchAll();
        $jsCode.= 'RM.Pages.Functions.Formdesigner_InitPanels = function (){';
        foreach ($panels as $panel) {
            $jsCode.= $this->_getPanelJsCode($panel);
        }
        $jsCode.= '}
        ';
        return $jsCode;
    }   

    private function _getPanelJsCode($panel){
        $panelXType = RM_Form_Naming_Manager::generatePanelXType($panel->id);
        $panelClassName = RM_Form_Naming_Manager::generatePanelClassName($panel->id);
        $buttonID = RM_Form_Naming_Manager::generateButtonID($panelXType);
        $divID = RM_Form_Naming_Manager::generateDivID($panel->id);
        

        $js = "
            $panelClassName = Ext.extend(Ext.Panel, {
                title: '".$panel->name."',
                style: 'padding:0px 0px 0px 0px',
                autoHeight: true,
                closeable: true,
                collapsible: false,
                html: document.getElementById('rm_formdesigner_data_iframe').contentWindow.document.getElementById('$divID').innerHTML,
                //preventBodyReset: true,
                plugins: Ext.ux.PortletPlugin,
                baseCls: 'RM_form_designer_container',
                tools: [{
                    id:'gear',
                    handler: function(){
                        RM.Pages.Functions.Formdesigner_SettingsWindow('".$panel->id."','".$panel->name."','".$panel->settings."');
                    }
                }],
                listener: {'close': function(){Ext.getCmp('$buttonID').setDisable(false);}}
            });
            Ext.reg('$panelXType', $panelClassName);
        ";
        return $js;
    }


    /*
     * This updates the form designer panel settings
     */
    function updatepanelsettingsJsonAction(){
        $align = $this->_getParam('rm_formdesigner_panelsettings_alignment_hidden');
        $width = $this->_getParam('rm_formdesigner_panelsettings_width');
        $form_id = $this->_getParam('form_id');

        $settings = array("align"=>$align, "width"=>$width);

        $panelModel = new RM_FormPanels();
        $form = $panelModel->find($form_id)->current();
        $form->settings = Zend_Json::encode($settings);
        $result = $form->save();

        $resultJson = false;
        if ($result) {
            $resultJson = true;
        }

        return array('data' => array('success' => $resultJson));
    }

    /**
     * Get all form panels returns JSON with title and link to JS
     *
     */
    public function helppageAction(){
        ob_clean();
        $view = new RM_View();
        $view->addScriptPath(implode(
            DIRECTORY_SEPARATOR,
            array(
                RM_Environment::getConnector()->getRootPath(),
                'RM',
                'userdata',
                'modules',
                'FormDesigner',
                'views',
                'admin',
                'scripts',
                'FormDesigner',
            )
        ));

        echo $view->render('help.phtml');
        die();
    }

    /**
     * Form designer JSON
     * currently unused, but maybe used later for other settings on this page.
     *
     * @return JSON
     */
    public function controlpanelJsonAction(){
        return array('data' => array('success' => true));
    }

    /**
     * Get the json data for the form designer list on the form designer control panel
     *
     * @return JSON
     */
    public function controlpanelpagelistJsonAction(){
        $formsModel = new RM_Forms();
        $allGlobal = $formsModel->getAllGlobal();
        $allNonGlobal = $formsModel->getAllNonGlobal();

        $jsonPanels = array();
        $count=0;
        foreach ($allGlobal as $form){
            $jsonPanels[] = array("id"=>$form->id, "name"=>$form->name);
            $count++;
        }
        foreach ($allNonGlobal as $form){
            $jsonPanels[] = array("id"=>$form->id, "name"=>$form->name);
            $count++;
        }

        // on the js we need a button next to each name, to clear the db entry for the form.

        $json = new stdClass;
        $json->total = $count;
        $json->data = $jsonPanels;

        return array(
            'data' => $json
        );
    }

    /**
     * this will reset the selected form panel state
     *
     * @TODO    we need to change this so that just a single form can be cleared
     */
    public function resetformsJsonAction(){
        //$formIDs = $this->_getParam('ids',array());
        $formsModel = new RM_Forms();
        $formsModel->resetForms();
        return array('data' => array('success' => true));
    }

    /**
     * this will clear the selected form panel state
     *
     * @TODO    we need to change this so that just a single form can be cleared
     */
    public function clearformsJsonAction(){
        //$formIDs = $this->_getParam('ids',array());
        $formsModel = new RM_Forms();
        $formsModel->clearForms();
        return array('data' => array('success' => true));
    }

    /**
     * load user override css file contents
     */
    public function editcssJsonAction(){
        $formsModel = new RM_Forms();
        $cssData=$formsModel->getCSSFile();
        return array('data' => array('success' => true, 'text'=>$cssData));
    }

    /**
     * Save the user override css file contents
     */
    public function savecssJsonAction(){
        $contents = $this->_getParam('contents');
        $formsModel = new RM_Forms();
        $result = $formsModel->saveCSSFile($contents);
        return array('data' => array('success' => $result));
    }
}
