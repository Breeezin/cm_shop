<?

	$data = array(
		'Q_ShippedToday'	=>	$Q_ShippedToday,
		'Errors'	=>	$errors,
		'Amount'	=>	$this->atts['Amount'],
		'Ref'	=>	$this->atts['Ref'],
	);

	$this->display->title = 'Correos';
	
	$this->useTemplate('ShippingReport',$data);
	
?>