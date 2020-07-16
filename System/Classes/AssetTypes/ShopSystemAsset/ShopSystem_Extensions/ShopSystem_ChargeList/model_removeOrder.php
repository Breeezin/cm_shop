<?php 
	$this->param('or_id');
	$this->param('BackURL');
	
	$Q_Update = query("
		UPDATE shopsystem_orders
		SET or_charge_list = NULL
		WHERE or_id = {$this->ATTRIBUTES['or_id']}
	");
	
	locationRelative($this->ATTRIBUTES['BackURL']);
?>