<?php 



	$this->param("tr_id");	
		
	//$this->webPayConfig;
	$errors = array();
	
	// load the transaction record from DB table
	$transaction = getRow("SELECT * FROM transactions, countries WHERE tr_id = {$this->ATTRIBUTES['tr_id']} AND cn_id = tr_currency_link");
	$transactionTemp = getRow("SELECT * FROM transactions WHERE tr_id = {$this->ATTRIBUTES['tr_id']}");
	
	// if the transaction is not existing or the transction form was completed before
	// then client cannot continue
	if (strlen($transaction['tr_id']) || $transaction['tr_completed'] == 1) {
		$this->payment = $transaction;
		//$this->fields = $transactionTemp;
	} else {
		die("You cannot process the payment");
	}
	
	if (strlen($transaction['tr_payment_details_szln'])) {
		//$this->cereal = deserialize($row['as_serialized']);
		$this->cereal = unserialize($transaction['tr_payment_details_szln']);
		if ($this->cereal == NULL) $this->cereal = array();	
	} else {
		$this->cereal = array();	
	}
	$this->primaryKey = $this->ATTRIBUTES['tr_id'];
	
	// Get an object for the correct webpayment processor and define the fields
	
	$className = $transaction['tr_payment_method'];
	$processorType = null;
	
	if (strtolower($className) != 'cheque' and strtolower($className) != 'direct' ) {		
		$className = $transaction['tr_payment_method'];
		requireOnceClass($className);
		$processorType = new $className;
		$processorType->defineFields(&$this);
	}

	// Load the fields with values from the DB or from a previous form submission	
	$this->loadFieldValues($this->ATTRIBUTES,$this->payment);
	if ($processorType != null){
		$processorType->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->cereal,$this->isEdit($this->ATTRIBUTES));
		// proceess payment
		$errors = $processorType->processPayment(&$this);
	}
	
	if (!count($errors)) {
		if ($processorType != null){					
			$paymentDetailsSerialized = serialize($processorType->fieldSet->getFieldValuesArray());			
		}			
		// Update the fields
		$result = query("
			UPDATE {$this->tableName}
			SET 
				tr_payment_details_szln = NULL, 
				tr_processed_date_time = Now(), 			
				tr_status_link = 2
			WHERE {$this->tablePrimaryKey} = {$this->primaryKey}
		");			
	}

	return $errors;
			
?>