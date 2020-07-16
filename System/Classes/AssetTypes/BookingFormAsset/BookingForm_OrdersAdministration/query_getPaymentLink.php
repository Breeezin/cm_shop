<?php
	$this->param('tr_id');
	
	$Transaction = getRow("
		SELECT * FROM transactions
		WHERE tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
	");

	$Booking = getRow("
		SELECT * FROM booking_form_bookings
		WHERE bo_tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
	");
	
	$res = new Request('Asset.PathFromID',array(
		'as_id'	=>	$Booking['bo_as_id'],
	));
	$assetPath = $res->value;
	
	
?>