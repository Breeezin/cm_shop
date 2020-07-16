<?php
	$data = array(
		'StartDate'		=>	$this->ATTRIBUTES['StartDate'],
		'EndDate'		=>	$this->ATTRIBUTES['EndDate'],
		'Errors'		=>	$errors,
		'Message'		=>	$message,
	);
	if (isset($Q_Products)) {
		$data['Q_Products'] =	$Q_Products;
	}
	$this->display->title = 'Listados para CEDOP';
	$this->display->layout = 'AdministrationPrint';
	
	$this->useTemplate('AcmeCEDOPReport',$data);
?>