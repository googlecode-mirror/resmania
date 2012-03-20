<?php
/**
* Admin Taxes Controller
*
* This handles all AJAX requests from the Admin GUI Taxes page.
* These methods will create an AJAX response containing JSON data. The JSON
* data is read by the JS code and rendered into interface.
*
* @access 	public
* @author 	Rob, Valentin
* @copyright	ResMania 2009 all rights reserved.
* @version	1.0
* @link		http://developer.resmania.com/api
* @since  	05-2009
*/
class RM_Admin_TaxesController extends RM_Controller
{
    /**     
     * @param   request start
     * @param   request limit
     * @param   request sort
     * @param   request dir
     * @return array
     */
    function listJsonAction(){
        $iso = $this->_getParam('iso', RM_Environment::getInstance()->getLocale());
        $offset = $this->_getParam('start');
        $count = $this->_getParam('limit');
        $sort = $this->_getParam('sort', 'id');
        if ($sort == 'units') {
            $sort = 'global';
        }

        $direction = $this->_getParam('dir', 'DESC');
        $order = $sort . ' ' . $direction;        

        $model = new RM_Taxes;
        $total = count($model->getAll($order, null, null));
        $rows = $model->getAll($order, $count, $offset);

        $data = array();
        foreach ($rows as $row) {
            $dataRow = $row->toArray();
            $dataRow['name'] = $row->getName($iso);
            $data[] = $dataRow;
        }

        $json = new stdClass;
        $json->total = $total;
        $json->data = $data;

        return array(
            'data' => $json
        );
    }

    /**     
     * @param   request data  tax rows information (if 'id'=0 this is a new row)
     * @see plugins/taxes/js/edit.js
     * @return 	json    json array information: success or failure
     */
    function updateJsonAction()
    {        
        $dataJson = $this->_getParam('data', '[]');
        $data = Zend_Json::decode($dataJson);

        $iso = $this->_getParam('iso', RM_Environment::getInstance()->getLocale());

        $unitTaxesModel = new RM_UnitTaxes();
        $model = new RM_Taxes;
        foreach ($data as $row){
            if (isset($row['name'])) {
                $row[$iso] = $row['name'];
                unset($row['name']);
            }
            $unitIDs = explode(',', $row['units']);
            unset($row['units']);
            if ($row['id'] == 0) {
                unset($row['id']);                
                $taxID = $model->insert($row);
                $tax = $model->find($taxID)->current();
            } else {
                $tax = $model->find($row['id'])->current();
                if ($tax == null) continue;
                foreach ($row as $key => $value) {
                    $tax->$key = $value;
                }
                $tax->save();
            }
            $unitTaxesModel->insertRows($tax, $unitIDs);
        }

        return array('data' => array('success' => true));
    }        

    /**
     * @param   request ids  taxes rows ids
     * @see plugins/taxes/js/edit.js
     * @return 	json    json array information: success of failure
     */
    function deleteJsonAction()
    {
        $taxIDs = $this->_getParam('ids', array());

        $model = new RM_Taxes();
        foreach ($taxIDs as $taxID) {
            $tax = $model->find($taxID)->current();
            if ($tax !== null) {
                $tax->delete();
            }            
        }

        return array('data' => array('success' => true));
    }

    /**
     * this creates the JSON for the unit selection on the coupons grid
     * @return array used to construct the json result
     */
    public function unitlistJsonAction()
    {
        $dao = new RM_Units;
        $units = $dao->getAll(new RM_Unit_Search_Criteria())->toArray();

        // add *all units* selection to the start of the list
        $units[] = array(
            'id' => '0',
            'name' => RM_Environment::getInstance()->getTranslation()->_('Admin.Taxes.Edit','AllUnits')
        );
        return array(
            'data' => $units
        );
    }
}