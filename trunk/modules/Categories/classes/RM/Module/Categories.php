<?php
class RM_Module_Categories extends RM_Module implements RM_Search_Advanced_Interface, RM_Unit_Copy_Interface
{
    function copyInformation(RM_Unit_Row $original, RM_Unit_Row $copy)
    {
        $unitCategoriesModel = new RM_UnitCategories();
        $unitCategories = $unitCategoriesModel->getByUnit($original);
        foreach ($unitCategories as $unitCategory) {
            $unitCategoryCopyData = $unitCategory->toArray();
            $unitCategoryCopyData['unit_id'] = $copy->id;
            $unitCategoryCopy = $unitCategoriesModel->createRow($unitCategoryCopyData);
            $unitCategoryCopy->save();
        }
    }

    /**
     * Returns all unit ids that are passed criteria option
     *
     * @param RM_Unit_Search_Criteria $criteria
     * @return array, false
     */
    function getAdvancedSearchUnitIDs(RM_Unit_Search_Criteria $criteria){
        if (!$criteria->categories || count($criteria->categories) == 0) return false;

        $model = new RM_UnitCategories();
        $units = $model->getByCategories($criteria->categories);
        
        $unitIDs = array();
        foreach ($units as $row) {
            $unitIDs[] = $row->unit_id;
        }

        //we need to add code to support Group Module
        //we need to include every unit ID of a groups that main unit in group assigned to a category
        if (class_exists('RM_Groups')) {
            $groupUnitIDs = array();
            $groupsModel = new RM_Groups();
            $unitsModel = new RM_Units();
            foreach ($unitIDs as $unitID) {
                $unit = $unitsModel->get($unitID);
                if ($groupsModel->isMain($unit)) {
                    $units = $groupsModel->getGroupUnitsByMain($unit);
                    foreach ($units as $unit) {
                        $groupUnitIDs[] = $unit->getId();
                    }
                }
            }
            $unitIDs = array_merge($unitIDs, $groupUnitIDs);
            $unitIDs = array_unique($unitIDs);
        }

        return $unitIDs;
    }

    public function  __construct() {
        $this->name = 'Categories';        
    }

    public function install()
    {
        parent::install();

        //1. move template for advanced search panel
        //the standard algorithm already moves userdata/view templates into plugin separate direstory thats why we need to move this file into main template directory
        $rootPath = RM_Environment::getConnector()->getRootPath();
        $pluginFolderPath = implode(DIRECTORY_SEPARATOR, array(
            $rootPath,
            'RM',
            'userdata',
            'modules',
            $this->name,
            'views',
            'user',
            'scripts',
            'Search',
            'advanced',
            'category_advanced.phtml'
        ));
        $userdataFolderPath = implode(DIRECTORY_SEPARATOR, array(
            $rootPath,
            'RM',
            'userdata',
            'views',
            'user',
            'scripts',
            'Search',
            'advanced',
            'category_advanced.phtml'
        ));
        return rename($pluginFolderPath, $userdataFolderPath);
    }
    
    public function uninstall()
    {
        parent::uninstall();

        //1. remove template for advanced search panel
        $rootPath = RM_Environment::getConnector()->getRootPath();
        $file = implode(DIRECTORY_SEPARATOR, array(
            $rootPath,
            'RM',
            'userdata',
            'views',
            'user',
            'scripts',
            'Search',
            'advanced',
            'category_advancedsearch.phtml'
        ));
        RM_Filesystem::deleteFile($file);

        //2. remove information about this panel from database in form->state field
        $formModel = new RM_Forms();
        $form = $formModel->find('advancedsearch')->current();
        $deleted = $form->deletePanel('category_advancedsearch');
        if ($deleted) {
            $form->save();
        }
    }    

    public function addLanguage($iso)
    {
        parent::addLanguage($iso);
        $model = new RM_Categories();
        $model->addLanguage($iso);
    }

    /**
     * Invokes after user delete language, make some changes for price module
     *
     * @param string $iso
     */
    public function deleteLanguage($iso)
    {
        parent::deleteLanguage($iso);
        $model = new RM_Categories();
        $model->deleteLanguage($iso);
    }

    public function getConfigNode()
    {
        return null;
    }
}
