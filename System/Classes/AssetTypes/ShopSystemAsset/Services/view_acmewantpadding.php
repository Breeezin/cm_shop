<?
	$data = array(
		'Name'	=>	$this->atts['Name'],
		'Errors'	=>	$errors,
		'BackURL'	=>	$this->atts['BackURL'],
	);

	$this->useTemplate('AcmeWantPadding',$data);
	$asset->display->title = 'Additional Padding Required';
?>