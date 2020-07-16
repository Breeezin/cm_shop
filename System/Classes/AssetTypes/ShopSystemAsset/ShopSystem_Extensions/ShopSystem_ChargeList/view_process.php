<?php
	$data = array(
		'Q_Orders'	=>	$Q_Orders,
		'ccTypes'	=>	$ccTypes,
	);
	$this->display->title = 'Process List';
	
	$this->useTemplate("Process",$data);
?>
