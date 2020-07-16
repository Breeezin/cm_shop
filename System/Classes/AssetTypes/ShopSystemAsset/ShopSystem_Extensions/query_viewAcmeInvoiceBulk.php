<?php

	$this->param('InIDFrom');
	$this->param('InIDTo');
	
	$Q_Invoice = query("
		SELECT * FROM shopsystem_invoices
		WHERE inv_id >= ".safe($this->ATTRIBUTES['InIDFrom'])."
		  AND inv_id <= ".safe($this->ATTRIBUTES['InIDTo'])."
	");	
	
	//$blackListcheck = new Request('shopsystem_blacklist.CheckClient', array(''))
	
	$this->display->layout = 'None';
	$this->display->title = 'Factura Björck Bros. S.L.';
	
	
?>
