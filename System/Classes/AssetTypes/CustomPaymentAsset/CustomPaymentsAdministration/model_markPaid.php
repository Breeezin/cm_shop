<?php 
	
	$this->param("tr_id");		
	$this->param("CuPaID");	
	$this->param("as_id");	
	$this->param("BackURL");	
	
	
	$updateWebPay = new Request("WebPayAdministration.ProcessPayment",array("tr_id"=> $this->ATTRIBUTES['tr_id']));
	
	if (count($updateWebPay->value)) {
		print("Error occoured");
		
	} else {					
		$Q_Form = query("UPDATE CustomPayments SET CuPaPaid = Now() WHERE CuPaID = {$this->ATTRIBUTES['CuPaID']}");
		
		$res = new Request("WebPay.MarkPaid",array(
			'tr_id'	=>	$this->ATTRIBUTES['tr_id'],
		));
		locationRelative($this->ATTRIBUTES['BackURL']);
	}
?>