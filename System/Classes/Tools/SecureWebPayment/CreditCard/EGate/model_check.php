<?php 
	$txnResponseCode = ss_null2unknown($webpay->ATTRIBUTES["vpc_TxnResponseCode"]);
	
	if ($txnResponseCode == '0') 
		return 2;
	else
		return 3;
		
		
	function ss_null2unknown($data) {
	    if ($data == "") {
	        return "No Value Returned";
	    } else {
	        return $data;
	    }
	} 
?>