<?php
	$this->display->layout = 'none';
	
	$data = array(
		'Q_OrderSheetItems'	=>	$Q_OrderSheetItems,
		'BackURL'	=>	$this->ATTRIBUTES['BackURL'],
	);
	
	$this->useTemplate('ManageLocations',$data);	

?>
