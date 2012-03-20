<?php
class RM_Admin_ExtrasController extends RM_Controller
{
    /**
     * this creates the JSON for the unit selection on the coupons grid
     * @return array used to construct the json result
     */
    public function unitlistJsonAction()
    {
        $dao = new RM_Units;
        $units = $dao->getAll(new RM_Unit_Search_Criteria())->toArray();

        $units[] = array(
            'id' => '0',
            'name' => RM_Environment::getInstance()->getTranslation()->_('Admin.Extras.Edit','AllUnits')
        );
        return array(
            'data' => $units
        );
    }

    public function reservationUpdate($detail)
    {
        $reservationDetailID = $detail->id;
        if ($reservationDetailID == null) {
            return false;
        }

        $reservationDetailsModel = new RM_ReservationDetails();
        $reservationDetail = $reservationDetailsModel->find($reservationDetailID)->current();
        if ($reservationDetail == null) {
            return false;
        }

        $summaryModel = new RM_ReservationSummary();
        $extrasModel = new RM_Extras();
        $unitObject = new RM_Units();
        
        $taxSystem = RM_Environment::getInstance()->getTaxSystem();
        $taxes = $taxSystem->getAllTaxes($unitObject->find($reservationDetail->unit_id)->current());
        

        // we need to create a new reservation details object so that the tax can be calculated
        $periodObj = new RM_Reservation_Period(new RM_Date(strtotime($reservationDetail->start_datetime)),new RM_Date(strtotime($reservationDetail->end_datetime)));
        $persons = new RM_Reservation_Persons(array("adults" => $reservationDetail->adults, "children" => $reservationDetail->children, "infants" => $reservationDetail->infants));
        $fullReservationDetails = new RM_Reservation_Details($reservationDetail->findUnit(), $periodObj, $persons, array());

        $extras = $detail->extras;
        foreach ($extras as $extra) {
            $extraRow = $extrasModel->find($extra->id)->current();
            if ($extraRow == null) {
                continue;
            }

            $extraTotal = $extraRow->calculate($reservationDetail);
            $quantity = (int)$extra->value;
            
            // calculate the taxes
            $taxTotal = 0;
            foreach ($taxes as $tax) {
                $taxTotal = $taxTotal + $tax->calculate($extraTotal, $fullReservationDetails);
            }

            // multiply by the selection
            $extraTotal = $quantity * $extraTotal;

            $summaryRow = $summaryModel->getByDetail(
                $reservationDetail,
                RM_Module_Extras::SUMMARY_TYPE,
                $extra->id
            );
            
            // tax
            $summaryTaxRow = $summaryModel->getByDetail(
                $reservationDetail,
                RM_Module_Extras::SUMMARY_TYPE_TAX,
                $extra->id
            );

            // remove the row if the quantity is zero
            if ($summaryRow !== null && $quantity === 0){
                $delResult = $summaryModel->delete("reservation_detail_id='".$reservationDetail->id."' AND type='".RM_Module_Extras::SUMMARY_TYPE."' AND row_id='".$extra->id."'");
                if ($delResult){
                    // delete the tax row
                    $summaryTaxRow->delete("reservation_detail_id='".$reservationDetail->id."' AND type='".RM_Module_Extras::SUMMARY_TYPE_TAX."' AND row_id='".$extra->id."'");
                }
                continue;
            }

            if ($summaryRow !== null) {
                $summaryRow->value = $quantity;
                $summaryRow->total_amount = $extraTotal;
                $summaryRow->save();
            } else {
                if ($quantity >0){
                    $summaryRow = $summaryModel->createRow(array(
                        'reservation_id' => null,
                        'reservation_detail_id' => $reservationDetail->id,
                        'type' => RM_Module_Extras::SUMMARY_TYPE,
                        'row_id' => $extra->id,
                        'value' => $quantity,
                        'total_amount' => $extraTotal,
                        'name' => $extraRow->getName(RM_Environment::getInstance()->getLocale())
                    ));
                    $summaryRow->save();
                }
            }

            // tax
            if ($summaryTaxRow !== null) {
                $summaryTaxRow->value = $quantity;
                $summaryTaxRow->total_amount = $taxTotal * $quantity;
                $summaryTaxRow->save();
            } else {
                if ($quantity >0){
                    $summaryTaxRow = $summaryModel->createRow(array(
                        'reservation_id' => null,
                        'reservation_detail_id' => $reservationDetail->id,
                        'type' => RM_Module_Extras::SUMMARY_TYPE_TAX,
                        'row_id' => $extra->id,
                        'value' => $quantity,
                        'total_amount' => $taxTotal * $quantity,
                        'name' => "Extras Tax"
                    ));
                    $summaryTaxRow->save();
                }
            }
        }
        return true;
    }

    public function reservationupdateJsonAction()
    {
        $details = Zend_Json::decode($this->_getParam('ids', '[]'), Zend_Json::TYPE_OBJECT);
        foreach ($details as $detail) {
            $this->reservationUpdate($detail);
        }
        return array(
            'data' => array('success' => true)
        );
    }
    
    public function calculateJsonAction()
    {
        $value = $this->_getParam('value');
        if ($value === null) {
            return array('data' => array('success' => false));
        }

        $extraID = $this->_getParam('extra_id');
        if ($extraID === null) {
            return array('data' => array('success' => false));
        }
        
        $extrasModel = new RM_Extras();
        $extra = $extrasModel->find($extraID)->current();
        if ($extra === null) {
            return array('data' => array('success' => false));
        }

        $reservationDetailID = $this->_getParam('detail_id');
        if ($reservationDetailID == null) {
            return array('data' => array('success' => false));
        }

        $reservationDetailsModel = new RM_ReservationDetails();
        $reservationDetail = $reservationDetailsModel->find($reservationDetailID)->current();
        if ($reservationDetail == null) {
            return array('data' => array('success' => false));
        }

        $config = new RM_Config();
        $currencySymbol = $config->getValue('rm_config_currency_symbol');

        // calculate the tax due on the extra...
        $extraSubTotal = $extra->calculate($reservationDetail);


        // we need to create a new reservation details object so that the tax can be calculated
        $periodObj = new RM_Reservation_Period(new RM_Date(strtotime($reservationDetail->start_datetime)),new RM_Date(strtotime($reservationDetail->end_datetime)));
        $persons = new RM_Reservation_Persons(array("adults" => $reservationDetail->adults, "children" => $reservationDetail->children, "infants" => $reservationDetail->infants));
        $fullReservationDetails = new RM_Reservation_Details($reservationDetail->findUnit(), $periodObj, $persons, array());

        return array('data' => array(
            'success' => true,            
            'value' =>  $currencySymbol. $extraSubTotal * $value
        ));
    }

    public function listhtmlJsonAction()
    {
        $reservationID = $this->_getParam('id', null);
        if ($reservationID == null) {
            return array('data' => array('success' => false));
        }

        $reservationModel = new RM_Reservations();
        $reservation = $reservationModel->find($reservationID)->current();
        if ($reservation == null) {
            return array('data' => array('success' => false));
        }

        $unitModel = new RM_Units();

        $reservationDetails = $reservation->getDetails();

        $reservationDetailsJson = array();
        foreach ($reservationDetails as $reservationDetail) {
            $unit = $reservationDetail->findUnit();
            $extrasModel = new RM_Extras();
            $extras = $extrasModel->getByUnit($unit);
            if ($extras->count() == 0) {
                continue;
            }

            $config = new RM_Config();
            $currencySymbol = $config->getValue('rm_config_currency_symbol');

            // tax
            $taxSystem = RM_Environment::getInstance()->getTaxSystem();
            $taxes = $taxSystem->getAllTaxes($unit);

            // we need to create a new reservation details object so that the tax can be calculated
            $periodObj = new RM_Reservation_Period(new RM_Date(strtotime($reservationDetail->start_datetime)),new RM_Date(strtotime($reservationDetail->end_datetime)));
            $persons = new RM_Reservation_Persons(array("adults" => $reservationDetail->adults, "children" => $reservationDetail->children, "infants" => $reservationDetail->infants));
            $fullReservationDetails = new RM_Reservation_Details($unit, $periodObj, $persons, array());

            $summaryModel = new RM_ReservationSummary();
            $extrasJson = array();
            foreach ($extras as $extra) {

                // calculate the tax due on the extra...
                $extraSubTotal = $extra->calculate($reservationDetail);

                $taxTotal = 0;
                foreach ($taxes as $tax) {
                    $taxTotal = $taxTotal + $tax->calculate($extraSubTotal, $fullReservationDetails);
                }
                $extraSubTotal = $extraSubTotal + $taxTotal;

                $extraJson = new stdClass();
                $extraJson->id = $extra->id;
                $extraJson->name = $extra->getName(RM_Environment::getInstance()->getLocale());
                $extraJson->min = (int)$extra->min;
                $extraJson->max = (int)$extra->max;
                $extraJson->type = RM_Environment::getInstance()->getTranslation()->_('Admin.Extras.Type', ucfirst($extra->type));
                $extraJson->price = $currencySymbol.$extraSubTotal;

                $reservationDetailsExtra = $summaryModel->getByDetail(
                    $reservationDetail,
                    RM_Module_Extras::SUMMARY_TYPE,
                    $extra->id
                );
                if ($reservationDetailsExtra == null) {
                    $extraJson->saved_price = $currencySymbol.'0';
                    $extraJson->value = 0;
                } else {
                    $extraJson->saved_price = $currencySymbol.$reservationDetailsExtra->total_amount;
                    $extraJson->value = (int)$reservationDetailsExtra->value;
                }
                $extrasJson[] = $extraJson;
            }

            $unit = $unitModel->get($reservationDetail->unit_id);
            if ($unit == null) {
                $unitName = "DELETED";
            } else {
                $unitName = $unit->name;
            }
            $priceSystem = RM_Prices_Manager::getInstance()->getRealPriceSystem($unit);
            
            $reservationDetailJson = new stdClass();
            $reservationDetailJson->id = $reservationDetail->id;
            $reservationDetailJson->extras = $extrasJson;
            $reservationDetailJson->unit_id = $reservationDetail->unit_id;
            $reservationDetailJson->unit_name = $unitName;
            $reservationDetailJson->start = $reservationDetail->getStartDatetime($priceSystem->getDateformat(true));
            $reservationDetailJson->end = $reservationDetail->getEndDatetime($priceSystem->getDateformat(true));
            $reservationDetailJson->subtotal = $reservationDetail->total_price;
            $reservationDetailsJson[] = $reservationDetailJson;
        }

        $json = new stdClass();
        $json->details = $reservationDetailsJson;

        return array(
            'data' => $json
        );        
    }

    public function listJsonAction()
    {
        $iso = $this->_getParam('iso', RM_Environment::getInstance()->getLocale());
        $offset = $this->_getParam('start');
        $count = $this->_getParam('limit');
        $sort = $this->_getParam('sort', 'id');
        if ($sort == 'units') {
            $sort = 'global';
        }        
        $direction = $this->_getParam('dir', 'DESC');
        $filters = $this->_getParam('filter', array());

        $order = $sort . ' ' . $direction;
        $dao = new RM_Extras();

        $total = $dao->filterAll($order, null, null, $filters)->count();
        $rows = $dao->filterAll($order, $count, $offset, $filters);
        
        $data = array();
        foreach ($rows as $row) {
            $dataRow = $row->toArray($iso);
            $data[] = $dataRow;
        }

        $json = new stdClass;
        $json->total = $total;
        $json->data = $data;

        return array(
            'data' => $json
        );
    }

    public function deleteJsonAction()
    {
        $ids = $this->_getParam('ids', array());
        $model = new RM_Extras();
        
        foreach ($ids as $id) {
            $row = $model->find($id)->current();
            if ($row === null) continue;
            $row->delete();
        }

        return array(
            'data' => array('success' => true)
        );
    }   

    /**     
     * @param   request data  price rows information (if 'id'=0 this is a new row)
     * @see modules/extras/js/edit.js
     * @return 	json    json array information: success or failure
     */
    function updateJsonAction()
    {
        $dataJson = $this->_getParam('data', '[]');
        $iso = $this->_getParam('iso', RM_Environment::getInstance()->getLocale());
        $data = Zend_Json::decode($dataJson);

        $unitExtrasModel = new RM_UnitExtras();
        $model = new RM_Extras;
        foreach ($data as $row){
            if (isset($row['name'])) {
                $row[$iso] = $row['name'];
                unset($row['name']);
            }
            $unitIDs = explode(',', $row['units']);
            unset($row['units']);
            if ($row['id'] == 0) {
                unset($row['id']);                
                $extraID = $model->insert($row);
                $dbRow = $model->find($extraID)->current();
            } else {
                $dbRow = $model->find($row['id'])->current();
                if ($dbRow == null) continue;
                foreach ($row as $key => $value) {
                    $dbRow->$key = $value;
                }
                $dbRow->save();
            }
            $unitExtrasModel->insertRows($dbRow, $unitIDs);
        }

        return array('data' => array('success' => true));
    }    
}