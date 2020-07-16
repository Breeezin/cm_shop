<?php
	$this->param("wpc_id", 1);
	$errors = array();
	$this->configuration = getRow("SELECT * FROM web_pay_configuration");
		
	$this->primaryKey = $this->ATTRIBUTES['wpc_id'];

	//ss_DumpVarDie($this->configuration);
	if (strlen($this->configuration['wpc_card_details'])) {
		//$this->cereal = deserialize($row['as_serialized']);
		$this->creditCardSettings = unserialize($this->configuration['wpc_card_details']);
		if ($this->creditCardSettings == NULL) $this->creditCardSettings = array();	
	} else {
		$this->creditCardSettings = array();	
	}
	
	$this->display->title = "<a href='index.php?act=web_pay_configuration.Edit'>Web Payment configuration</A> : Credit Card Payment";
	
	requireOnceClass('CreditCardSettings');
	$creditCardSettings = new CreditCardSettings;
	$creditCardSettings->defineFields(&$this);	
	$creditCardSettings->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->creditCardSettings,$this->isEdit($this->ATTRIBUTES));
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {

		
		// Validate each field and record any errors reported		
		$errors = array_merge($errors,$creditCardSettings->fieldSet->validate());	
						
		// Update if no errors validating data
		if (count($errors) == 0) {		
			
			foreach ($creditCardSettings->fieldSet->fields as $field) {
				$creditCardSettings->fieldSet->fields[$field->name]->specialInsert();			
			}
			$creditCardDetailsSerialized = serialize($creditCardSettings->fieldSet->getFieldValuesArray());														
			$updateFields = "  wpc_card_details  = '{$creditCardDetailsSerialized}'";
						
			// Update the fields
			$result = query("
				UPDATE {$this->tableName}
				SET 				
					$updateFields								
				WHERE wpc_id = 1
			");	

			//location("index.php?act=web_pay_configuration.Edit");
		}
	}
?>