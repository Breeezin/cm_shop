<?php

	// Default some values
	$this->param('RowsPerPage','10');
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','10');
	$this->param('OrderBy','');
	$this->param('SortBy','');
	$this->param('vendor', 2);

	$this->param('SearchKeyword','');
	
	$limitSQL = '';
	
	$params = array();
	$params['StartRow'] = ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
	$params['MaxRows'] = $this->ATTRIBUTES['RowsPerPage'];
	
	if (($params['StartRow'] !== null) && ($params['MaxRows'] !== null)) {
		$limitSQL = "LIMIT {$params['StartRow']},{$params['MaxRows']}";
	}

	
	$allowedSQL = 'ors_ve_id = '.$this->ATTRIBUTES['vendor'];
	if (strlen($this->ATTRIBUTES['SearchKeyword'])) {
		$searchKeywordSQL = ' (0 = 1';
		$searchKeyword = str_replace("'","''",$this->ATTRIBUTES['SearchKeyword']);
		$searchfields = array('ors_id','ors_invoice_number','ors_notes', 'ors_date', 'orsi_pr_name');
		foreach($searchfields as $field) {			
			$searchKeywordSQL .= ' OR '.$field." like '%".$searchKeyword."%'";
		}	
		$searchKeywordSQL .= ')';
		$Q_Allowed = query("
			SELECT DISTINCT ors_id FROM {$this->tableName},{$this->tableName}Items
			WHERE orsi_ors_id = ors_id 
				AND ($searchKeywordSQL
				OR orsi_bs_code LIKE '%".$searchKeyword."%')
		");	
		if ($Q_Allowed->numRows() > 0) {
			$allowedSQL = 'ors_id IN ('.$Q_Allowed->columnValuesList('ors_id',',','').')';
		} else {
			$allowedSQL = '0=1';	
		}
	}

/*
	$result = query("
		SELECT * FROM {$this->tableName}
		WHERE 1 = 1
			$allowedSQL
		ORDER BY ors_id DESC
		$limitSQL
	");
*/

	$vendors = query( "select * from vendor" );

	$result = query("
		SELECT shopsystem_order_sheets.*, MIN(or_tr_id) as MinOrID, MAX(or_tr_id) as MaxOrID 
			FROM shopsystem_order_sheets join shopsystem_order_sheets_items on orsi_ors_id = ors_id
			join shopsystem_orders on or_id = orsi_or_id
			where $allowedSQL
		GROUP BY ors_id
		ORDER BY ors_id DESC
		$limitSQL
	");

	// query the database
/*	$result = $this->query(array(
		'StartRow'	=>	($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'],
		'MaxRows'	=>	$this->ATTRIBUTES['RowsPerPage'],
	));*/

	$countResult = getRow("
		SELECT COUNT(*) AS TheCount FROM $this->tableName 
		WHERE $allowedSQL
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
