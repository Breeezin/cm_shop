<?php 

	$this->param('bo_id');	
	$this->param('tr_id');	
	$this->param('BackURL');	
		
	$Q_DeleteOrder = query("
			DELETE FROM booking_form_bookings
			WHERE bo_id = {$this->ATTRIBUTES['bo_id']}
	");
	
	$Q_DeleteTransaction = query("
			DELETE FROM transactions 			
			WHERE tr_id = {$this->ATTRIBUTES['tr_id']}
	");
	
	locationRelative($this->ATTRIBUTES['BackURL']);
		
?>