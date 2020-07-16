<?php
	$this->param('Edit',0);
	
	$display = true;
	$isFromOutSide = null;
	if(array_key_exists("Paid", $_REQUEST)) {
		if (array_key_exists('MerchantOrderNo', $_REQUEST)) {
			$this->param("tr_id", $_REQUEST['MerchantOrderNo']);
			$sesionInfo = ListToArray($_REQUEST['SessionInfo'],"US");
			$isFromOutSide = 'paypro';
		} else if (array_key_exists('vpc_MerchTxnRef', $_REQUEST)) {
			$isFromOutSide = 'egate';
			$this->param("tr_id", $_GET['vpc_MerchTxnRef']);
			$sesionInfo = ListToArray($_GET['vpc_OrderInfo'],"US");
		} else if ( array_key_exists('ms', $_REQUEST ) ){			
            $this->param("tr_id", $_REQUEST['ms']);
            $sesionInfo = ListToArray($_REQUEST['merchant_ref'],"US");
            $isFromOutSide = 'paystation';
        }
				
		//http://phpcm.im.co.nz/Shop/Service/Completed/tr_id/392/tr_token/0219c7f067980f99e6cea5f4d272290b/us_id/4
		

 echo '<!-- ';
if (ss_isItUs()) {
    ss_DumpVar($_REQUEST);
    ss_DumpVar($this);
    ss_DumpVar($sesionInfo);
}
echo ' -->';

		$temp = getRow("SELECT tr_token FROM transactions WHERE tr_id = {$this->ATTRIBUTES['tr_id']}");
		$this->param("tr_token", $temp['tr_token']);
				
		$result = new Request("Asset.PathFromID", array('as_id' => $sesionInfo[0]));
		$assetPath = ss_withoutPreceedingSlash($result->value);
		
		$this->param("BackURL", $GLOBALS['cfg']['plaintext_server']."$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$temp['tr_token']}/us_id/{$sesionInfo[1]}");
		//ss_DumpVarDie($this->ATTRIBUTES);
	} else {
		$this->param("tr_id");
		$this->param("tr_token");	
		$this->param("BackURL");
		$this->param("Type", '');
		$this->param("SessionInfo", '');
		
	}
	
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
	
	$nzdChargedIn = $this->getPaymentNZDTotal();	
	$this->payment['tr_nzd_total_charged'] = $nzdChargedIn;	
	$chargedIn = $this->getChargePriceFromDefaultSettings($this->payment['tr_total'], $this->payment['cn_currency_code']);		
	$this->payment['tr_charge_total'] = $chargedIn;
	
	if (strlen($transaction['tr_payment_details_szln'])) {
		//$this->cereal = deserialize($row['as_serialized']);
		$this->cereal = unserialize($transaction['tr_payment_details_szln']);
		if ($this->cereal == NULL) $this->cereal = array();	
	} else {
		$this->cereal = array();	
	}
	$this->primaryKey = $this->ATTRIBUTES['tr_id'];
	
	
		
	// Get an object for the correct webpayment processor and define the fields
	$creditConfig = unserialize($this->webPayConfig['wpc_card_details']);
	
	$className = $creditConfig['Processor'];
	requireOnceClass($className);
	$processorType = new $className;
	$processorType->defineFields($this);

	// Load the fields with values from the DB or from a previous form submission	
	$this->loadFieldValues($this->ATTRIBUTES,$this->payment);
	//ss_DumpVar($this,'this',true);
	$processorType->fieldSet->loadFieldValues($this->ATTRIBUTES,$this->cereal,$this->isEdit($this->ATTRIBUTES));
	
	
	$this->display->layout = "nolink";
		
?>
