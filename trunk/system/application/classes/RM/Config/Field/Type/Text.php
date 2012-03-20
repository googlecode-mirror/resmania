<?php
class RM_Config_Field_Type_Text extends RM_Config_Field_Type {
	var $minLength = 0;
	var $maxLength;
	
	public function validate($value){
		if (strlen($value) > $this->maxLength) return false;
		if (strlen($value) < $this->minLength) return false;
		return true;
	}
}
?>