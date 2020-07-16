<?php

requireOnceClass('WebPay_CreditCard');

class WebPay_CreditCard_Manual extends WebPay_CreditCard {
	
	function processPayment(&$webpay) {
		/*
		 *	process the payment
		 */
		 		 
		 $this->display->layout = 'None';		
		 require('model_processPayment.php');
	}
	/*
	
	
	function prepareDrawPaymentForm() {
		parent::prepareDrawPaymentForm();
	}
	
	
	function drawPaymentForm(&$errors) {
		parent::drawPaymentForm($errors);
	}
	
	function newPayment() {
		$this->display->layout = 'None';
		require('query_newPayment.php');
		require('model_newPayment.php');
		require('view_newPayment.php');
		
	}

	function displayPayment() {
		parent::displayPayment();
	}
	
	function deletePayment() {
		parent::deletePayment();		
	}
	
	function exposeServices() {
		
		$prefix = 'WebPay_CreditCard_Manual';
		
		return array(
			"$prefix.New"			=>		array('method' => 'newPayment'),
			"$prefix.Delete"		=>		array('method' => 'deletePayment'),
			"$prefix.Process"		=>		array('method' => 'processPayment'),
			"$prefix.Display"		=>		array('method' => 'displayPayment'),
		);
	}
	*/

}
?>
