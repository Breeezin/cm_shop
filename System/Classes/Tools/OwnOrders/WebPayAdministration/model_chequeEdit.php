<?php
	$this->param("wpc_id", 1);
	$errors = array();
	$this->configuration = getRow("SELECT * FROM web_pay_configuration");
		
	$this->primaryKey = $this->ATTRIBUTES['wpc_id'];

	//ss_DumpVarDie($this->configuration);
	if (strlen($this->configuration['wpc_cheque_details'])) {
		//$this->cereal = deserialize($row['as_serialized']);
		$this->chequeSettings = unserialize($this->configuration['wpc_cheque_details']);
		if ($this->chequeSettings == NULL) $this->chequeSettings = array();	
	} else {
		$this->chequeSettings = array();	
	}
	
	$this->display->title = "<a href='index.php?act=web_pay_configuration.Edit'>Web Payment configuration</A> : Cheque Payment";
	
	requireOnceClass('ChequeSettings');
	$chequeSettings = new ChequeSettings;
	$chequeSettings->defineFields(&$this);	
	$chequeSettings->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->chequeSettings,$this->isEdit($this->ATTRIBUTES));
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {

		
		// Validate each field and record any errors reported		
		$errors = array_merge($errors,$chequeSettings->fieldSet->validate());	
						
		// Update if no errors validating data
		if (count($errors) == 0) {		

			$chequeDetailsSerialized = serialize($chequeSettings->fieldSet->getFieldValuesArray());														
			$updateFields = "  wpc_cheque_details  = '{$chequeDetailsSerialized}'";
						
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