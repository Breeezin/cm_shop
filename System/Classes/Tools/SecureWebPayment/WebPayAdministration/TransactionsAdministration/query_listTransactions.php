<?php

	$this->param('RowsPerPage','10');
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','10');
	$this->param('BreadCrumbs', 'transactions');
	$this->param('SearchKeyword', '');
	$this->param('Status', '-1');
	$this->param('Method', '-1');
	
	$Q_Configuration = query("SELECT * FROM web_pay_configuration, countries WHERE wpc_id=1 AND WePaCoCountryLink = cn_id");
	//$Q_Processors = query("SELECT * FROM web_pay_processors");
	
	$Q_Status = query("SELECT * FROM transaction_status");
	$Q_Methods = query("SELECT * FROM web_pay_processors");
	
	
	$search = "";
	
	if (strlen($this->ATTRIBUTES['SearchKeyword'])) {
		$search = "AND tr_reference LIKE '%".$this->ATTRIBUTES['SearchKeyword']."%' ";
	}

	if ($this->ATTRIBUTES['Status'] != '-1' && strlen($this->ATTRIBUTES['Status'])) {		
		$search .= " AND tr_status_link = {$this->ATTRIBUTES['Status']} ";	
	}
	
	if ($this->ATTRIBUTES['Method'] != '-1' && strlen($this->ATTRIBUTES['Method'])) {
		$search .= " AND TrProcessorLink = {$this->ATTRIBUTES['Method']} ";
	}
	
	// query the database
	$result = query("SELECT * FROM transactions, transaction_status
					WHERE tr_status_link = trs_id
					$search
					ORDER BY tr_reference");

	// display a page thru
	$backURL = $_SESSION['BackStack']->getURL();
	
	$pageThru = new Request('PageThru.Display',array(
		'ItemCount'		=>	$result->numRows(),	
		'ItemsPerPage'	=>	$this->ATTRIBUTES['RowsPerPage'],
		'CurrentPage'	=>	$this->ATTRIBUTES['CurrentPage'],
		'PagesPerBlock'	=>	$this->ATTRIBUTES['PagesPerBlock'],
		'URL'			=>	'index.php?act=transactions.List',
	));
	//ss_log_message_r("page", $pageThru);
	//die("here");

?>
