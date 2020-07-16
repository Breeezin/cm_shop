<?

	$this->display->title = 'Bank Balance';

	$data = array(
		'Errors'	=>	$errors,
		'Done'	=>	$done,
		'Amount'	=>	$this->atts['Amount'],
	);

	$this->useTemplate('Bank',$data);
	
?>