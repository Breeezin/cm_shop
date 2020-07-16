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
	
		
	ss_paramKey($webpay->ATTRIBUTES, 'ti','');
	ss_paramKey($webpay->ATTRIBUTES, 'ec','');
	ss_paramKey($webpay->ATTRIBUTES, 'em','');
	ss_paramKey($webpay->ATTRIBUTES, 'am','');
	ss_paramKey($webpay->ATTRIBUTES, 'ms','');
	ss_paramKey($webpay->ATTRIBUTES, 'merchant_ref','');

	$results = array();
	array_push($results, array('name' => 'Paystation Transaction ID',
								'value' => $webpay->ATTRIBUTES['ti']));
	array_push($results, array('name' => 'Error Code',
								'value' => $webpay->ATTRIBUTES['ec']));
	array_push($results, array('name' => 'Error Message',
								'value' => $webpay->ATTRIBUTES['em']));
	array_push($results, array('name' => 'Amount processed by bank',
								'value' => $webpay->ATTRIBUTES['am']));
	array_push($results, array('name' => 'Our Transaction ID',
								'value' => $webpay->ATTRIBUTES['ms']));
	array_push($results, array('name' => 'Asset and User ID',
								'value' => $webpay->ATTRIBUTES['merchant_ref']));

	return serialize($results);
?>

