<?php
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {

		// Validate the data for each field
		// Set up the error array
		//$ok = true;
		
		
		if(!array_key_exists("Paid", $this->ATTRIBUTES)) {				
					
			if (array_key_exists($this->tablePrimaryKey,$this->ATTRIBUTES)) {
				$this->primaryKey = $this->ATTRIBUTES[$this->tablePrimaryKey];
			}
			
			// Validate each field and record any errors reported
			$errors = array_merge($errors,$this->validate());
			$errors = array_merge($errors,$processorType->fieldSet->validate());
			//ss_DumpVar($processorType->fieldSet->fields,'att',true);			
			//ss_DumpVar($this->ATTRIBUTES,'att',true);			
		}
		
		
		// Update if no errors validating data
		if (count($errors) == 0) {
			$display = false;
			$transactionDone = $processorType->checkTransactionDone($this);
			// transcation result need to store all not only time
			$previousResult = $this->payment['tr_result'];
			$previousResult .= strlen($previousResult)?'<BR>':'';
			$previousInfo = $this->payment['tr_sent_information'];
			$previousInfo .= strlen($previousInfo)?'<BR>':'';
			
			$transactionResults = escape($previousResult.$processorType->storeTransactionResult($this));
			$transactionInfo = escape($previousInfo.$processorType->getTransactionSentInfo($this));
			$paymentDetailsSerialized = '';
								
			if(!array_key_exists("Paid", $this->ATTRIBUTES)) {		
				if ($creditConfig['Processor'] == 'WebPay_CreditCard_Manual' or ($transactionDone != 2 and ss_optionExists('Transaction Fail Continue'))) {
					$paymentDetailsSerialized = serialize($processorType->fieldSet->getFieldValuesArray());			
				} 
				
			}
			
			// Update the fields
			$result = query("
				UPDATE {$this->tableName}
				SET 
					tr_payment_details_szln = '".escape($paymentDetailsSerialized)."', 
					tr_completed = 1, 
					tr_status_link = $transactionDone,
					tr_timestamp = Now(), 
					tr_charge_total = '{$chargedIn}', 
					tr_nzd_total_charged = $nzdChargedIn,
					tr_result = '$transactionResults',
					tr_sent_information = '$transactionInfo',
					tr_payment_method = '{$creditConfig['Processor']}'		
				WHERE {$this->tablePrimaryKey} = {$this->primaryKey}
			");
		
		
			//location($this->ATTRIBUTES['BackURL'], true);	
			//ss_DumpVarDie($this);
		}
		
	}
?>	