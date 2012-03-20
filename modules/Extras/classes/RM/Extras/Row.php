<?php
class RM_Extras_Row extends RM_Row
{
    const SINGLE = 'single';
    const DAY = 'day';
    const PERCENTAGE = 'percentage';    

    const ROUND_UP = 'RoundUp';
    const ROUND_DOWN = 'RoundDown';
    const HOURLY = 'Hourly';

    /**
     * Return name of the extra
     *
     * @param string $iso ISO code
     * @return string
     */
    public function getName($iso = null)
    {
        if ($iso == null) {
            $iso = RM_Environment::getInstance()->getLocale();
        }

        return $this->$iso;
    }
   
    /**
     * Calculate the total price for the extras
     *
     * @param RM_Reservation_Details_Row $details
     * @return float
     */
    public function calculate(RM_Reservation_Details_Row $details)
    {
        return $this->calculateBy(
            $details->getTotalPrice(),
            $details->getTotalSeconds()
        );
    }

    /**
     * Calculate total optional extras value by price and days
     *
     * @param float $totalPrice
     * @param int $totalSeconds
     * @return float
     */
    public function calculateBy($totalPrice, $totalSeconds)
    {
        switch ($this->type) {
            case RM_Extras_Row::DAY :
                switch ($this->rule){
                    case RM_Extras_Row::HOURLY:
                        $totalHours = $totalSeconds / (60 * 60);
                        $hourlyPrice = $this->value / 24;
                        return $hourlyPrice * $totalHours;
                    case RM_Extras_Row::ROUND_UP:
                        $totalDays = ceil($totalSeconds / (60 * 60 * 24));
                        return $totalDays * $this->value;
                    case RM_Extras_Row::ROUND_DOWN:
                        $totalDays = floor($totalSeconds / (60 * 60 * 24));
                        return $totalDays * $this->value;
                }                
            case RM_Extras_Row::PERCENTAGE :
                return $totalPrice * $this->value / 100;
            case RM_Extras_Row::SINGLE :
            default:
                return $this->value;
        }
    }

    /**
     * We need to delete all unit extras as well.
     *
     * @return void
     */
    public function delete()
    {
        $model = new RM_UnitExtras();
        $rows = $model->getByExtra($this);
        foreach ($rows as $row) {
            $row->delete();
        }
        parent::delete();
    }

    /**
     * Returns the column/value data as an array and parse all assigned units
     * in csv format in 'units' field.
     *
     * @return array
     */
    public function toArray($iso = null)
    {
        $dataRow = parent::toArray();
        if ($this->global == 1) {
            $dataRow['units'] = array(0);
        } else {
            $unitExtrasModel = new RM_UnitExtras();
            $unitIDs = array();
            $unitExtras = $unitExtrasModel->getByExtra($this);
            foreach ($unitExtras as $unitExtra){
                $unitIDs[] = $unitExtra->unit_id;
            }
            $dataRow['units'] = implode(',', $unitIDs);
        }
        $dataRow['name'] = $this->getName($iso);
        return $dataRow;
    }

    /**
     * Check if this extra is belong to unit
     *
     * @param RM_Unit_Row $unit
     * @return bool
     */
    public function isBelongTo(RM_Unit_Row $unit)
    {
        if ($this->global) {
            return true;
        }
        $array = $this->toArray();
        $units = explode(',', $array['units']);
        return in_array($unit->id, $units);
    }
}
