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
			
			if (array_key_exists('Send',$this->ATTRIBUTES)) {
				locationRelative('index.php?act=Newsletter.BeforeSend&nl_id='.$this->primaryKey);
			} else if (array_key_exists('SendPreview',$this->ATTRIBUTES)) {
				locationRelative('index.php?act=Newsletter.SendPreview&BreadCrumbs='.ss_URLEncodedFormat($this->ATTRIBUTES['BreadCrumbs']).'&BackURL='.ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']).'&DisableOutputBuffering=1&nl_id='.$this->primaryKey);
			} else if (array_key_exists('Preview',$this->ATTRIBUTES)) {
				locationRelative('index.php?act=NewslettersAdministration.Edit&Preview=1&BreadCrumbs='.ss_URLEncodedFormat($this->ATTRIBUTES['BreadCrumbs']).'&BackURL='.ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']).'&nl_id='.$this->primaryKey);
			} else {
				// Return (to the list of records hopefully)
				location($this->ATTRIBUTES['BackURL']);
			}
		}
	}
?>