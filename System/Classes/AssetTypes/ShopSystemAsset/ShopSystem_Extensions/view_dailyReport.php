<?

	$data = array(
		'Q_ShippedToday'	=>	$Q_ShippedToday,
		'Errors'	=>	$errors,
		'Amount'	=>	$this->atts['Amount'],
		'Ref'	=>	$this->atts['Ref'],
		'BankAmount'	=>	$this->atts['BankAmount'],
		'Stock'	=>	$this->atts['Stock'],
		'Done'	=>	$done,
		'Note'	=>	$this->atts['Note'],
		'ShipDate'	=>	$this->atts['ShipDate'],
	);

	$this->display->title = 'Daily Report';
	
	$this->useTemplate('DailyReport',$data);
	
?>