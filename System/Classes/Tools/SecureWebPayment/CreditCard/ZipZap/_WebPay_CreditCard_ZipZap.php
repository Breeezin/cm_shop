<?php

requireOnceClass('WebPay_CreditCard');

class WebPay_CreditCard_ZipZap extends WebPay_CreditCard {
	var $responseHTML = null;
	var $sentInfo = null;
	
	function displayTransactionResult(&$webpay) {		
		$this->classDirectory = ss_getClassDirectory('WebPay_CreditCard_ZipZap');
		return require('view_transactionResult.php');
	}
	
	function storeTransactionResult(&$webpay) {
		return $this->responseHTML;
	}
	function getTransactionSentInfo(&$webpay) {
		return $this->sentInfo;
	}
	function checkTransactionDone(&$webpay) {
		return require('model_checkTransactionDone.php');		
	}
}
?>
