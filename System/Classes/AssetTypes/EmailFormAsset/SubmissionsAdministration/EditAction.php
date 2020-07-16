<?php
	$errors = array();
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
	
		// We're writing to the database, so must load each field
		// with the value receieved from the form

		$this->loadFieldValuesFromForm($this->ATTRIBUTES);
		
		// Write to the database
		$errors = $this->update();

		// Return if no error messages were returned
		if (count($errors) == 0) {
			// Return (to the list of records hopefully)
			//rfaReturn();
			//location($this->ATTRIBUTES['RFA']);
			location($this->ATTRIBUTES['BackURL']);
		}
	}
?>