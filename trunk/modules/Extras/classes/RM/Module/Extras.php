<?php
class RM_Module_Extras extends RM_Module implements RM_Extras_Interface, RM_Unit_Copy_Interface
{
    /**
     * @param RM_Unit_Row $original
     * @param RM_Unit_Row $copy
     * @return void
     */
    function copyInformation(RM_Unit_Row $original, RM_Unit_Row $copy)
    {
        $unitExtrasModel = new RM_UnitExtras();
        $unitExtras = $unitExtrasModel->getByUnit($original);
        foreach ($unitExtras as $unitExtra) {
            $unitExtrasModel->insert(array(
                'unit_id' => $copy->getId(),
                'extra_id' => $unitExtra->extra_id
            ));
        }
    }

    /**
     * Recalculate all for reservation, after some of the unit details has been changed
     *
     * @abstract
     * @param RM_Reservation_Row $reservation
     * @return bool
     */
    function recalculate(RM_Reservation_Row $reservation)
    {
        $summaryModel = new RM_ReservationSummary();
        $extrasModel = new RM_Extras();
        $details = $reservation->getDetails();
        foreach ($details as $detail) {
            $summaryRows = $summaryModel->fetchByReservationDetail($detail);
            foreach ($summaryRows as $summaryRow) {
                if ($summaryRow->type == self::SUMMARY_TYPE) {
                    $extra = $extrasModel->find($summaryRow->row_id)->current();
                    if ($extra == null) {
                        //IF this is a deprecated - total value remains the same
                        continue;
                    }
                    $detailsObj = $detail->transform();
                    if ($detailsObj instanceof RM_Reservation_Details){
                        $summaryRow->total_amount = -$extra->calculate($detailsObj) * $summaryRow->value;
                        $summaryRow->save();
                    } else {
                        RM_Log::toLog("Extras Encountered Issues: ".print_r($detailsObj, true));
                    }
                }
            }
        }
    }

    /**
     * Returns extra HTML for a summary reservation page to present current system
     *
     * @abstract
     * @param RM_Reservation_Details $detail
     * @return string HTML code to paste
     */
    public function getSummary(RM_Reservation_Details $detail)
    {
        $model = new RM_Extras();
        $extras = $model->getByUnit($detail->getUnit());
        if (0 == $extras->count()) {
            return "";
        }

        // tax
        $taxSystem = RM_Environment::getInstance()->getTaxSystem();
        $taxes = $taxSystem->getAllTaxes($detail->getUnit());

        $request = RM_Environment::getConnector()->getRequestHTTP();                
        $request->setControllerName('Extras');
        $request->setActionName('summary');
        $controller = new RM_User_ExtrasController(
            $request,
            new Zend_Controller_Response_Http()
        );
        $controller->setFrontController(Zend_Controller_Front::getInstance());        
        $controller->view->unit = $detail->getUnit();

        $config = new RM_Config();
        $controller->view->currencySymbol = $config->getValue('rm_config_currency_symbol');

        $translate = RM_Environment::getInstance()->getTranslation();
        $extraTypes = array(
            'day' =>  $translate->_('User.Extras.Type', 'Day'),
            'percentage' => $translate->_('User.Extras.Type', 'Percentage'),
            'single' => $translate->_('User.Extras.Type', 'Single')
        );

        $viewExtras = array();
        foreach ($extras as $extra) {

            $extraTotal = RM_Environment::getInstance()->roundPrice(
                $extra->calculateBy(
                    $detail->getTotal(),
                    $detail->getPeriod()->getSeconds()
                )
            );

            // calculate the tax due on the extra...
            $taxTotal = 0;
            foreach ($taxes as $tax) {
                $taxTotal = $taxTotal + $tax->calculate($extraTotal, $detail);
            }
//            $extraTotal = $extraTotal + $taxTotal;

            $viewExtras[] = array(
                'id' => $extra->id,
                'name' => $extra->getName(),
                'type' => $extraTypes[$extra->type],
                'value' => $extraTotal,
                'tax' => $taxTotal,
                'max' => $extra->max,
                'min' => $extra->min
            );
        }        
        $controller->view->extras = $viewExtras;
        return $controller->view->render('Extras/summary.phtml');
    }

    /**
     * This string is for rm_reservation_summary table
     */
    const SUMMARY_TYPE = 'extras';
    const SUMMARY_TYPE_TAX = 'extras_tax';


    public function  __construct()
    {
        $this->name = 'Extras';        
    }

    public function deleteLanguage($locale)
    {
        parent::deleteLanguage($locale);
        $model = new RM_Extras;
        $model->deleteLanguage($locale);
    }

    public function addLanguage($locale)
    {
        parent::addLanguage($locale);
        $model = new RM_Extras;
        $model->addLanguage($locale);
    }

    /**
     * This method will return node object for main admin menu tree.
     * Every child classes could overload this method to return any of the node object.
     * If there is no need to present a module in the main admin tree overloaded method should return NULL
     *
     * @return stdClass | null
     */
    public function getNode()
    {
        $std = new stdClass;

        $std->id = $this->name.'_EditJson_NoAjax';
        $std->text = $this->getName();

        $std->iconCls = 'RM_modules_leaf_icon';
        $std->leaf = 'true';
        return $std;
    }

    public function install()
    {
        parent::install();

        //Add iso columns into rm_extras table for each already installed language
        $languageModule = new RM_Languages();
        $languages = $languageModule->fetchAll();
        require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Extras.php');
        $extrasModule = new RM_Extras();
        foreach ($languages as $language){
            $extrasModule->addLanguage($language->iso);
        }
    }

    public function getConfigNode()
    {
        return null;
    }

    /**
     * Validate extras selection for the user GUI
     *
     * @param array $data in format [extras system name][unit id][extra id] = user selected value for the extra
     * @return bool
     */
    public function applySelection(Zend_Controller_Request_Abstract $request, RM_Reservation_Details $detail)
    {
        $data = $request->getParam('rm_extras', array());
        if (is_array($data) == false) {
            return false;
        }
        if (count($data) == 0) {
            return $detail;
        }

        if (isset($data[$detail->getUnit()->id]) == false) {
            return $detail;
        }

        if (is_array($data[$detail->getUnit()->id]) == false) {
            return false;
        }

        if (count($data[$detail->getUnit()->id]) == 0) {
            return $detail;
        }

        // tax
        $taxSystem = RM_Environment::getInstance()->getTaxSystem();
        $taxes = $taxSystem->getAllTaxes($detail->getUnit());

        $model = new RM_Extras();
        $extras = array();
        foreach ($data[$detail->getUnit()->id] as $extraID => $value) {
            $extra = $model->find($extraID)->current();
            if ($extra == null) {
                continue;
            }
            if ($extra->isBelongTo($detail->getUnit()) == false){
                continue;
            }
            if (($value > $extra->max || $value < $extra->min) && $value != 0){
                continue;
            }

            // calculate the tax due on the extra...
            $extraTotal = $extra->calculateBy($detail->getTotal(), $detail->getPeriod()->getSeconds());

            $taxTotal = 0;
            foreach ($taxes as $tax) {
                $taxTotal = $taxTotal + $tax->calculate($extraTotal, $detail);
            }

            $extraObject = new RM_Extras_Object(
                $extra,
                $value * $extraTotal,
                $value,
                $value * $taxTotal
            );
            $extras[] = $extraObject;
        }

        $detail->addExtras($extras);
        return $detail;
    }

    /**
     * Assign extras to reservation
     *
     * @param RM_Reservation_Details $detail
     * @param RM_Reservation_Details_Row $detailRow
     * @return void
     */
    public function assign(RM_Reservation_Details $detail, RM_Reservation_Details_Row $detailRow)
    {
        $extras = $detail->getExtras();
        foreach ($extras as $extra) {
            if ($extra instanceof RM_Extras_Object) {                                     
                $summaryModel = new RM_ReservationSummary();
                $summaryModel->insert(array(
                    'row_id' => $extra->getID(),
                    'type' => self::SUMMARY_TYPE,
                    'reservation_detail_id' => $detailRow->id,
                    'value' => $extra->getValue(),
                    'total_amount' => $extra->getPrice(),
                    'name' => $extra->getName()
                ));
                
                // insert the extra tax
                $summaryModel->insert(array(
                    'row_id' => $extra->getID(),
                    'type' => self::SUMMARY_TYPE_TAX,
                    'reservation_detail_id' => $detailRow->id,
                    'value' => $extra->getValue(),
                    'total_amount' => $extra->getTax(),
                    'name' => "Extras Tax" //TODO: need to change this to language translation
                ));


            }
        }
    }    
}