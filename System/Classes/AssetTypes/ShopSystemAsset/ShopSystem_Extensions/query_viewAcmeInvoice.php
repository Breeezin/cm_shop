<?php

	$this->param('inv_id');

	$Q_Invoice = query("
		SELECT * FROM shopsystem_invoices JOIN shopsystem_orders on in_or_id = or_id
		join transactions on tr_id = or_tr_id
		WHERE inv_id = ".safe($this->ATTRIBUTES['inv_id'])."
	");	

	$Invoice = getRow("
		SELECT * FROM shopsystem_invoices JOIN shopsystem_orders on in_or_id = or_id
		join transactions on tr_id = or_tr_id
		WHERE inv_id = ".safe($this->ATTRIBUTES['inv_id'])."
	");	

	//$blackListcheck = new Request('shopsystem_blacklist.CheckClient', array(''))
	
	$this->display->layout = 'None';
	$this->display->title = 'Factura Björck Bros. S.L.';
	
	
?>
