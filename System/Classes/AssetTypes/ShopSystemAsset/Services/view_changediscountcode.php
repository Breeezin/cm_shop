<?php

	$action = 'cleared';
	$asset->display->title = 'Discount Code Cleared';
	
	if (is_array($_SESSION['Shop']['DiscountCode'])) {
		$action = 'set';	
		$asset->display->title = 'Discount Code Accepted';
	} else if (strlen($this->ATTRIBUTES['DiscountCode'])) {
		$action = 'invalid';
		$asset->display->title = 'Invalid Discount Code';
	}

	$data = array(
		'Action'	=>	$action,
		'BackURL'	=>	$this->ATTRIBUTES['BackURL'],
		'Discount'	=>	$_SESSION['Shop']['DiscountCode'],
	);
	// Always link in the shop style sheet
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('DiscountCode',$data);
	
?>
