<?php

requireOnceClass('WebPay_CreditCard');

class WebPay_CreditCard_PayPro extends WebPay_CreditCard {
	
	function storeTransactionResult(&$webpay) {
		return require('model_storePaymentResult.php');
	}
	function checkTransactionDone(&$webpay) {
		ss_paramKey($webpay->ATTRIBUTES, 'Result','');
		
		if ($webpay->ATTRIBUTES['Result'] == 'OK') 
			return 2;
		else
			return 3;
	}
	
	function newPayment(&$webpay) {
		return require('view_newPayment.php');
	}
	
	function display(&$webpay) {
		return require('view_display.php');
	}
		
	function processPayment(&$webpay) {
		/*
		 *	process the payment
		 */
		 		 
		 $this->display->layout = 'None';		
		 require('model_processPayment.php');
	}
}
?>
