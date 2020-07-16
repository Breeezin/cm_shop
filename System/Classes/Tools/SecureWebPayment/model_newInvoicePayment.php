<?php
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {

		// Validate the data for each field
		// Set up the error array
		//ss_DumpVarDie($this);
		/*
		if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) {
			$this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
		}
		
		// Validate each field and record any errors reported
		$errors = array_merge($errors,$this->validate());*/
		//$errors = array_merge($errors,$processorType->fieldSet->validate());		
		
		// Update if no errors validating data
		if (count($errors) == 0) {
			
			
			//$paymentDetailsSerialized = serialize($processorType->fieldSet->getFieldValuesArray());			
									
			// Update the fields
			$result = query("
				UPDATE {$this->tableName}
				SET 					
					tr_completed = 1, 
					tr_timestamp = Now(), 
					tr_charge_total = '{$chargedIn}', 
					tr_payment_method = 'Invoice'		
				WHERE {$this->tablePrimaryKey} = {$this->primaryKey}
			");
			//ss_DumpVarDie($this->ATTRIBUTES, $GLOBALS['cfg']['plaintext_server']);
			//location($GLOBALS['cfg']['plaintext_server'].ss_absolutePathToURL($this->ATTRIBUTES['BackURL']));
			location($this->ATTRIBUTES['BackURL']);
		}
	}
?>	