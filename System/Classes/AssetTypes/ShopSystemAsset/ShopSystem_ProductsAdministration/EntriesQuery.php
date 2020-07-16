<?php

	// Default some values
	$this->param('RowsPerPage','10');
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','10');
	$this->param('OrderBy','');
	$this->param('SortBy','');
	
	$this->param('UpsellFilter','Main');
	$this->param('OfflineFilter','Main');
	$this->param('StockLevelFilter','All');
	$this->param('CategoryFilter','All');
	$this->param('External','All');	
	$this->param('ComboMultipack','All');	
	$this->param('PriceFilter','All');	
	$this->param('QuickOrderFilter','All');
	$this->param('DiscountGroupFilter','All');
	$this->param('WrapSafelyFilter','All');
	$this->param('ProductToGateway','');
	$this->param('SpecialToGateway','');
	
	$filterSQL = '';

	if ($this->ATTRIBUTES['OfflineFilter'] == 'Off')
		$filterSQL .= ' AND pr_offline = 1 ';	

	if ($this->ATTRIBUTES['OfflineFilter'] == 'On')
		$filterSQL .= ' AND pr_offline IS NULL ';	

	if ($this->ATTRIBUTES['UpsellFilter'] == 'Off')
		$filterSQL .= ' AND pr_upsell = 1 ';	

	if ($this->ATTRIBUTES['UpsellFilter'] == 'On')
		$filterSQL .= ' AND pr_upsell IS NULL ';	



	// stock level filter
	$stockLevelFilters = array(
		'All'			=>	'',
		'OutOfStock'	=>	' AND pro_stock_available <= 0 ',
		'InStock'		=>	' AND pro_stock_available > 0 ',
		'Unspecified'	=>	' AND pro_stock_available IS NULL ',
	);
	if (array_key_exists($this->ATTRIBUTES['StockLevelFilter'],$stockLevelFilters)) {
		$filterSQL .= $stockLevelFilters[$this->ATTRIBUTES['StockLevelFilter']];	
	}


	$categoryFilter = array(
		'All'	=>	'',	
	);
	$Q_Categories = query("select ca_name, ca_id from shopsystem_categories order by ca_name" );
	while($row = $Q_Categories->fetchRow()) {
		$categoryFilter[$row['ca_name']] = ' AND pr_ca_id = '.$row['ca_id'].' ';	
	}

	if (array_key_exists($this->ATTRIBUTES['CategoryFilter'],$categoryFilter)) {
		$filterSQL .= $categoryFilter[$this->ATTRIBUTES['CategoryFilter']];	
	}

	// external products
	$externalProduct = array(
		'All'			=>	'',
		'Las Palmas Origin'		=>	' AND (pr_ve_id = 0 OR pr_ve_id IS NULL) ',
	);
	$vendorsQ = query( "select * from vendor where ve_id IS NOT NULL" );
	while($row = $vendorsQ->fetchRow()) {
		$externalProduct[$row['ve_name']] = ' AND pr_ve_id = '.$row['ve_id'].' ';	
	}
	if (array_key_exists($this->ATTRIBUTES['External'],$externalProduct)) {
		$filterSQL .= $externalProduct[$this->ATTRIBUTES['External']];	
	}

	// gateway only specials/products
	$specialToGateway = array( 'All'			=>	'');
	$productToGateway = array( 'All'			=>	'');
	$gatewayQ = query( "select * from payment_gateways" );
	while($row = $gatewayQ->fetchRow()) {
		$specialToGateway[$row['pg_name']] = ' AND pr_restrict_special_to_gateway = '.$row['pg_id'].' ';	
		$productToGateway[$row['pg_name']] = ' AND pr_restrict_product_to_gateway = '.$row['pg_id'].' ';	
	}
	if (array_key_exists($this->ATTRIBUTES['SpecialToGateway'],$specialToGateway))
		$filterSQL .= $specialToGateway[$this->ATTRIBUTES['SpecialToGateway']];	

	if (array_key_exists($this->ATTRIBUTES['ProductToGateway'],$productToGateway))
		$filterSQL .= $productToGateway[$this->ATTRIBUTES['ProductToGateway']];	


	// free gift
	$comboMultipack = array(
		'All'			=>	'',
		'Combo'	=>	' AND pr_combo = 1 ',
		'Mulitpack'	=>	' AND pr_combo = 2 ',
		'Either'		=>	' AND pr_combo IS NOT NULL ',
	);

	if (array_key_exists($this->ATTRIBUTES['ComboMultipack'],$comboMultipack)) {
		$filterSQL .= $comboMultipack[$this->ATTRIBUTES['ComboMultipack']];	
	}
	// price filter
	$priceFilters = array(
		'All'			=>	'',
		'Free'	=>	' AND pro_price = 0 ',
		'On Special'		=>	' AND pro_special_price IS NOT NULL ',
	);
	if (array_key_exists($this->ATTRIBUTES['PriceFilter'],$priceFilters)) {
		$filterSQL .= $priceFilters[$this->ATTRIBUTES['PriceFilter']];	
	}

	// discount group filter
	$discountGroupFilters = array( 'All'			=>	'', );
	$dgQ = query( "select * from discounts" );
	while($row = $dgQ->fetchRow()) {
		$discountGroupFilters[$row['di_code']] = ' AND pr_dig_id = '.$row['di_discount_group'].' ';	
	}
	if (array_key_exists($this->ATTRIBUTES['DiscountGroupFilter'],$discountGroupFilters)) {
		$filterSQL .= $discountGroupFilters[$this->ATTRIBUTES['DiscountGroupFilter']];	
	}

	// wrap safely filter
	$wrapSafelyFilters = array(
		'All'			=>	'',
		'Needs Padding Only'	=>	' AND pr_needs_extra_padding = 1 ',
	);
	if (array_key_exists($this->ATTRIBUTES['WrapSafelyFilter'],$wrapSafelyFilters)) {
		$filterSQL .= $wrapSafelyFilters[$this->ATTRIBUTES['WrapSafelyFilter']];	
	}
	
	
	
	$quickOrderFilters = array(
		'All'	=>	'',	
		'No'	=>	" and pr_featured = 'no'",	
		'Featured'	=>	" and pr_featured = 'featured'",	
		'Popular'	=>	" and pr_featured = 'popular'",	
	);

	if (array_key_exists($this->ATTRIBUTES['QuickOrderFilter'],$quickOrderFilters)) {
		$filterSQL .= $quickOrderFilters[$this->ATTRIBUTES['QuickOrderFilter']];	
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
