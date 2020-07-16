<?php
	$this->param("CuPaID");
	$this->param("tr_id");
	$this->param("tr_token");

	$Q_Transaction = getRow("SELECT * FROM transactions WHERE tr_id = {$this->ATTRIBUTES['tr_id']} AND tr_token LIKE '{$this->ATTRIBUTES['tr_token']}' AND tr_completed = 1");
	$Q_Payments = getRow("SELECT * FROM CustomPayments WHERE CuPaID = {$this->ATTRIBUTES['CuPaID']} AND CuPaTransactionLink = {$this->ATTRIBUTES['tr_id']}");
	//ss_DumpVar($Q_Payments,$Q_Payments['CuPaSentEmail']);
	if (!strlen($Q_Payments['CuPaSentEmail'])) {
		// send da email
			$stylesheet = "Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}sty_main.css";		
			$result = new Request('Email.Send',array(
				'to'	=>	array($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']),
				'from'	=>	$asset->cereal[$this->fieldPrefix.'ADMINEMAIL'],
				'subject'	=>	"New Payment Received from {$GLOBALS['cfg']['website_name']}",
				'html'	=>	$Q_Payments['CuPaEmailContent'],
				'css'	=>	$stylesheet,
			));	
			$thankyou = "<p>Order Detail</p>";
			$stylesheet = "Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}sty_main.css";
			$result = new Request('Email.Send',array(
				'to'	=>	array($Q_Payments['CuPaCustomEmail']),
				'from'	=>	$asset->cereal[$this->fieldPrefix.'ADMINEMAIL'],
				'subject'	=>	"Order Receipt from {$GLOBALS['cfg']['website_name']}",
				'html'	=>	$thankyou.$Q_Payments['CuPaEmailContent'],			
				'css'	=>	$stylesheet,				
			));	
		$updateOrderSQL = '';								
		if ($Q_Payments['CuPaID'] == $this->ATTRIBUTES['CuPaID'] AND  $Q_Transaction['tr_id'] == $this->ATTRIBUTES['tr_id'] and $Q_Transaction['tr_status_link'] < 3 ) {
			
			if ($Q_Transaction['tr_payment_method'] != 'WebPay_CreditCard_Manual' 
				and $Q_Transaction['tr_payment_method'] != 'Cheque'
				and $Q_Transaction['tr_payment_method'] != 'Direct'
				and $Q_Transaction['tr_payment_method'] != 'Invoice'
				and $Q_Transaction['tr_payment_method'] != 'Collection'
				) {			
				$updateOrderSQL =", CuPaPaid = Now()";						
			}
		}		
					
			$Q_UpdateOrder = query("
					UPDATE CustomPayments 
					SET 
						CuPaSentEmail = 1
						$updateOrderSQL
					WHERE
						CuPaTransactionLink = {$this->ATTRIBUTES['tr_id']}
					AND
						CuPaID = {$this->ATTRIBUTES['CuPaID']}					
			");
			
			locationRelative("$assetPath/Service/ThankYou/Reference/{$Q_Transaction['tr_reference']}");
			
		
	}
	locationRelative("$assetPath/Service/ThankYou");
	
?>