<?php 
	$this->param("tr_id");
	$this->param("BackURL");
	$this->param("as_id");	
	
	
	$deleteWebPay = new Request("WebPayAdministration.DeletePayment",array("tr_id"=> $this->ATTRIBUTES['tr_id']));
	
	if (count($deleteWebPay->value)) {
		print("Error occoured");
		
	} else {
		// get the length of the subscription to renew		
		$Q_DeleteOrder = query("DELETE FROM CustomPayments WHERE CuPaTransactionLink = {$this->ATTRIBUTES['tr_id']}");	
		
		locationRelative($this->ATTRIBUTES['BackURL']);
	}
?>