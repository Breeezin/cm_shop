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

	
	
	
	$amount          = null2unknown($webpay->ATTRIBUTES["vpc_Amount"]);
	$locale          = null2unknown($webpay->ATTRIBUTES["vpc_Locale"]);
	$batchNo         = null2unknown($webpay->ATTRIBUTES["vpc_BatchNo"]);
	$command         = null2unknown($webpay->ATTRIBUTES["vpc_Command"]);
	$message         = null2unknown($webpay->ATTRIBUTES["vpc_Message"]);
	$version         = null2unknown($webpay->ATTRIBUTES["vpc_Version"]);
	$cardType        = null2unknown($webpay->ATTRIBUTES["vpc_Card"]);
	$orderInfo       = null2unknown($webpay->ATTRIBUTES["vpc_OrderInfo"]);
	$receiptNo       = null2unknown($webpay->ATTRIBUTES["vpc_ReceiptNo"]);
	$merchantID      = null2unknown($webpay->ATTRIBUTES["vpc_Merchant"]);	
	$authorizeID     = null2unknown(array_key_exists("vpc_AuthorizeId", $webpay->ATTRIBUTES)? $webpay->ATTRIBUTES["vpc_AuthorizeId"]: "");
	$merchTxnRef     = null2unknown($webpay->ATTRIBUTES["vpc_MerchTxnRef"]);
	$transactionNo   = null2unknown($webpay->ATTRIBUTES["vpc_TransactionNo"]);
	$acqResponseCode = null2unknown($webpay->ATTRIBUTES["vpc_AcqResponseCode"]);
	$txnResponseCode = null2unknown($webpay->ATTRIBUTES["vpc_TxnResponseCode"]);
	// 3-D Secure Data
	$verType         = array_key_exists("vpc_VerType", $webpay->ATTRIBUTES)          ? $webpay->ATTRIBUTES["vpc_VerType"]          : "No Value Returned";
	$verStatus       = array_key_exists("vpc_VerStatus", $webpay->ATTRIBUTES)        ? $webpay->ATTRIBUTES["vpc_VerStatus"]        : "No Value Returned";
	$token           = array_key_exists("vpc_VerToken", $webpay->ATTRIBUTES)         ? $webpay->ATTRIBUTES["vpc_VerToken"]         : "No Value Returned";
	$verSecurLevel   = array_key_exists("vpc_VerSecurityLevel", $webpay->ATTRIBUTES) ? $webpay->ATTRIBUTES["vpc_VerSecurityLevel"] : "No Value Returned";
	$enrolled        = array_key_exists("vpc_3DSenrolled", $webpay->ATTRIBUTES)      ? $webpay->ATTRIBUTES["vpc_3DSenrolled"]      : "No Value Returned";
	$xid             = array_key_exists("vpc_3DSXID", $webpay->ATTRIBUTES)           ? $webpay->ATTRIBUTES["vpc_3DSXID"]           : "No Value Returned";
	$acqECI          = array_key_exists("vpc_3DSECI", $webpay->ATTRIBUTES)           ? $webpay->ATTRIBUTES["vpc_3DSECI"]           : "No Value Returned";
	$authStatus      = array_key_exists("vpc_3DSstatus", $webpay->ATTRIBUTES)        ? $webpay->ATTRIBUTES["vpc_3DSstatus"]        : "No Value Returned";

	
	$results = array();
	array_push($results, array('name' => 'VPC API Version',
						'value' => $version));
	array_push($results, array('name' => 'Command', 
								'value' => $command));
	array_push($results, array('name' => 'Merchant Transaction Reference', 
								'value' => $merchTxnRef));
	array_push($results, array('name' => 'Merchant ID', 
								'value' => $merchantID));	
	array_push($results, array('name' => 'Order Information', 
								'value' => $orderInfo));
	array_push($results, array('name' => 'Purchase Amount', 
								'value' => $amount));
	array_push($results, array('name' => 'VPC Transaction Response Code', 
								'value' => $txnResponseCode));
	array_push($results, array('name' => 'Transaction Response Code Description', 
								'value' => getResponseDescription($txnResponseCode)));		
	array_push($results, array('name' => 'Message', 
								'value' => $message));
	// only display the following fields if not an error condition								
	if ($txnResponseCode != "7" && $txnResponseCode != "No Value Returned") { 								
		array_push($results, array('name' => 'Receipt Number', 
									'value' => $receiptNo));															
		array_push($results, array('name' => 'Transaction Number', 
									'value' => $transactionNo));
		array_push($results, array('name' => 'Acquirer Response Code', 
									'value' => $acqResponseCode));		
		array_push($results, array('name' => 'Bank Authorization ID', 
									'value' => $authorizeID));
		array_push($results, array('name' => 'Batch Number', 
									'value' => $batchNo));																
		array_push($results, array('name' => 'Card Type', 
									'value' => $cardType));																																				
	}							
            
	return serialize($results);
	
	
	function getResponseDescription($responseCode) {

	    switch ($responseCode) {
	        case "0" : $result = "Transaction Successful"; break;
	        case "?" : $result = "Transaction status is unknown"; break;
	        case "1" : $result = "Unknown Error"; break;
	        case "2" : $result = "Bank Declined Transaction"; break;
	        case "3" : $result = "No Reply from Bank"; break;
	        case "4" : $result = "Expired Card"; break;
	        case "5" : $result = "Insufficient funds"; break;
	        case "6" : $result = "Error Communicating with Bank"; break;
	        case "7" : $result = "Payment Server System Error"; break;
	        case "8" : $result = "Transaction Type Not Supported"; break;
	        case "9" : $result = "Bank declined transaction (Do not contact Bank)"; break;
	        case "A" : $result = "Transaction Aborted"; break;
	        case "C" : $result = "Transaction Cancelled"; break;
	        case "D" : $result = "Deferred transaction has been received and is awaiting processing"; break;
	        case "F" : $result = "3D Secure Authentication failed"; break;
	        case "I" : $result = "Card Security Code verification failed"; break;
	        case "L" : $result = "Shopping Transaction Locked (Please try the transaction again later)"; break;
	        case "N" : $result = "Cardholder is not enrolled in Authentication scheme"; break;
	        case "P" : $result = "Transaction has been received by the Payment Adaptor and is being processed"; break;
	        case "R" : $result = "Transaction was not processed - Reached limit of retry attempts allowed"; break;
	        case "S" : $result = "Duplicate SessionID (OrderInfo)"; break;
	        case "T" : $result = "Address Verification Failed"; break;
	        case "U" : $result = "Card Security Code Failed"; break;
	        case "V" : $result = "Address Verification and Card Security Code Failed"; break;
	        default  : $result = "Unable to be determined"; 
	    }
	    return $result;
	}
	
	
	
	//  -----------------------------------------------------------------------------
	
	// This method uses the verRes status code retrieved from the Digital
	// Receipt and returns an appropriate description for the QSI Response Code
	
	// @param statusResponse String containing the 3DS Authentication Status Code
	// @return String containing the appropriate description
	
	function getStatusDescription($statusResponse) {
	    if ($statusResponse == "" || $statusResponse == "No Value Returned") {
	        $result = "3DS not supported or there was no 3DS data provided";
	    } else {
	        switch ($statusResponse) {
	            Case "Y"  : $result = "The cardholder was successfully authenticated."; break;
	            Case "E"  : $result = "The cardholder is not enrolled."; break;
	            Case "N"  : $result = "The cardholder was not verified."; break;
	            Case "U"  : $result = "The cardholder's Issuer was unable to authenticate due to some system error at the Issuer."; break;
	            Case "F"  : $result = "There was an error in the format of the request from the merchant."; break;
	            Case "A"  : $result = "Authentication of your Merchant ID and Password to the ACS Directory Failed."; break;
	            Case "D"  : $result = "Error communicating with the Directory Server."; break;
	            Case "C"  : $result = "The card type is not supported for authentication."; break;
	            Case "S"  : $result = "The signature on the response received from the Issuer could not be validated."; break;
	            Case "P"  : $result = "Error parsing input from Issuer."; break;
	            Case "I"  : $result = "Internal Payment Server system error."; break;
	            default   : $result = "Unable to be determined"; break;
	        }
	    }
	    return $result;
	}
	
	//  -----------------------------------------------------------------------------
	   
	// If input is null, returns string "No Value Returned", else returns input
	function null2unknown($data) {
	    if ($data == "") {
	        return "No Value Returned";
	    } else {
	        return $data;
	    }
	} 
?>