<?php 
	$this->param("tr_id");
	$this->param("tr_token");	
	$this->param("BackURL");
	$this->param("Type", '');
	//$this->webPayConfig;
	$errors = array();
	
	// load the transaction record from DB table
	$transaction = getRow("SELECT * FROM transactions, countries WHERE tr_id = {$this->ATTRIBUTES['tr_id']} AND tr_token LIKE '{$this->ATTRIBUTES['tr_token']}' AND cn_id = tr_currency_link");
	$transactionTemp = getRow("SELECT * FROM transactions WHERE tr_id = {$this->ATTRIBUTES['tr_id']} AND tr_token LIKE '{$this->ATTRIBUTES['tr_token']}'");
	
	// if the transaction is not existing or the transction form was completed before
	// then client cannot continue
	if (strlen($transaction['tr_id']) || $transaction['tr_completed'] != 1) {
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
	
	/*
	$chequeConfig = unserialize($tempWebPayConfig['wpc_cheque_details']);
		
	ss_DumpVar($tempWebPayConfig);
	ss_DumpVar($creditConfig);
	ss_DumpVarDie($chequeConfig);
		*/
	// Get an object for the correct webpayment processor and define the fields
	$chequeConfig = unserialize($this->webPayConfig['wpc_cheque_details']);
	
	// Load the fields with values from the DB or from a previous form submission	
	$this->loadFieldValues($this->ATTRIBUTES,$this->payment);	
	$chargedIn = $this->getChargePriceFromDefaultSettings($this->payment['tr_total'], $this->payment['cn_currency_code']);	
	$this->display->layout = "nolink";
?>