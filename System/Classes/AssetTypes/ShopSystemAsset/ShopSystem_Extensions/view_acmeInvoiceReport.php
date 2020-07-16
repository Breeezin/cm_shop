<?php
	$data = array(
		'StartDate'		=>	$this->ATTRIBUTES['StartDate'],
		'EndDate'		=>	$this->ATTRIBUTES['EndDate'],
		'Errors'		=>	$errors,
	);
	if (isset($Q_Invoices)) {
		$data['Q_Invoices'] =	$Q_Invoices;
	}
	$this->display->title = 'Invoices Report';
	$this->display->layout = 'AdministrationPrint';
	
	$this->useTemplate('AcmeInvoiceReport',$data);
?>