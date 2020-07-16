<?php
	$data = array(
		'Q_Orders'	=>	$Q_Orders
	);
	
	$this->display->title = 'DUA Preparation Report';
	
	$this->useTemplate('AcmeDUAPreparationList',$data);
?>