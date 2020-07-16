<?php
	$this->display->layout = 'none';
	
	$data = array(
		'Q_OrderSheet'	=>	$Q_OrderSheet,
		'Q_OrderSheetItems'	=>	$Q_OrderSheetItems,
		'BackURL'	=>	$this->ATTRIBUTES['BackURL'],
	);
	
	$this->useTemplate('AcmeOrderSheet',$data);	

?>