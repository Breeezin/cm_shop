<?php
	$data = array(
		'StartDate'		=>	$this->ATTRIBUTES['StartDate'],
		'EndDate'		=>	$this->ATTRIBUTES['EndDate'],
		'Filter'		=>	$this->ATTRIBUTES['Filter'],
		'Errors'		=>	$errors,
	);
	if (isset($Q_Invoices)) {
		$data['Q_Invoices'] =	$Q_Invoices;
	}
	$this->display->title = 'Supplier Orders Report';
	$this->display->layout = 'AdministrationPrint';
	
	$this->useTemplate('AcmeSupplierOrdersReport',$data);
?>