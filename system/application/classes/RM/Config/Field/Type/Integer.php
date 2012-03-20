<?php
class RM_Config_Field_Type_Integer extends RM_Config_Field_Type {
	var $minValue;
	var $maxValue;	
	
	public function validate($value){
		$value = (int)$value;
		if ($value < $this->minValue) return false;
		if ($value > $this->maxValue) return false;		
		return true;
	}
}
?>