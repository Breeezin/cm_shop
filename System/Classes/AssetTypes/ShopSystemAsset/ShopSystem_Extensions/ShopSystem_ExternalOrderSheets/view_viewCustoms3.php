<?php
	$this->display->layout = 'none';
	
	$data = array(
		'Q_OrderSheet'	=>	$Q_OrderSheet,
		'Q_OrderSheetItems'	=>	$Q_OrderSheetItems,
		'Distributor' => $this->ATTRIBUTES['Distributor'],
		'BackURL'	=>	$this->ATTRIBUTES['BackURL'],
		'vendor'	=>  $vendor,
	);
	
	$this->useTemplate('Customs3',$data);	

?>
