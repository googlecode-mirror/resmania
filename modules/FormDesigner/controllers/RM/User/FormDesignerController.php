<?php
class RM_User_FormDesignerController extends RM_Controller {

    var $_LiveChatUserGroupID = 98;
    
    public function allpanelsAction()
    {
        $unitModel = new RM_Units();

        $unitTypeID = $this->_getParam('unitTypeID');
        $unitTypeModel = new RM_UnitTypes();
        $unitType = $unitTypeModel->find($unitTypeID)->current();
        $unit = $unitModel->getAll(new RM_Unit_Search_Criteria(array('type' => $unitType, 'includeOptionUnits'=>true, 'includeExcursionUnits'=>true)))->current();

        if ($unitTypeID == RM_UnitTypes::DEFAULT_TYPE && $unit == null) {
            $unit = $unitModel->getAll(new RM_Unit_Search_Criteria())->current();
        }
        if ($unit == null){
            $unit = $unitModel->createRow();
        }
        $this->view->unit = $unit;

        $this->view->admin = true; // this is required by captcha
        $panelModel = new RM_FormPanels();
        $panels = $panelModel->fetchAll();
        $this->view->panels = $panels;      
    }
}