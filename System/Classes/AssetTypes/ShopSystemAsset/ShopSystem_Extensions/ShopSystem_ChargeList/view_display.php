<?php
	$data = array(
		'Q_Orders'	=>	$Q_Orders,
		'OrdersCount'	=>	$OrdersCount,
		'ccTypes'	=>	$ccTypes,
		'List'		=>  $list,
		'OneAtATime' => $one_at_a_time,
	);
	$this->display->title = 'Charge List';
	
	$this->useTemplate("Display",$data);
?>
