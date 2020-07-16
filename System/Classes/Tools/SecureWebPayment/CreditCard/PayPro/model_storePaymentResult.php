<?php 
	
	
	/*
		 MerchantOrderNo: Your original Merchant Order ID 
	• AcquirerBankReceiptCode: Acquirer Bank Receipt Code or RRN 
	• Result - (Result) - can be "OK" , "Fail" or "Error"
	The results also contain the following fields if you need it:
	• AcquirerBankResponseCode: Acquirer Bank Response Code 
	• TransactionNo: Bank Refund Reference ID 
	• Amount: The original amount tendered (not same as the amount captured)

	*/
	
		
	ss_paramKey($webpay->ATTRIBUTES, 'AcquirerBankReceiptCode','');
	ss_paramKey($webpay->ATTRIBUTES, 'Result','');
	ss_paramKey($webpay->ATTRIBUTES, 'AcquirerBankResponseCode','');
	ss_paramKey($webpay->ATTRIBUTES, 'TransactionNo','');
	ss_paramKey($webpay->ATTRIBUTES, 'Amount','');
	
	$results = array();
	array_push($results, array('name' => 'Acquirer Bank Receipt Code or RRN', 
								'value' => $webpay->ATTRIBUTES['AcquirerBankReceiptCode']));
	array_push($results, array('name' => 'Result', 
								'value' => $webpay->ATTRIBUTES['Result']));
	array_push($results, array('name' => 'Acquirer Bank Response Code', 
								'value' => $webpay->ATTRIBUTES['AcquirerBankResponseCode']));
	array_push($results, array('name' => 'Bank Refund Reference ID', 
								'value' => $webpay->ATTRIBUTES['TransactionNo']));
	array_push($results, array('name' => 'The original amount tendered', 
								'value' => $webpay->ATTRIBUTES['Amount']));
	
	return serialize($results);
?>