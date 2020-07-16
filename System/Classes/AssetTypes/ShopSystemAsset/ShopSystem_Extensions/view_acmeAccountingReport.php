<?php
	$data = array(
		'StartDate'		=>	$this->ATTRIBUTES['StartDate'],
		'EndDate'		=>	$this->ATTRIBUTES['EndDate'],
		'FilterSup'		=>	$this->ATTRIBUTES['FilterSup'],
		'FilterAb'		=>	$this->ATTRIBUTES['FilterAb'],
		'ReportType'	=>	$this->ATTRIBUTES['ReportType'],
		'Errors'		=>	$errors,
	);
	if (isset($Q_Invoices)) {
		$data['Q_Invoices'] =	$Q_Invoices;
	}
	$this->display->title = 'Accounting Report';
	$this->display->layout = 'AdministrationPrint';
	
	$this->useTemplate('AccountingReport',$data);
?>