<?php
	$this->display->layout = 'none';
	
	$data = array(
		'Q_OrderSheet'	=>	$Q_OrderSheet,
		'Q_OrderSheetItems'	=>	$Q_OrderSheetItems,
		'Q_Products'	=>	$Q_Products,
		'BackURL'	=>	$this->ATTRIBUTES['BackURL'],
		'Errors'	=>	$errors,
	);
	
	$this->useTemplate('AcmeOrderSheetEdit',$data);	

?>