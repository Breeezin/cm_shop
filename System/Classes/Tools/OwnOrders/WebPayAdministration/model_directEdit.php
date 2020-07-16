<?php
	$this->param("wpc_id", 1);
	$errors = array();
	$this->configuration = getRow("SELECT * FROM web_pay_configuration");
		
	$this->primaryKey = $this->ATTRIBUTES['wpc_id'];

	//ss_DumpVarDie($this->configuration);
	if (strlen($this->configuration['wpc_direct_payment_details'])) {
		//$this->cereal = deserialize($row['as_serialized']);
		$this->directSettings = unserialize($this->configuration['wpc_direct_payment_details']);
		if ($this->directSettings == NULL) $this->directSettings = array();	
	} else {
		$this->directSettings = array();	
	}
	
	$this->display->title = "<a href='index.php?act=web_pay_configuration.Edit'>Web Payment configuration</A> : Direct Payment";
	
	requireOnceClass('DirectPaymentSettings');
	$directSettings = new DirectPaymentSettings;
	$directSettings->defineFields(&$this);	
	$directSettings->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->directSettings,$this->isEdit($this->ATTRIBUTES));
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {

		
		// Validate each field and record any errors reported		
		$errors = array_merge($errors,$directSettings->fieldSet->validate());	
						
		// Update if no errors validating data
		if (count($errors) == 0) {		

			$chequeDetailsSerialized = escape(serialize($directSettings->fieldSet->getFieldValuesArray()));														
			$updateFields = "  wpc_direct_payment_details  = '{$chequeDetailsSerialized}'";
						
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