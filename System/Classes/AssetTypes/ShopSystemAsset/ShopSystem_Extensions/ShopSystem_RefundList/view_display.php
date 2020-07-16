<?php
	$data = array(
		'Q_Orders'	=>	$Q_Orders,
		'OrdersCount'	=>	$OrdersCount,
		'ccTypes'	=>	$ccTypes,
		'List'		=>  $list,
	);
	$this->display->title = 'Refund List';
	
	$this->useTemplate("Display",$data);
?>
