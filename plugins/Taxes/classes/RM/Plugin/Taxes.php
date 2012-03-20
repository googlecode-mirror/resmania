<?php
/**
 * Taxes plugin class
 *
 * Main class for all taxes manipulations
 * @access 	public
 * @author 	Valentin
 * @copyright	ResMania 2009 all rights reserved.
 * @version	1.0
 * @link	http://developer.resmania.com/api
 * @since  	08-2009
 */
class RM_Plugin_Taxes extends RM_Plugin implements RM_Taxes_Interface, RM_Unit_Copy_Interface {
    /**
     * This string is for rm_reservation_summary table
     */
    const SUMMARY_TYPE = 'tax';

    function copyInformation(RM_Unit_Row $original, RM_Unit_Row $copy)
    {
        $unitTaxesModel = new RM_UnitTaxes();
        $unitTaxes = $unitTaxesModel->getByUnit($original);
        foreach ($unitTaxes as $unitTax) {
            $copyUnitTaxData = $unitTax->toArray();
            unset($copyUnitTaxData['id']);
            $copyUnitTaxData['unit_id'] = $copy->id;
            $unitTaxesModel->insert($copyUnitTaxData);
        }
    }

    /**
     * Recalculate all for reservation, after some of the unit details has been changed
     *
     * @param RM_Reservation_Row $reservation
     * @return bool
     */
    function recalculate(RM_Reservation_Row $reservation)
    {
        $summaryModel = new RM_ReservationSummary();
        $details = $reservation->getDetails();
        foreach ($details as $detail) {
            //TODO: we need to think - maybe we should leave taxes as is while recalculation process if
            //there was a summary row for unit detail.
            $summaryRows = $summaryModel->fetchByReservationDetail($detail);
            foreach ($summaryRows as $row) {
                if ($row->type == self::SUMMARY_TYPE) {
                    $row->delete();
                }
            }
            $reservationDetail = $detail->transform();
            $this->assign($reservationDetail, $detail);
        }
    }

    /**
     * Assign all taxes to reservation
     * 
     * @param RM_Reservation_Row $reservation
     * @param RM_Reservation_Details_Row $detailRow
     * @return bool
     */
    function assign(RM_Reservation_Details $detail, RM_Reservation_Details_Row $detailRow)
    {
        $detailsPrice = $detail->getTotal();

        $summaryModel = new RM_ReservationSummary();
        $summaryRows = $summaryModel->fetchByReservationDetail($detailRow);
        foreach ($summaryRows as $summaryRow) {
            if ($summaryRow->type !== self::SUMMARY_TYPE) {
                $detailsPrice += $summaryRow->total_amount;
            }
        }

        $model = new RM_Taxes;
        $taxes = $model->getByUnit($detail->getUnit());
        foreach ($taxes as $tax) {
            $summaryModel->insert(array(
                'row_id' => $tax->id,
                'type' => self::SUMMARY_TYPE,
                'reservation_id' => null,
                'reservation_detail_id' => $detailRow->id,
                'total_amount' => $tax->calculate($detailsPrice, $detail),
                'name' => $tax->getName(RM_Environment::getInstance()->getLocale())
            ));
        }
    }

    /**
     * Return total value of saved taxes for reservation
     *
     * @param RM_Reservation_Row $reservation
     * @return float
     */
    function getTotalTaxes(RM_Reservation_Row $reservation)
    {
        $total = 0;
        $details = $reservation->getDetails();

        $summaryModel = new RM_ReservationSummary();
        foreach ($details as $detail) {
            $summaryRows = $summaryModel->fetchByReservationDetail($detail);
            foreach ($summaryRows as $summaryRow) {
                if ($summaryRow->type == self::SUMMARY_TYPE) {
                    $total += $summaryRow->total_amount;
                }
            }
        }
        return $total;
    }

    /**
     * Calculate total amount of all taxes for the unit.
     *     
     * @param float $price total price for the unit before taxes
     * @return float total amount of all taxes that applied to current unit
     */
    function calculateTotalTax(RM_Unit_Row $unit, $price)
    {
        $taxes = $this->getAllTaxes($unit);
        $total = 0;
        foreach ($taxes as $tax) {
            $total = $tax->calculate($price,$unit);
        }
        return RM_Environment::getInstance()->roundPrice($total);
    }

    /**
     * This method will return node object for main admin menu tree.
     * Every child classes could overload this method to return any of the node object.
     * If there is no need to present a plugin in the main admin tree overloaded method should return NULL
     *
     * @return stdClass | null
     */
    public function getNode()
    {
        $std = new stdClass;
        $std->id = $this->name.'_EditJson_NoAjax';
        $std->text = $this->getName();
        $std->leaf = 'true';
        $std->iconCls = 'RM_plugins_leaf_icon';
        return $std;
    }

    function getConfigNode()
    {
        return null;
    }

    /**
     * Returns all enabled taxes
     *
     * @return Zend_Db_Table_Rowset
     */
    function getAllTaxes(RM_Unit_Row $unit)    
    {
        $model = new RM_Taxes;
        return $model->getByUnit($unit);
    }

    /**
     * Public constructor
     */
    public function  __construct()
    {
        $this->name = 'Taxes';
    }

    /**
     * This method will invoke while adding new language to the Resmania
     *
     * @param string $iso ISO language code
     */
    public function addLanguage($iso)
    {
        parent::addLanguage($iso);
        $model = new RM_Taxes();
        $model->addLanguage($iso);
    }

    /**
     * Invokes after user delete language, make some changes for price module
     *
     * @param string $iso ISO language code
     * @return null
     */
    public function deleteLanguage($iso)
    {
        parent::deleteLanguage($iso);
        $model = new RM_Taxes();
        $model->deleteLanguage($iso);
    }
}
