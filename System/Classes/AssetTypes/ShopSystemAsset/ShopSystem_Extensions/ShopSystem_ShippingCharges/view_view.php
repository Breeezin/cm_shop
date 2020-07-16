<?php
	//$this->display->layout = 'none';
	$this->display->title = 'Shipping Charge';
	
	
	$data = array(
		'Q_ShippingCharge'	=>	$Q_ShippingCharge,
		'BackURL'	=>	$this->ATTRIBUTES['BackURL'],
	);
	
	$this->useTemplate('ShippingCharge',$data);	

?>