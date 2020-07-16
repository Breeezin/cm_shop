<?php

	// Default some values
	$this->param('RowsPerPage','10');
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','10');
	$this->param('OrderBy','');
	$this->param('SortBy','');

	$this->param('SearchKeyword','');
	
	$limitSQL = '';
	
	$params = array();
	$params['StartRow'] = ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
	$params['MaxRows'] = $this->ATTRIBUTES['RowsPerPage'];
	
	if (($params['StartRow'] !== null) && ($params['MaxRows'] !== null)) {
		$limitSQL = "LIMIT {$params['StartRow']},{$params['MaxRows']}";
	}


	$result = query("
		SELECT * FROM supplier_invoice
		ORDER BY sin_id DESC
		$limitSQL
	");


	// query the database
/*	$result = $this->query(array(
		'StartRow'	=>	($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'],
		'MaxRows'	=>	$this->ATTRIBUTES['RowsPerPage'],
	));*/

	$countResult = getRow("
		SELECT COUNT(*) AS TheCount FROM $this->tableName 
	");
	$totalRows = $countResult['TheCount'];
	
	
	//$totalRows = $this->query(array('CountOnly'=>true));
	
	// display a page thru
	$backURL = $_SESSION['BackStack']->getURL();
	$pageThru = new Request('PageThru.Display',array(
		'ItemCount'		=>	$totalRows,	
		'ItemsPerPage'	=>	$this->ATTRIBUTES['RowsPerPage'],
		'CurrentPage'	=>	$this->ATTRIBUTES['CurrentPage'],
		'PagesPerBlock'	=>	$this->ATTRIBUTES['PagesPerBlock'],
		'URL'			=>	$backURL,
	));
	
	function FindAndReplace($str, $source) {
		$str = urldecode($str);
		
		foreach($source as $key =>$value) {						
			$str = str_replace("[$key]",$value,$str);		
		}
		//die($str);
		
		return $str;
	}
?>
