<?php
	$errors = array();

	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
	
		$this->loadFieldValuesFromForm($this->ATTRIBUTES);
		
		// Validate and then write to the database
		$errors = $this->insert();

		// Return if no error messages were returned
		if (count($errors) == 0) {

			if (array_key_exists('Send',$this->ATTRIBUTES)) {
				locationRelative('index.php?act=News.Send&nei_id='.$this->primaryKey.'&as_id='.$this->assetLink.'&BackURL='.ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']));
			} else {
				// Return (to the list of records hopefully)
				location($this->ATTRIBUTES['BackURL']);
			}
		}
	}
?>