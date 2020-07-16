<?php
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {

		// Validate the data for each field
		// Set up the error array
		//ss_DumpVarDie($this);
		// Validate each field and record any errors reported
		$errors = array_merge($errors,$this->validate());
		$errors = array_merge($errors,$currencySettings->fieldSet->validate());	
						
		// Update if no errors validating data
		if (count($errors) == 0) {		
			$updateFields = 'wpc_id = 1';
			if (array_key_exists('wpc_use_cheque',$this->ATTRIBUTES) and $this->ATTRIBUTES['wpc_use_cheque'] == 1) {
				$updateFields .= ", wpc_use_cheque = 1";
			} else {
				$updateFields .= ", wpc_use_cheque = 0";
			}
			if (array_key_exists('wpc_can_invoice',$this->ATTRIBUTES) and $this->ATTRIBUTES['wpc_can_invoice'] == 1) {
				$updateFields .= ", wpc_can_invoice = 1";
			} else {
				$updateFields .= ", wpc_can_invoice = 0";
			}
			if (array_key_exists('wpc_use_collection',$this->ATTRIBUTES) and $this->ATTRIBUTES['wpc_use_collection'] == 1) {
				$updateFields .= ", wpc_use_collection = 1";
			} else {
				$updateFields .= ", wpc_use_collection = 0";
			}
			
			if (array_key_exists('wpc_direct_payment',$this->ATTRIBUTES) and $this->ATTRIBUTES['wpc_direct_payment'] == 1) {
				$updateFields .= ", wpc_direct_payment = 1";
			} else {
				$updateFields .= ", wpc_direct_payment = 0";
			}
			if (array_key_exists('wpc_use_credit_card',$this->ATTRIBUTES) and $this->ATTRIBUTES['wpc_use_credit_card'] == 1) {
				$updateFields .= ", wpc_use_credit_card = 1";
			} else {
				$updateFields .= ", wpc_use_credit_card = 0";
			}					
			
			$defaultCurrencyDetailsSerialized = serialize($currencySettings->fieldSet->getFieldValuesArray());														
			$updateFields .= ", wpc_default_currency_details = '{$defaultCurrencyDetailsSerialized}'";
						
			// Update the fields
			$result = query("
				UPDATE {$this->tableName}
				SET 				
					$updateFields								
				WHERE wpc_id = 1
			");	
			if (strlen($this->ATTRIBUTES['BackURL']) and !array_key_exists('NoBack', $this->ATTRIBUTES)) {				
					locationRelative('index.php?act=TabbedInterfaceConfiguration');
				
			}				
		}
	}
?>

