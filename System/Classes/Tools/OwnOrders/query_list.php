<?php 
	$this->param("BreadCrumb",''); // root breadcrumbs
	$this->param("as_id"); // To make a goup between AssetLink	


	if( !array_key_exists( 'User', $_SESSION ) )
	{
		echo "Log in first";
		die;
	}

	if( !array_key_exists( 'us_id', $_SESSION['User'] ) )
	{
		echo "Log in first";
		die;
	}

	// Default some values
//	ss_DumpVar( $this->ATTRIBUTES );
//	ss_DumpVar( $_GET);
	$this->param('RowsPerPage','10');
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','10');
	$this->param('BreadCrumbs','');
	$this->param('SearchKeyword','');
	$this->param('ShippingDateKeyword','');
	$this->param('OrderBy','tr_id');
	$this->param('OrderByType','DESC');
	$this->param('FilterBy','');
	$this->param('VendorFilterBy','');
	$this->param('SiteFolder','');

	$backURL = getBackURL();//$_SESSION['BackStack']->getURL();
	global $cfg;
	//$cfg['currentServer'] 
	$shortBackURL = str_replace($cfg['currentServer'],'',$backURL);		
				
	$Q_Asset = getRow("SELECT * FROM assets WHERE as_id = {$this->ATTRIBUTES['as_id']}");

/*
	$result = new Request("{$Q_Asset['as_type']}.OwnListSettings", array(
		'as_id'	=>	$this->ATTRIBUTES['as_id'],
	));	
	$listSettings = $result->value;
*/

	$options = array();
	$options['View Order']	=	array(					
				'URL'	=>	"javascript:window.open('index.php?act=ShopSystem.ViewOrder".ss_URLEncodedFormat("&or_id=[or_id]&tr_id=[tr_id]&as_id=[or_as_id]")."&BreadCrumbs=[BreadCrumbs]','_blank', 'height=480,width=580,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",				
			);

	$listSettings = array(
			'JoinTable'			=> 'shopsystem_orders',
			'JoinTablePrefix'	=> 'or',		
			'BreadCrumb'		=> 'Orders',	
			'OrderBy'			=>	array(				
										array('name' => 'Paid', 'field' => 'or_paid'),
										array('name' => 'Shipped', 'field' => 'or_shipped'),
										array('name' => 'Insured and Traced', 'field' => 'or_tracked_and_traced'),
									),	
			'FilterBy'			=>	array(
										array('name' => 'Paid', 'filter' => 'or_paid IS NOT NULL'),
										array('name' => 'Not Paid', 'filter' => 'or_paid IS NULL'),
										array('name' => 'Shipped', 'filter' => 'or_shipped IS NOT NULL'),
										array('name' => 'Not Shipped', 'filter' => 'or_shipped IS NULL'),
								),
			'Options'	=>	$options,
			'MultiSiteFilter'	=>	'or_site_folder',
		);

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
	$searchKeyword = $this->ATTRIBUTES['SearchKeyword'];
	//ss_paramKey($listSettings, 'SearchFields',array());
	if (strlen($searchKeyword) > 0) 
    {
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
		$whereSQL = "AND ".escape($this->ATTRIBUTES['FilterBy']);	

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
		$whereSQL = "AND ".$altered;

	}
	
	if (strlen($this->ATTRIBUTES['SiteFolder'])) 
    {
		if (array_key_exists('MultiSiteFilter', $listSettings)) 
        {
			$whereSQL = "AND {$listSettings['MultiSiteFilter']} LIKE '{$this->ATTRIBUTES['SiteFolder']}'";	
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
	
	$trCompleted = 'AND tr_completed = 1';
	if (!$listSettings['tr_completed']) 
    {
		$trCompleted = '';	
	}
//AND ((tr_status_link > 1) or ((tr_payment_method = 'WebPay_CreditCard_ZipZap' OR tr_payment_method = 'WebPay_CreditCard_DPS') AND (tr_status_link = 2)))	

//	echo "SELECT * 
//			FROM {$listSettings['JoinTable']}, transactions, countries
//			WHERE {$listSettings['JoinTablePrefix']}AssetLink = {$this->ATTRIBUTES['as_id']}
//				AND tr_id = {$listSettings['JoinTablePrefix']}_tr_id
//				AND tr_currency_link = cn_id
//	  			
//				$trCompleted
//				$whereSQL	
//				$searchKeywordSQL		
//				$shippingDateSQL
//				
//			ORDER BY {$this->ATTRIBUTES['OrderBy']} {$this->ATTRIBUTES['OrderByType']}
//			LIMIT $startRow, $maxRows <br>";

	$Q_List = query("
			SELECT * 
			FROM {$listSettings['JoinTable']}, transactions, countries
			WHERE {$listSettings['JoinTablePrefix']}AssetLink = {$this->ATTRIBUTES['as_id']}
				$trCompleted
				AND or_us_id = {$_SESSION['User']['us_id']}
				AND tr_id = {$listSettings['JoinTablePrefix']}_tr_id
				AND tr_currency_link = cn_id
				$whereSQL	
				$searchKeywordSQL		
				$shippingDateSQL
				
			ORDER BY {$this->ATTRIBUTES['OrderBy']} {$this->ATTRIBUTES['OrderByType']}
			LIMIT $startRow, $maxRows
		
	");
	
	$Q_CountList = getRow("
			SELECT Count(tr_id) AS Total
			FROM {$listSettings['JoinTable']}, transactions, countries
			WHERE {$listSettings['JoinTablePrefix']}AssetLink = {$this->ATTRIBUTES['as_id']}
				$trCompleted
				AND or_us_id = {$_SESSION['User']['us_id']}
				AND tr_id = {$listSettings['JoinTablePrefix']}_tr_id
				AND tr_currency_link = cn_id
				$whereSQL	
				$searchKeywordSQL		
				$shippingDateSQL
	");
	
	// display a page thru
	$totalRows = $Q_CountList['Total'];
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
