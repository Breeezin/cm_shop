<?php

	// Default some values
	$this->param('RowsPerPage','10');
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','10');

	// calculate start and max rows
	$StartRow = ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
	$MaxRows = $this->ATTRIBUTES['RowsPerPage'];
	
	$Q_Newsletters = query("
		SELECT * FROM newsletter_archive
		WHERE na_as_id = {$this->ATTRIBUTES['as_id']}
			OR na_as_id IS NULL
		ORDER BY na_sent DESC
		LIMIT $StartRow, $MaxRows;
	");

	$totalRows = getRow("
		SELECT COUNT(*) AS Total FROM newsletter_archive
		WHERE na_as_id = {$this->ATTRIBUTES['as_id']}
			OR na_as_id IS NULL
	");
	
	// display a page thru
	$backURL = $_SESSION['BackStack']->getURL();
	$pageThru = new Request('PageThru.Display',array(
		'ItemCount'		=>	$totalRows['Total'],	
		'ItemsPerPage'	=>	$this->ATTRIBUTES['RowsPerPage'],
		'CurrentPage'	=>	$this->ATTRIBUTES['CurrentPage'],
		'PagesPerBlock'	=>	$this->ATTRIBUTES['PagesPerBlock'],
		'URL'			=>	$backURL,
	));	
	
	$result = new Request("Asset.PathFromID",array(
		'as_id'	=>	$this->ATTRIBUTES['as_id']
	));
	$this->display->title = ss_withoutPreceedingSlash($result->value);

	
?>