<?php
	$this->param("wpc_id", 1);
	$errors = array();
	$this->configuration = getRow("SELECT * FROM web_pay_configuration");
		
	$this->primaryKey = $this->ATTRIBUTES['wpc_id'];

	//ss_DumpVarDie($this->configuration);
	if (strlen($this->configuration['wpc_collection_details'])) {
		//$this->cereal = deserialize($row['as_serialized']);
		$this->collectionSettings = unserialize($this->configuration['wpc_collection_details']);
		if ($this->collectionSettings == NULL) $this->collectionSettings = array();	
	} else {
		$this->collectionSettings = array();	
	}
	
	$this->display->title = "<a href='index.php?act=web_pay_configuration.Edit'>Web Payment configuration</A> : Pay on Collection Payment";
	
	requireOnceClass('CollectionPaymentSettings');
	$Settings = new CollectionPaymentSettings;
	$Settings->defineFields(&$this);	
	$Settings->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->collectionSettings,$this->isEdit($this->ATTRIBUTES));
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {

		
		// Validate each field and record any errors reported		
		$errors = array_merge($errors,$Settings->fieldSet->validate());	
						
		// Update if no errors validating data
		if (count($errors) == 0) {		

			$DetailsSerialized = serialize($Settings->fieldSet->getFieldValuesArray());														
			$updateFields = "  wpc_collection_details  = '{$DetailsSerialized}'";
						
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