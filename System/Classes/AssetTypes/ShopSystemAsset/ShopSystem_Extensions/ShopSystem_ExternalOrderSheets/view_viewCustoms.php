<?php
	$this->display->layout = 'none';
	
	$data = array(
		'Q_OrderSheet'	=>	$Q_OrderSheet,
		'Q_Customs2'	=>	$Q_Customs2,
		'Q_products'	=> $Q_products,
		'Q_OrderSheetItems'	=>	$Q_OrderSheetItems,
		'BackURL'	=>	$this->ATTRIBUTES['BackURL'],
		'vendor'	=>  $vendorRow,
	);
	
	$this->useTemplate('Customs',$data);	

?>
