<?php

class WebPay_CreditCard extends Plugin  {
	
	var $fieldSet = NULL;
	var $classDirectory = '';
	
	function checkTransactionDone(&$webpay) {		
		return 1;
	}
	
	function storeTransactionResult(&$webpay) {
		return '';
	}
	
	function getTransactionSentInfo(&$webpay) {
		return '';
	}
	
	function displayTransactionResult(&$webpay) {		
		$this->classDirectory = ss_getClassDirectory('WebPay_CreditCard');
		return require('view_transactionResult.php');
	}
	
	
	function display(&$webpay) {
		return require('view_display.php');
	}
	
	function processPayment(&$webpay) {
		print("Please define processPayment method");
		//return require('model_process.php');
	}
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function defineFields(&$webpay) {
		require('query_defineFields.php');
	}
	
	function newPayment(&$webpay) {
		return require('view_newPayment.php');
	}

}
?>
