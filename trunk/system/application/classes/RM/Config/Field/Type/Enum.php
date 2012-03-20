<?php
class RM_Config_Field_Type_Enum extends RM_Config_Field_Type {
	var $values;
	
	public function validate($inputValue){
		foreach ($this->values as $value) {
			if ($inputValue == $value) {
				return true;
			}
		}
		return false;
	}		
}
?>