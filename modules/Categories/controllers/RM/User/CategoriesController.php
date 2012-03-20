<?php
class RM_User_CategoriesController extends RM_Controller
{
    function treeJsonAction()
    {
        $iso = $this->_translate->getAdapter()->getLocale();

        $selectedCategoriesIDs = RM_Reservation_Manager::getInstance()->getCriteria()->categories;
        if (is_array($selectedCategoriesIDs) == false){
            $selectedCategoriesIDs = array();
        }

        $model = new RM_Categories();        
        $jsonCategories = $this->_createNodes($model, RM_Categories::ROOT_NAME, $selectedCategoriesIDs, $iso);
        return array(
            'data' => $jsonCategories
        );
    }

    function _createNodes($model, $parentID, $selectedCategoriesIDs, $iso)
    {
        $categories = $model->fetchByParent($parentID);
        $jsonCategories = array();
        foreach ($categories as $category){
            $jsonCategory = new stdClass();
            $jsonCategory->id = $category->id;
            $jsonCategory->text = $category->$iso;
            $jsonCategory->iconCls = "unit-category-icon";
            if (in_array($category->id, $selectedCategoriesIDs)){
                $jsonCategory->checked = true;
            } else {
                $jsonCategory->checked = false;
            }
            $subCategories = $this->_createNodes($model, $category->id, $selectedCategoriesIDs, $iso);
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

    function indexAction()
    {
        $this->view->test = "this is another test information";
    }
}