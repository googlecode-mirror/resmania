<?php
/**
* Admin Prices Controller
*
* This handles all AJAX requests from the Admin GUI Prices Section.
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
class RM_Admin_DailyPricesController extends RM_Controller
{
    /**
     * Returns the list configuration for reservations
     *
     * This creates a JS variable containing the list configuration/setup for
     * the unit price list. This is implicated in unit_list.js
     *     
     * @return   js variable RM.Common.DailyPrices_MinStay, used directly by unit_list.js
     */
    public function listJsAction()
    {        
        $periods = RM_DailyPrices_Period::getAllTranslated();

        $jsPeriods = array();
        foreach ($periods as $key => $value) {
            $jsPeriods[] = "['$key', '$value']";
        }
        
        return "RM.Common.DailyPrices_MinStay = [".implode(',', $jsPeriods)."];RM.Common.DailyPrices_MaxStay = [".implode(',', $jsPeriods)."];";
    }    

    /**
     * Edit Price config for edit.js
     *
     * Copy information from database rm_daily_prices_config.admin_view_edit
     * for form inputs creation.
     *
     * @param   request start  offset for the SQL
     * @param   request limit  limit for the SQL
     * @param   request sort  sort for the SQL
     * @param   request dir  sorting direction for the SQL: ASC/DESC
     * @param   request id  unit id
     * @return 	array information in format:
     * {total: <total rows number>, data: [{<price_row>}, {<price_row>, ...}]}
     */
    function listpricesJsonAction()
    {
        $offset = $this->_getParam('start');
        $count = $this->_getParam('limit');
        $sort = $this->_getParam('sort', 'id');
        $direction = $this->_getParam('dir', 'DESC');
        $order = $sort . ' ' . $direction;

        $unitID = $this->_getParam('id', '');
        $unitModel = new RM_Units;
        $unit = $unitModel->find($unitID)->current();        
        
        $model = new RM_UnitDailyPrices;
        $total = count($model->getByUnit($unit, $order, null, null, true));
        $prices = $model->getByUnit($unit, $order, $count, $offset, true);

        $json = new stdClass;
        $json->total = $total;
        $json->data = $prices;

        return array(
            'data' => $json
        );
    }

    /**
     * @param   request id  unit id
     * @see modules/prices/js/unit_list.js
     * @return 	json    json array informatio in format:
     * {fields: [<view>, <view>, ...]}
     */
    function listJsonAction()
    {       
        $unitID = $this->_getParam('id', '');
        $unitModel = new RM_Units;
        $unit = $unitModel->find($unitID)->current();        

        $config = new RM_UnitDailyPricesConfig();
        $configObject = new stdClass();
        $configObject->dailyEnabled = $config->fetchValueByUnit($unitID, 'daily_price_enabled');
        $configObject->midweekEnabled = $config->fetchValueByUnit($unitID, 'midweek_days');
        $configObject->monthlyEnabled = $config->fetchValueByUnit($unitID, 'monthly_price_enabled');
        $configObject->weekendEnabled = $config->fetchValueByUnit($unitID, 'weekend_price_enabled');

        //We need all price row columns infor
        $jsonFields = array();

        $rowModel = new RM_DailyPricesColumns();
        $rows = $rowModel->fetchAllEnabled($configObject);

        //$rowModel = new RM_UnitDailyPricesColumns();
        //$rows = $rowModel->getByUnit($unit);
        
        foreach ($rows as $row){
            if ($row->admin_view == '') continue;
            $jsonFields[] = $row->admin_view;
        }

        $json = "{            
            fields : [".implode(',', $jsonFields)."]            
        }";

        return array(
            'data' => $json,
            'encoded' => true
        );
    }

    /**
     * @param   request unit_id  unit id
     * @param   request data  price rows information (if 'id'=0 this is a new row)
     * @see modules/prices/js/unit_list.js
     * @return 	json    json array information: success or failure
     */
    function updateJsonAction()
    {
        $unitID = $this->_getParam('unit_id');
        if ($unitID == null) {
            return array('data' => array('success' => 'false'));
        }

        $dataJson = $this->_getParam('data', '[]');
        $data = Zend_Json::decode($dataJson);
        
        $model = new RM_UnitDailyPrices;
        foreach ($data as $row) {
            //We need to check 'id' value, if it's equal 0 we have a new price row.
            if ($row['id'] == 0) {
                unset($row['id']);
                $row['unit_id'] = $unitID;
                $model->insert($row);
            } else {
                $model->updateFromGUI($row);                
            }
        }

        return array('data' => array('success' => true));
    }

    /**
     * @see modules/prices/js/config.js
     * @return 	json    json array information in format:
     * {fields: [{name: <name>, value: <value>, metainfo: <view metainfo>}]}
     */
    function configJsonAction()
    {
        $model = new RM_DailyPricesConfig();
        $fields = $model->fetchAll();

        $jsonFields = array();
        foreach ($fields as $field) {
            $jsonField = array();
            $jsonField[] = "name : '".$field->name."'";
            $jsonField[] = "value : '".$field->value."'";
            if ($field->metainfo != '') {
                $jsonField[] = 'metainfo : '.$field->metainfo;
            }
            $jsonFields[] = '{'.implode(',', $jsonField).'}';
        }

        return array(
            'data' => '{ fields: ['.implode(',', $jsonFields).'] }',
            'encoded' => true
        );
    }

    /**
     * @param   request daily_prices_config  prices config information
     * @see modules/DailyPrices/js/config.js
     * @return 	json    json array information: success or failure
     */
    function configupdateJsonAction()
    {        
        $fields = $this->_getParam('daily_prices_config', array());
        
        $model = new RM_DailyPricesConfig();
        foreach ($fields as $name => $value) {
            $configRow = $model->find($name)->current();
            $configRow->value = $value;
            $configRow->save();
        }

        return array('data' => array('success' => true));
    }

    /**
     * @param   request ids  unit prices rows ids
     * @see modules/prices/js/unit_list.js
     * @return 	json    json array information: success of failure
     */
    function deleteJsonAction()
    {
        $priceIDs = $this->_getParam('ids', '');
        $model = new RM_UnitDailyPrices();

        foreach ($priceIDs as $priceID) {
            $price = $model->find($priceID)->current();
            $price->delete();
        }
        
        return array('data' => array('success' => true));
    }

    /**     
     * @param   request id  unit id
     * @param   request unit_prices_config  unit price config information
     * @see modules/prices/js/unit_config.js
     * @return 	json    json array information: success or failure
     */
    function updateunitconfigJsonAction()
    {
        $unitID = $this->_getParam('id', '');
        $values = $this->_getParam('unit_daily_prices_config', array());

        $model = new RM_UnitDailyPricesConfig();
        $model->deleteByUnit($unitID);

        foreach ($values as $name => $value)
        {
            $row = array(
                'name' => $name,
                'value' => $value,
                'unit_id' => $unitID
            );

            $model->insert($row);
        }

        return array('data' => array('success' => true));
    }

    /**
     * @param   request id  unit id
     * @see modules/prices/js/unit_config.js
     * @return 	json    json array information in format:
     * {unit_id: <unit_id>, fields: [{name: <name>, value: <value>, metainfor: <metainfo>}]}
     */
    function unitconfigJsonAction()
    {
        $unitID = $this->_getParam('id', '');
        $model = new RM_UnitDailyPricesConfig();
        $fields = $model->fetchByUnit($unitID);

        foreach ($fields as $field) {
            $jsonField = array();
            $jsonField[] = "name : '".$field->name."'";
            $jsonField[] = "value : '".($field->unit_value !== null ? $field->unit_value : $field->default_value)."'";
            if ($field->metainfo != '') {
                $jsonField[] = 'metainfo : '.$field->metainfo;
            }
            $jsonFields[] = '{'.implode(',', $jsonField).'}';
        }

        return array(
            'data' => '{ unit_id: '.$unitID.', fields: ['.implode(',', $jsonFields).'] }',
            'encoded' => true
        );
        
    }

    function getpriceJsonAction(){

        $unitIDs = $this->_getParam('ids');
        $start_datetime = $this->_getParam('start_datetime');
        $end_datetime = $this->_getParam('end_datetime');
        $adults = $this->_getParam('adults', 1);
        $children = $this->_getParam('children', 0);
        $infants = $this->_getParam('infants', 0);

        $persons = new RM_Reservation_Persons(array("adults"=>$adults,"children"=>$children,"infants"=>$infants));

        $unitsDAO = new RM_Units();

        $stardateObj = new RM_Date(strtotime($start_datetime));
        $enddateObj = new RM_Date(strtotime($end_datetime));
        $periodObj = new RM_Reservation_Period($stardateObj , $enddateObj);

        $priceSystem = new RM_Module_DailyPrices();

        $units = explode(",",$unitIDs);

        $taxSystem = RM_Environment::getInstance()->getTaxSystem();        
        $tax = 0;
        
        foreach ($units as $uid){
            $unitObj = $unitsDAO->get($uid);
            $information = new RM_Prices_Information($unitObj, $periodObj, $persons);
            try {
                $subtotal = $subtotal + $priceSystem->getTotalUnitPrice($information);
            } catch (Exception $e){
                // no return needed
            }
            $tax += $taxSystem->calculateTotalTax($unitObj, $subtotal);
        }

        // get currency symbol
        $config = new RM_Config();
        $currency_symbol = $config->get('rm_config_currency_symbol');
                

        // calculate the total
        $total = $subtotal + $tax;

        return array(
            'data' => '{ data: [{
                                info: "Subtotal", value: "'.$currency_symbol['rm_config_currency_symbol'].' '.$subtotal.'"
                        },{
                                info: "Tax", value: "'.$currency_symbol['rm_config_currency_symbol'].' '.$tax.'"
                        },{
                                info: "Total", value: "'.$currency_symbol['rm_config_currency_symbol'].' '.$total.'"
                        }]
                    }',
            'encoded' => true
        );
    }
}
