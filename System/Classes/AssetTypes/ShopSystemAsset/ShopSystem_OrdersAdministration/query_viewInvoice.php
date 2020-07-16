<?php 

	$this->param('or_id');
	$this->param('tr_id');
	$this->param('as_id');
	
	
	$this->display->layout = 'none';
	
	$Q_Order = getRow("SELECT * FROM shopsystem_orders, transactions WHERE or_id = {$this->ATTRIBUTES['or_id']} AND tr_id = or_tr_id");
	$Q_Shop = getRow("SELECT * FROM assets WHERE as_id = {$this->ATTRIBUTES['as_id']}");
	
	$shopSetting = unserialize($Q_Shop['as_serialized']);
		
?>