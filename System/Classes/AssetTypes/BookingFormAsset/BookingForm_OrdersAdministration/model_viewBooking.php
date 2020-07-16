<?php

	$errors = array();

	$close = false;

	if (!strlen($Booking['tr_charge_total'])) {
	
		if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
			
			// Validate the data for each field
			// Set up the error array
			//ss_DumpVarDie($this);
			if (array_key_exists($this->fieldSet->tablePrimaryKey,$this->ATTRIBUTES)) {
				$this->fieldSet->primaryKey = $this->ATTRIBUTES[$this->fieldSet->tablePrimaryKey];
			}
			
			// Validate each field and record any errors reported
			$errors = array_merge($errors,$this->fieldSet->validate());
			
			// Update if no errors validating data
			if (count($errors) == 0) {
			
				// Construct the SQL
				$insertFields = '';
				foreach ($this->fieldSet->fields as $field) {
					$insertFields .= $field->updateSQL();
				}
				
				// Update the fields
				$result = query("
					UPDATE {$this->fieldSet->tableName}
					SET $insertFields
					WHERE {$this->fieldSet->tablePrimaryKey} = {$this->fieldSet->primaryKey}
				");
		
				// Now handle the special fields.. e.g MultiSelectField
				foreach ($this->fieldSet->fields as $field) {
					$field->specialUpdate();
				}
				
				// Now notify the customer
				locationRelative('index.php?act=BookingForm.SendEmail&bo_id='.$this->ATTRIBUTES['bo_id'].'&as_id='.$this->ATTRIBUTES['as_id']);
				
/*				$close = true;
				
				$Booking = getRow("
					SELECT * FROM booking_form_bookings, transactions
					WHERE bo_id = ".safe($this->ATTRIBUTES['bo_id'])."
						AND bo_tr_id = tr_id
				");*/
			}
	
		}
	}
?>