<?php
class RM_Module_FormDesigner extends RM_Module {

    public function  __construct() {
        $this->name = 'FormDesigner';        
    }

    public function getNode(){
        $std = new stdClass;

        $std->id = $this->name.'_controlpanelJson_EditJson';
        $std->text = $this->getName();
        $std->disabled = false;
        $std->iconCls = 'RM_modules_leaf_icon';

        $nodes = array();
        
        $formModel = new RM_Forms();
        $globalForms = $formModel->getAllGlobal();

        foreach ($globalForms as $form) {
            $nodeStd = new stdClass;
            $nodeStd->text = $form->name;
            $nodeStd->id = 'FormDesigner_EditJson_'.$form->id;
            $nodeStd->formID = $form->id;
            $nodeStd->unitTypeID = RM_UnitTypes::DEFAULT_TYPE;
            $nodeStd->leaf = true;
            $nodeStd->iconCls = "RM_module_formdesigner_leaf_icon";
            $nodes[] = $nodeStd;
        }

        $unitTypesModel = new RM_UnitTypes();        
        $nonGlobalForms = $formModel->getAllNonGlobal();

        $types = $unitTypesModel->fetchAll();
        foreach ($types as $type) {
            $nodeUnit = new stdClass;
            $nodeUnit->text = $type->getName();
            $nodeUnit->leaf = false;
            $nodeUnit->expanded = false;
	        $nodeUnit->disabled = true;
            $nodeUnit->children = array();
            foreach ($nonGlobalForms as $form) {
                $nodeStd = new stdClass;
                $nodeStd->text = $form->name;
                $nodeStd->id = 'FormDesigner_EditJson_'.$form->id.'_'.$type->id;
                $nodeStd->formID = $form->id;
                $nodeStd->unitTypeID = $type->id;
                $nodeStd->leaf = true;
                $nodeStd->iconCls = "RM_module_formdesigner_leaf_icon";
                $nodeUnit->children[] = $nodeStd;
            }
            $nodes[] = $nodeUnit;
        }


        if (count($nodes) == 0) {
            $std->leaf = 'true';
        } else {
            $std->expanded = 'true';
            $std->children = $nodes;
        }

        return $std;
    }    

    public function getConfigNode()
    {       
        return null;
    }


}
