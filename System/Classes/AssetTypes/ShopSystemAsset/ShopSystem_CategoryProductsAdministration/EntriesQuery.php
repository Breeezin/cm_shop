<?php

	// Default some values
	$this->param('RowsPerPage','10');
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','10');
	$this->param('OrderBy','');
	$this->param('SortBy','');
	
	$this->param('OptionsFilter','Main');
	$this->param('StockLevelFilter','All');
	
	$filterSQL = '';
	if ($this->ATTRIBUTES['OptionsFilter'] == 'Main') {
		$filterSQL .= ' AND pro_is_main = 1 ';	
	}

	$stockLevelFilters = array(
		'All'			=>	'',
		'OutOfStock'	=>	' AND pro_stock_available <= 0 ',
		'InStock'		=>	' AND pro_stock_available > 0 ',
		'Unspecified'	=>	' AND pro_stock_available IS NULL ',
	);
	if (array_key_exists($this->ATTRIBUTES['StockLevelFilter'],$stockLevelFilters)) {
		$filterSQL .= $stockLevelFilters[$this->ATTRIBUTES['StockLevelFilter']];	
	}
	
	
	// query the database
	$result = $this->query(array(
		'FilterSQL'	=>	$filterSQL,
		'StartRow'	=>	($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'],
		'MaxRows'	=>	$this->ATTRIBUTES['RowsPerPage'],
	));

	$totalRows = $this->query(array(
		'FilterSQL'	=>	$filterSQL,
		'CountOnly'=>true
	));
	
	$totalProducts = $this->query(array(
		'FilterSQL'	=>	' AND pro_is_main = 1 ',
		'CountOnly'=>true
	));	
	
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