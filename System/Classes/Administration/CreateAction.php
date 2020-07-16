<?php
	$errors = array();
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {		
		$this->loadFieldValuesFromForm($this->ATTRIBUTES);
				
		// Validate and then write to the database
		$errors = $this->insert();
		//ss_DumpVarDie($errors);
		// Return if no error messages were returned
		if (count($errors) == 0) {
			// Return (to the list of records hopefully)
			//rfaReturn();
			////////location($this->ATTRIBUTES['RFA']);
			location($this->ATTRIBUTES['BackURL']);
		}
	}
?>