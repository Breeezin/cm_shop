<?php 
	$this->param("BreadCrumb",''); // root breadcrumbs
	$this->param("as_id"); // To make a goup between AssetLink	
		
	// Default some values
//	ss_DumpVar( $this->ATTRIBUTES );
//	ss_DumpVar( $_GET);
	$this->param('RowsPerPage','10');
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','10');
	$this->param('BreadCrumbs','');
	$this->param('SearchArea','');
	$this->param('SearchKeyword','');
	$this->param('ShippingDateKeyword','');
	$this->param('OrderBy','tr_id');
	$this->param('OrderByType','DESC');
	$this->param('FilterBy','');
	$this->param('VendorFilterBy','');
	$this->param('PaymentGatewayFilterBy','');
	$this->param('ArchiveFilterBy','or_archive_year IS NULL');
	$this->param('CompletedFilter','1');
	$this->param('SiteFolder','');

	$backURL = getBackURL();//$_SESSION['BackStack']->getURL();
	global $cfg;
	//$cfg['currentServer'] 
	$shortBackURL = str_replace($cfg['currentServer'],'',$backURL);		
				
	$Q_Asset = getRow("SELECT * FROM assets WHERE as_id = {$this->ATTRIBUTES['as_id']}");
	
	
	
	$result = new Request("{$Q_Asset['as_type']}.OrderListSettings", array(
		'as_id'	=>	$this->ATTRIBUTES['as_id'],
	));	
	$listSettings = $result->value;
	
	set_time_limit( 300 );
	
	ss_paramKey($listSettings,'tr_completed',true);
	ss_paramKey($listSettings,'DisplayFields',array());
	ss_paramKey($listSettings,'DisplayFieldTitles',array());
	$this->tableDisplayFields = array_merge($listSettings['DisplayFields'],$this->tableDisplayFields);
	$this->tableDisplayFieldTitles = array_merge($listSettings['DisplayFieldTitles'],$this->tableDisplayFieldTitles);
	
	$optionActions = $listSettings['Options'];
	
	if(!strlen($this->ATTRIBUTES['BreadCrumbs']) AND strlen($listSettings['BreadCrumb'])) 
    {		
		//$this->ATTRIBUTES['BreadCrumbs'] = "Administration : <a href=\"$backURL\">{$listSettings['BreadCrumb']}</a>";
		$this->ATTRIBUTES['BreadCrumbs'] = "Administration : {$listSettings['BreadCrumb']}";
	}
	
	// build searchKeyword SQL
	$searchKeywordSQL = '';
	$searchArea = $this->ATTRIBUTES['SearchArea'];
	$searchKeyword = $this->ATTRIBUTES['SearchKeyword'];
	//ss_paramKey($listSettings, 'SearchFields',array());


/*
	if (strlen($searchKeyword) > 0) 
	{
		if( substr( $searchKeyword, 0, 1 ) == '+' )
		{
			$searchKeyword = substr( $searchKeyword, 1 );
			$searchKeywordSQL = 'AND (0 = 1';
			$searchKeyword = str_replace("'","''",$searchKeyword);
			$searchfields = $this->tableDisplayFields;
			if (array_key_exists('DisplayFields', $listSettings)) 
			{
				$searchfields = array_merge($searchfields, $listSettings['DisplayFields']);
			}		
			if (array_key_exists('SearchFields', $listSettings)) 
			{
				$searchfields = array_merge($searchfields, $listSettings['SearchFields']);
			}		
			
			foreach($searchfields as $field) 
			{	
				if( $field == 'TrSelect' )			// artificial field, not in table.
					continue;

				$searchKeywordSQL .= " OR (1=1";			
				foreach(ListToArray($this->ATTRIBUTES['SearchKeyword']," ") as $keyword) 
				{
					$searchKeywordSQL .= " AND ($field LIKE '%".escape($keyword)."%')";											
				}
				$searchKeywordSQL .= ')';		
			}
			$searchKeywordSQL .= ')';										
		}
		else
		{
		}
		*/
	if (strlen($searchKeyword) > 0) 
		switch( $searchArea )
		{
			case 0:		// order number
				if( is_numeric( $searchKeyword ) )		// order number (transaction number)
					$searchKeywordSQL = " AND or_tr_id = ".escape( $searchKeyword );
				break;

			case 1:		// name
				$Q_foo = query("select us_id from users where us_first_name like '%".escape($searchKeyword)."%' OR us_last_name like '%".escape($searchKeyword)."%' OR CONCAT_WS(' ', TRIM(us_first_name), TRIM(us_last_name)) like '%".escape($searchKeyword)."%'");

				if( $Q_foo->numRows() > 0 )
				{
					$searchKeywordSQL = " AND (";

					while( $foo_row = $Q_foo->fetchRow( ) )
						$searchKeywordSQL .= "or_us_id = ".$foo_row['us_id']." OR ";

					$searchKeywordSQL = substr( $searchKeywordSQL, 0, strlen( $searchKeywordSQL )-4 );
					$searchKeywordSQL .= ")";
				}
				else
					$searchKeywordSQL = " AND 0=1";
				break;

			case 2:		// email address
				$Q_foo = query("select us_id from users where us_email like '%".escape($searchKeyword)."%'");

				if( $Q_foo->numRows() > 0 )
				{
					$searchKeywordSQL = " AND (";

					while( $foo_row = $Q_foo->fetchRow( ) )
						$searchKeywordSQL .= "or_us_id = ".$foo_row['us_id']." OR ";

					$searchKeywordSQL = substr( $searchKeywordSQL, 0, strlen( $searchKeywordSQL )-4 );
					$searchKeywordSQL .= ")";
				}
				else
					$searchKeywordSQL = " AND 0=1";
				break;

			case 3:		// address
				$searchKeywordSQL = " AND or_shipping_details like '%".escape($searchKeyword)."%'";
				break;

			case 4:		// stock code
				$Q_foo = query("select op_or_id from ordered_products where op_stock_code like '%".escape($searchKeyword)."%' order by op_or_id DESC limit 500");

				if( $Q_foo->numRows() > 0 )
				{
					$searchKeywordSQL = " AND (";

					while( $foo_row = $Q_foo->fetchRow( ) )
						$searchKeywordSQL .= "or_id = ".$foo_row['op_or_id']." OR ";

					$searchKeywordSQL = substr( $searchKeywordSQL, 0, strlen( $searchKeywordSQL )-4 );
					$searchKeywordSQL .= ")";
				}
				else
					$searchKeywordSQL = " AND 0=1";
				break;

			case 5:		// card
				$searchKeywordSQL = " AND tr_payment_details_szln like '%".escape($searchKeyword)."%'";
				break;

			case 6:    // transaction
				if( is_numeric( $searchKeyword ) )		// order number (transaction number)
					$searchKeywordSQL = " AND or_id = ".escape( $searchKeyword );
				break;

			case 7:
				$searchKeywordSQL = " AND or_authorisation_number like '".escape( $searchKeyword )."'";
				break;

			case 8:
				$Q_foo = query("select orn_or_id from shopsystem_order_notes where orn_text like '%".escape($searchKeyword)."%' order by orn_or_id DESC limit 500");

				if( $Q_foo->numRows() > 0 )
				{
					$searchKeywordSQL = " AND (";

					while( $foo_row = $Q_foo->fetchRow( ) )
						$searchKeywordSQL .= "or_id = ".$foo_row['orn_or_id']." OR ";

					$searchKeywordSQL = substr( $searchKeywordSQL, 0, strlen( $searchKeywordSQL )-4 );
					$searchKeywordSQL .= ")";
				}
				else
					$searchKeywordSQL = " AND 0=1";
				break;

			case 9:
				$searchKeywordSQL = " AND or_us_id = ".escape($searchKeyword);

		}


	$shippingDateSQL = '';
	if (strlen($this->ATTRIBUTES['ShippingDateKeyword'])) 
    {
		if (ListLen($this->ATTRIBUTES['ShippingDateKeyword'],'-') == 3) 
        {
			$endDay = ListGetAt($this->ATTRIBUTES['ShippingDateKeyword'],3,'-');
			$endMonth = ListGetAt($this->ATTRIBUTES['ShippingDateKeyword'],2,'-');
			$endYear = ss_AdjustTwoDigitYear(ListGetAt($this->ATTRIBUTES['ShippingDateKeyword'],1,'-'));
			
			if (checkdate($endMonth,$endDay,$endYear)) 
            {						
				$endDate = mktime(0,0,0,$endMonth,$endDay,$endYear);
				$this->ATTRIBUTES['ShippingDateKeyword'] = date('Y-m-d',$endDate);
				$shippingDateSQL = "AND (or_basket LIKE '%\"".$this->ATTRIBUTES['ShippingDateKeyword']."\"%')";
			}
            else 
            {
				$shippingDateSQL = 'AND 1=0';
			}
		}
	}
	

	$whereSQL = '';
	if (strlen($this->ATTRIBUTES['FilterBy'])) 
		$whereSQL = "AND ".$this->ATTRIBUTES['FilterBy'];	

	if (strlen($this->ATTRIBUTES['ArchiveFilterBy'])) 
		$whereSQL .= " AND ".$this->ATTRIBUTES['ArchiveFilterBy'];	

	$trCompleted = 'AND tr_completed > 0';
	if ($this->ATTRIBUTES['CompletedFilter'] == 0) 
		$trCompleted = 'AND tr_completed = 0';

	if (strlen($this->ATTRIBUTES['VendorFilterBy'])) 
	{
		// hack this to make the subselect a scalar list

		$sql = preg_replace( "/.*\((.*)\).*/", "$1", $this->ATTRIBUTES['VendorFilterBy'] );
		$Q_Sub = query( $sql );
		$results = "";
		while( $row = $Q_Sub->fetchRow( ) )
		{
			foreach( $row as $key=> $val )
			{
				if( strlen( $results ) )
					$results .= ", ";
				$results .= $val;
			}
		}
		$altered = substr( $this->ATTRIBUTES['VendorFilterBy'], 0, strpos( $this->ATTRIBUTES['VendorFilterBy'], '(' ) + 1)
					. $results
					. substr( $this->ATTRIBUTES['VendorFilterBy'], strpos( $this->ATTRIBUTES['VendorFilterBy'], ')' ) );
//		$altered =  preg_replace( "/(.* \().*(\).*)/", "AND $1".$results."$2", $this->ATTRIBUTES['VendorFilterBy'] );
//		$whereSQL = "AND ".$this->ATTRIBUTES['VendorFilterBy'];	
		$whereSQL .= " AND ".$altered;

	}

	if (strlen($this->ATTRIBUTES['PaymentGatewayFilterBy'])) 
		$whereSQL .= " AND ".$this->ATTRIBUTES['PaymentGatewayFilterBy'];	

	if (strlen($this->ATTRIBUTES['SiteFolder'])) 
    {
		if (array_key_exists('MultiSiteFilter', $listSettings)) 
        {
			$whereSQL .= " AND {$listSettings['MultiSiteFilter']} LIKE '{$this->ATTRIBUTES['SiteFolder']}'";	
		}
        else 
        {
			die("Incorrect List Setting. The system need the 'MultiSiteFilter' in the setting.");
		}
	}
	

	if (array_key_exists('FilterByMulti',$listSettings)) 
    {
		foreach ($listSettings['FilterByMulti'] as $filter) 
        {
			$this->param('FilterBy'.$filter['name'],'');
			if (strlen($this->ATTRIBUTES['FilterBy'.$filter['name']])) 
            {
				$whereSQL .= " AND ".$this->ATTRIBUTES['FilterBy'.$filter['name']];	
			}
		}
	}
		
		
	$startRow= ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
	$maxRows =	$this->ATTRIBUTES['RowsPerPage'];
	
//AND ((tr_status_link > 1) or ((tr_payment_method = 'WebPay_CreditCard_ZipZap' OR tr_payment_method = 'WebPay_CreditCard_DPS') AND (tr_status_link = 2)))	

//	echo "SELECT * 
//			FROM {$listSettings['JoinTable']}, transactions, countries
//			WHERE {$listSettings['JoinTablePrefix']}AssetLink = {$this->ATTRIBUTES['as_id']}
//				AND tr_id = {$listSettings['JoinTablePrefix']}TransactionLink
//				AND tr_currency_link = cn_id
//	  			
//				$trCompleted
//				$whereSQL	
//				$searchKeywordSQL		
//				$shippingDateSQL
//				
//			ORDER BY {$this->ATTRIBUTES['OrderBy']} {$this->ATTRIBUTES['OrderByType']}
//			LIMIT $startRow, $maxRows <br>";

	$listSQL = "";

	// special case speedup to make flicking through recent orders less painful
	if( (($this->ATTRIBUTES['OrderBy'] == 'tr_timestamp') || ($this->ATTRIBUTES['OrderBy'] == 'tr_id')) 
		&& ($this->ATTRIBUTES['OrderByType'] == 'DESC')
		&& (trim($whereSQL) == 'AND or_archive_year IS NULL')
		&& (strlen($searchKeywordSQL) == 0)
		&& (strlen($shippingDateSQL) == 0 )
		&& (trim($trCompleted) == 'AND tr_completed > 0')
		&& ($startRow == 0) )
	{
		$maxOrID = GetField( "select max(or_id) from shopsystem_orders" );
		$minOrID = $maxOrID - $startRow - $maxRows - 500;

		$listSQL = 	"
			SELECT * 
			FROM {$listSettings['JoinTable']} 
				JOIN transactions ON tr_id = {$listSettings['JoinTablePrefix']}_tr_id 
				JOIN countries ON tr_currency_link = cn_id
				LEFT JOIN payment_gateways on tr_bank = pg_id
				LEFT JOIN bank_transfer_information on tr_id = bt_tr_id
			WHERE {$listSettings['JoinTablePrefix']}_as_id = {$this->ATTRIBUTES['as_id']}
				$trCompleted
				$searchKeywordSQL		
				$shippingDateSQL
				AND or_id > $minOrID
			ORDER BY {$this->ATTRIBUTES['OrderBy']} {$this->ATTRIBUTES['OrderByType']}
			LIMIT $startRow, $maxRows
		";
		$Q_List = query( $listSQL );
		$totalRows = 9999;
	}
	else
	{
		$listSQL = 	"
			SELECT * 
			FROM {$listSettings['JoinTable']} 
				JOIN transactions ON tr_id = {$listSettings['JoinTablePrefix']}_tr_id 
				JOIN countries ON tr_currency_link = cn_id
				LEFT JOIN payment_gateways on tr_bank = pg_id
				LEFT JOIN bank_transfer_information on tr_id = bt_tr_id
			WHERE {$listSettings['JoinTablePrefix']}_as_id = {$this->ATTRIBUTES['as_id']}
				$trCompleted
				$whereSQL	
				$searchKeywordSQL		
				$shippingDateSQL
				
			ORDER BY {$this->ATTRIBUTES['OrderBy']} {$this->ATTRIBUTES['OrderByType']}
			LIMIT $startRow, $maxRows
		";

		$Q_List = query( $listSQL );
		$totalRows = 9999;
/*		$totalRows = $Q_List->numRows();	*/
	}

	// display a page thru
//	$totalRows = $Q_CountList['Total'];
	$pageThru = new Request('PageThru.Display',array(
		'ItemCount'		=>	$totalRows,	
		'ItemsPerPage'	=>	$this->ATTRIBUTES['RowsPerPage'],
		'CurrentPage'	=>	$this->ATTRIBUTES['CurrentPage'],
		'PagesPerBlock'	=>	$this->ATTRIBUTES['PagesPerBlock'],
		'URL'			=>	$backURL,
	));

	function FindAndReplace($str, $source) 
    {
		$str = urldecode($str);
		
		foreach($source as $key =>$value) 
        {						
			$str = str_replace("[$key]",$value,$str);		
		}
		//die($str);
		
		return $str;
	}
?>
