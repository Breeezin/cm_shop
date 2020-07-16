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

	
	$allowedSQL = 'AND 1=1';
	if (strlen($this->ATTRIBUTES['SearchKeyword'])) {
		$searchKeywordSQL = ' (0 = 1';
		$searchKeyword = str_replace("'","''",$this->ATTRIBUTES['SearchKeyword']);
		$searchfields = array('sos_id','sos_invoice_number','sos_notes');
		foreach($searchfields as $field) {			
			$searchKeywordSQL .= ' OR '.$field." like '%".$searchKeyword."%'";
		}	
		$searchKeywordSQL .= ')';
		$Q_Allowed = query("
			SELECT DISTINCT sos_id FROM {$this->tableName},{$this->tableName}Items
			WHERE soit_sos_id = sos_id 
				AND ($searchKeywordSQL
				OR soit_bs_code LIKE '%".$searchKeyword."%')
		");	
		if ($Q_Allowed->numRows() > 0) {
			$allowedSQL = ' AND sos_id IN ('.$Q_Allowed->columnValuesList('sos_id',',','').')';
		} else {
			$allowedSQL = ' AND 0=1';	
		}
	}
	
	$result = query("
		SELECT * FROM {$this->tableName}
		WHERE 1 = 1
			$allowedSQL
		ORDER BY sos_id DESC
		$limitSQL
	");
	
	// query the database
/*	$result = $this->query(array(
		'StartRow'	=>	($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'],
		'MaxRows'	=>	$this->ATTRIBUTES['RowsPerPage'],
	));*/

	$countResult = getRow("
		SELECT COUNT(*) AS TheCount FROM $this->tableName 
		WHERE 1=1
			$allowedSQL
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