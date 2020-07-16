<?php

requireOnceClass('WebPay_CreditCard');

class WebPay_CreditCard_HPP extends WebPay_CreditCard {
	var $responseHTML = null;
	var $sentInfo = null;
	
	function __construct() {
		parent::__construct();
//		$this->WebPay_CreditCard();		
	}
	
	function displayTransactionResult(&$webpay) {		
		$this->classDirectory = ss_getClassDirectory('WebPay_CreditCard_HPP');
		return require('view_transactionResult.php');
	}
//briar put this here
	function newPayment(&$webpay) {
		return require('view_newPayment.php');
	}
	function storeTransactionResult(&$webpay) {
		return $this->responseHTML;
	}
	function getTransactionSentInfo(&$webpay) {
        //there won't be sent info as it is done elsewhere
        //return $this->sentInfo;
	}
	function checkTransactionDone(&$webpay) {
		return require('model_checkTransactionDone.php');
	}
}
?>
