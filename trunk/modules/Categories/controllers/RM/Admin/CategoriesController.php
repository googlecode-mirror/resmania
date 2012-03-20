<?php
class RM_Admin_CategoriesController extends RM_Controller {   
    function treeunitJsonAction()
    {
        $iso = $this->_translate->getAdapter()->getLocale();
        
        $model = new RM_Categories();
        $unitID = $this->_getParam('unit_id', 0);
        $unitCategories = $model->fetchByUnit($unitID);
        $unitCategoriesIDs = array();
        foreach ($unitCategories as $unitCategory){
            $unitCategoriesIDs[] = $unitCategory->id;
        }        

        $jsonCategories = $this->_createUnitNodes($model, RM_Categories::ROOT_NAME, $unitCategoriesIDs, $iso);

        return array(
            'data' => $jsonCategories
        );       
    }

    function _createUnitNodes($model, $parentID, $unitCategoriesIDs, $iso)
    {        
        $categories = $model->fetchByParent($parentID);
        
        $jsonCategories = array();
        foreach ($categories as $category){
            $jsonCategory = new stdClass();
            $jsonCategory->id = $category->id;
            $jsonCategory->text = $category->$iso;
            $jsonCategory->iconCls = "unit-category-icon";

            if (in_array($category->id, $unitCategoriesIDs)){
                $jsonCategory->checked = true;
            } else {
                $jsonCategory->checked = false;
            }

            $subCategories = $this->_createUnitNodes($model, $category->id, $unitCategoriesIDs, $iso);
            if (count($subCategories) > 0) {
                $jsonCategory->children = $subCategories;
                $jsonCategory->leaf = 0;
            } else {
                $jsonCategory->leaf = 1;
            }
            
            $jsonCategories[] = $jsonCategory;
        }
        return $jsonCategories;
    }   

    function _createNodes($model, $parentID, $iso)
    {
        $categories = $model->fetchByParent($parentID);

        $jsonCategories = array();
        foreach ($categories as $category){
            $jsonCategory = new stdClass();
            $jsonCategory->id = $category->id;
            $jsonCategory->text = "<b>(".$this->_translate->_('Common', 'Id').":&nbsp;".$category->id.")</b>&nbsp;".$category->$iso;
            $jsonCategory->iconCls = "unit-category-icon";

            $subCategories = $this->_createNodes($model, $category->id, $iso);
            if (count($subCategories) > 0) {
                $jsonCategory->children = $subCategories;
                $jsonCategory->leaf = 0;
            } else {
                $jsonCategory->leaf = 1;
            }

            $jsonCategories[] = $jsonCategory;
        }
        return $jsonCategories;
    }

    function treeJsonAction()
    {
        if ($this->_getParam('cmd') == 'moveTreeNode'){
            return $this->_reorderJson();
        }
        
        $node = $this->_getParam('node', RM_Categories::ROOT_NAME);

        $model = new RM_Categories();
        $categories = $model->fetchByParent($node);

        $iso = $this->_translate->getAdapter()->getLocale();

        $jsonCategories = $this->_createNodes($model, RM_Categories::ROOT_NAME, $iso);

//        $jsonCategories = array();
//        foreach ($categories as $category){
//            $jsonCategory = new stdClass();
//            $jsonCategory->id = $category->id;
//            $jsonCategory->text = $category->$iso;
//            if ($model->fetchByParent($category->id)->count()){
//                $jsonCategory->leaf = 0;
//            } else {
//                $jsonCategory->leaf = 1;
//            }
//            $jsonCategory->cls = "folder";
//
//            $jsonCategories[] = $jsonCategory;
//        }

        if ($node == RM_Categories::ROOT_NAME){
            $jsonCategory = new stdClass();
            $jsonCategory->id = RM_Categories::DISABLED;
            $jsonCategory->text = $this->_translate->_('Admin.Categories.Edit', 'Disabled');
            $subCategories = $this->_createNodes($model, $jsonCategory->id, $iso);
            if (count($subCategories) > 0) {
                $jsonCategory->children = $subCategories;
                $jsonCategory->leaf = 0;
            } else {
                $jsonCategory->leaf = 1;
            }           
            $jsonCategory->cls = "folder";
            $jsonCategory->iconCls = "RM_categories_disabled_icon";
            $jsonCategory->allowDrag = false;
            $jsonCategories[] = $jsonCategory;

            $jsonCategory = new stdClass();
            $jsonCategory->id = RM_Categories::TRASH;
            $jsonCategory->text = $this->_translate->_('Admin.Categories.Edit', 'Trash');            
            $subCategories = $this->_createNodes($model, $jsonCategory->id, $iso);
            if (count($subCategories) > 0) {
                $jsonCategory->children = $subCategories;
                $jsonCategory->leaf = 0;
            } else {
                $jsonCategory->leaf = 1;
            }           
            $jsonCategory->cls = "folder";
            $jsonCategory->iconCls = "RM_categories_trash_icon";
            $jsonCategory->allowDrag = false;
            $jsonCategories[] = $jsonCategory;
        }
        
        return array(
            'data' => $jsonCategories
        );        
    }    

    function _reorderJson()
    {
        $targetNodeID = $this->_getParam('target', RM_Categories::ROOT_NAME);
        $dropedNodeID = $this->_getParam('id', RM_Categories::ROOT_NAME);
        $command = $this->_getParam('point');

        $model = new RM_Categories();
        if (!$command) {
            $targetNodeID = $model->find($targetNodeID)->current()->parent_id;
        }
        
        $model->reorder($targetNodeID, $dropedNodeID);

        $json = array(
            'success' => true
        );
        return array('data' => $json);
    }

    function getJsonAction()
    {
        $id = $this->_getParam('id', 0);
        if ($id == 0) {
            return array('data' => array('success' => false));
        }

        $model = new RM_Categories;
        $category = $model->find($id)->current();

        if ($category == null) {
            return array('data' => array('success' => false));
        }

        $category = $category->toArray();
        $categoryJSON = array();
        
        //We don't need ID and parent ID, 'cause we already have this information from category tree.
        unset($category['id']);
        unset($category['parent_id']);

        foreach ($category as $name => $value) {
            $categoryJSON[] = array(
                'iso' => $name,
                'value' => $value
            );
        }

        return array('data' => $categoryJSON);
    }

    function editJsonAction()
    {
        $languageModel = new RM_Languages();
        $languages = $languageModel->fetchAll()->toArray();

        $json = new stdClass();
        $json->languages = $languages;
        
        return array('data' => $json);
    }

    function assignJsonAction()
    {
        $categoryID = $this->_getParam('category_id', 0);
        $unitID = $this->_getParam('unit_id', 0);
        $checked = $this->_getParam('checked');
        if ($checked === 'false') {
            $checked = false;
        } else {
            $checked = true;
        }
        
        $model = new RM_UnitCategories();
        $row = $model->find($unitID, $categoryID)->current();
        if ($checked) {
            if ($row == null) {
                $data = array(
                    'unit_id' => $unitID,
                    'category_id' => $categoryID
                );
                $model->insert($data);
            }
        } else {
            if ($row !== null) {
                $row->delete();
            }
        }
        
        return array('data' => array('success' => true));
    }

    function updateJsonAction()
    {
        $category = $this->_getParam('rm_category', array());
        if (is_array($category) == false && isset($category['id']) == false) {
            return array('data' => array('success' => false));
        }

        $model = new RM_Categories();
        $result = $model->update($category, 'id="'.$category['id'].'"');
        if ($result == 0) {
            return array('data' => array('success' => false));
        }
        
        return array('data' => array('success' => true));
    }

    function insertJsonAction()
    {
        $parent_id = $this->_getParam('parent_id', 0);

        $model = new RM_Categories();

        $category = array();
        $category['parent_id'] = $parent_id;
        $category[$this->_translate->getAdapter()->getLocale()] = RM_Categories::DEFAULT_NAME;

        $result = $model->insert($category);
        if ($result){
            return array('data' => array('success' => true));
        }
        return array('data' => array('success' => false));
    }

    function deleteJsonAction()
    {
        $catID = $this->_getParam('id', false);

        if (!$catID){
            return array('data' => array('success' => false));
        }
        
        $model = new RM_Categories();
        $model->delete('id='.$catID);
        
        return array('data' => array('success' => true));
    }
}