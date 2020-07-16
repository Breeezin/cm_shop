<?php

	$this->param('CoInID');
	
	$Q_Invoice = query("
		SELECT * FROM ShopSystem_CounterInvoices, shopsystem_invoices
		WHERE CoInID = ".safe($this->ATTRIBUTES['CoInID'])."
		 	AND CoInOriginalInvoiceLink = inv_id
	");	
	
	//$blackListcheck = new Request('shopsystem_blacklist.CheckClient', array(''))
	
	$this->display->layout = 'None';
	$this->display->title = 'Factura Björck Bros. S.L.';
	
	
?>