<?php

	$_SESSION['Shop']['LastSearch'] = getBackURL();
	$show_categories = false;
	$categoryMask = "join site_category_mask on scm_ca_id = ca_id and scm_lg_id = {$GLOBALS['cfg']['currentLanguage']} and scm_ca_active = 1";

	$this->param('pr_ve_id','');
	$this->param('pr_ca_id','');
	$this->param('ShowProductsInSubCategories','');
	$this->param('pr_qoc_id','');
	$this->param('Keywords','');

	$rowsPerPage = "10";
	if (strlen(ss_optionExists('Shop Engine Products Per Page'))) {
		$rowsPerPage = ss_optionExists('Shop Engine Products Per Page');
	}

	if( array_key_exists( "MainLayout", $this->ATTRIBUTES ) && ($this->ATTRIBUTES["MainLayout"] == 'none' ) )
		$this->param('RowsPerPage',999999);
	else
		$this->param('RowsPerPage',$rowsPerPage);
	$this->param('CurrentPage','1');
	$this->param('PagesPerBlock','10');

	$this->param('Tag','');
	$this->param('Outlet','');
	$this->param('Offers','');
	$this->param('DiscountCodes','');
	$this->param('Gateway','');
	$this->param('Specials','');
	$this->param('Heading','');
	$this->param('NotSpecials','');
    $this->param('Members','');
	$this->param('Featured','');
	$this->param('VIP','');
	$this->param('Loyalty','');
	$this->param('NoHusk', '0');

	$this->param('GroupBy','Category');
	$this->param('OrderBy','Default');
	$this->param('SameName','');
	$this->param('SameFormat','');
	//$this->param('OrderBy','');

	$this->param('Template','Engine');

	// Default to the engine template
	$template = $this->ATTRIBUTES['Template'];
	$pricesType = 'TableHTML';

	if( $this->ATTRIBUTES['NoHusk'] > 0 )
		$asset->display->layout = 'none';

	// For displaying a select list later 
	$result = new Request("Security.Sudo",array('Action'=>'Start'));
	$allCategoriesResult = new Request("shopsystem_categories.QueryAll",array('as_id'	=>	$asset->getID()));
	$Q_Categories = $allCategoriesResult->value;
	//ss_DumpVarDie($Q_Categories);

	// check for silly values

	if( !(is_numeric($this->ATTRIBUTES['pr_ca_id'] ) || $this->ATTRIBUTES['pr_ca_id'] == '' )
	 || !(is_numeric($this->ATTRIBUTES['Heading'] ) || $this->ATTRIBUTES['Heading'] == '' )
	 || !(is_numeric($this->ATTRIBUTES['pr_ve_id'] ) || $this->ATTRIBUTES['pr_ve_id'] == '' )
	 || !(is_numeric($this->ATTRIBUTES['pr_qoc_id'] ) || $this->ATTRIBUTES['pr_qoc_id'] == '' )
	 || !(is_numeric($this->ATTRIBUTES['RowsPerPage'] ) || $this->ATTRIBUTES['RowsPerPage'] == '' )
	 || !(is_numeric($this->ATTRIBUTES['CurrentPage'] ) || $this->ATTRIBUTES['CurrentPage'] == '' )
			)
	{
		header( 'Location: index.php' );
		die;
	}

	$offlineSQL = "pr_offline IS NULL and pr_is_service = 'false'";

	// Figure out what categories to search 
	if (strlen($this->ATTRIBUTES['Heading']))
	{
		$categoriesSQL = "ca_id IN (select pd_ca_id from product_dropdown where pd_ph_id = ".((int)$this->ATTRIBUTES['Heading']).")";
	}
	else
	{
		// Figure out what categories to search 
		if (strlen($this->ATTRIBUTES['pr_ca_id']))
		{
			if ($this->ATTRIBUTES['pr_ca_id'] == '..') die('Invalid pr_ca_id');
			$subCategoriesResult = new Request("shopsystem_categories.QueryAll",array(
				'as_id'		=>	$asset->getID(),
				'ca_id'			=>	$this->ATTRIBUTES['pr_ca_id'],
			));
/*			print_r( $subCategoriesResult ); die;	*/
			$categoriesSQL = "ca_id IN (".$subCategoriesResult->value->columnValuesList('ca_id',',','').")";
			if( ($subCategoriesResult->value->numRows() > 1) && ($this->ATTRIBUTES['ShowProductsInSubCategories'] == '' ) )
			{
				foreach( $subCategoriesResult->value->rows as $sub )
				$show_categories = true;
			}
		}
		else
			$categoriesSQL = "1=1";
	}

	// Figure out what quick order categories to display
	if (strlen($this->ATTRIBUTES['pr_qoc_id'])) {
//		unset( $_SESSION['doneUpsell'] );
		$quickOrderCategoriesSQL = "pr_qoc_id IN (".$this->ATTRIBUTES['pr_qoc_id'].")";
		$instockSQL = "pro_stock_available > if( pro_typical_daily_sales IS NULL, 0, pro_typical_daily_sales*3)";
		$this->ATTRIBUTES['GroupBy'] = 'QuickOrderCategory';
		$template = 'QuickOrderList';
		$pricesType = 'SmallHTML';
		if (!array_key_exists('NoOverrideRows',$this->ATTRIBUTES)) {
			$this->ATTRIBUTES['RowsPerPage'] = 99999;
		}
		$asset->display->layout = 'none';
	} else {
		$quickOrderCategoriesSQL = "1=1";
		$instockSQL = "1=1";
	}

	if (array_key_exists('PricesType',$this->ATTRIBUTES)) {
		$pricesType = $this->ATTRIBUTES['PricesType'];	
	}

	// Generate some SQL for searching the keywords 
	$keywordsSQL = '1=1';
	if (strlen($this->ATTRIBUTES['Keywords'])) {
		$newKeywords = ListToArray(ss_fixKeywords($this->ATTRIBUTES['Keywords'])," ");
		$keywordsSQL = "(1=1";		
		foreach($newKeywords as $keyword) {
			$keywordsSQL .= " AND (1=0";
			foreach (array('pr_name','pr_keywords','pr_short','pr_long') as $field) {
				$keywordsSQL .= " OR $field LIKE '%".escape($keyword)."%'";	
			}	
			$keywordsSQL .= ")";
		}
		$keywordsSQL .= ")";
	}

	// Generate some SQL for searching the keywords 
	$branchSearchSQL = '1=1';
	if ( IsSet( $this->ATTRIBUTES['branchSearch'] ) && strlen($this->ATTRIBUTES['branchSearch'])) {
		$newBranch = ListToArray(ss_fixKeywords($this->ATTRIBUTES['branchSearch'])," ");
		$branchSearchSQL = "(1=1";		
		foreach($newBranch as $branch) {
			$branchSearchSQL .= " AND ( pr_long like '%Branch:</span> ".escape($branch)."%')";
		}
		$branchSearchSQL .= ")";
	}

	if( ss_AuthdCustomer( ) )
		$zonefield = 'pr_authd_sales_zone';
	else
		$zonefield = 'pr_sales_zone';

	if( strlen($_SESSION['ForceCountry']['cn_sales_zones']) )
	{
		$external = "and $zonefield in (".$_SESSION['ForceCountry']['cn_sales_zones'].")";
		$externalSpecials = "and pr_specials_sales_zone in (".$_SESSION['ForceCountry']['cn_sales_zones'].")";
	}
	else
	{
		$external = '';
		$externalSpecials = '';
	}

	$discountSQL = '1=1';
	if (strlen($this->ATTRIBUTES['DiscountCodes']))
	{
		$dc = escape( $this->ATTRIBUTES['DiscountCodes'] );
		$discountSQL = "pr_dig_id in (select di_discount_group from discounts where di_active = 'true' and di_starting <= now() and di_ending >= now() and di_code = '$dc' and (di_left > 0 or di_left IS NULL) )";

	}

	// Generate some SQL for finding specials only products
	$specialsSQL = '1=1';
	$tagSQL = '1=1';
	if (strlen($this->ATTRIBUTES['Specials'])) {
		$sql = "SELECT DISTINCT pr_id FROM shopsystem_products, shopsystem_product_extended_options
			WHERE pr_id = pro_pr_id AND pr_deleted IS NULL
				AND pro_special_price IS NOT NULL
				$external $externalSpecials";

		if (strlen($this->ATTRIBUTES['pr_ve_id']))
			$sql .= ' AND pr_ve_id = '.safe($this->ATTRIBUTES['pr_ve_id']);	

		$sql .= " ORDER BY pr_combo, (pro_stock_available > if( pro_typical_daily_sales IS NULL, 0, pro_typical_daily_sales*3)) DESC, pro_price DESC, pr_name";

		$Q_Specials = query($sql);
		if ($Q_Specials->numRows()) {
			ss_log_message( "products on special ".$Q_Specials->columnValuesList('pr_id',',','') );
			$specialsSQL = "pr_id IN (".$Q_Specials->columnValuesList('pr_id',',','').")";
		} else {
			$specialsSQL = '1=0';	
		}
		$Q_Specials->free();
		$instockSQL = "pro_stock_available > if( pro_typical_daily_sales IS NULL, 0, pro_typical_daily_sales*3)";
	}

	if (strlen($this->ATTRIBUTES['Outlet']))
		$specialsSQL .= ' AND pr_outlet = 1';

	if (strlen($this->ATTRIBUTES['Tag']))
	{
		$sanit =  (int)( $this->ATTRIBUTES['Tag'] );
		$tagSQL = "pr_id in (select pa_pr_id from product_tags join tags on ta_id = pa_ta_id where ta_id = $sanit)";
	}

	$latjoin = '';

	if( strlen($this->ATTRIBUTES['SameName'] ) )
	{
		$template = 'QuickOrderList';
		$pricesType = 'SmallHTML';

		$pr_id = (int) $this->ATTRIBUTES['SameName'];
		$product = GetRow( "select pr_name, pr_combo, pr_type from shopsystem_products where pr_id = $pr_id" );
		if( $product['pr_combo'] )
		{
			// each product in turn (OR'd together)
			if( $Qp = query( "select * from shopsystem_combo_products join shopsystem_products on cpr_pr_id = pr_id where cpr_element_pr_id = $pr_id" ) )
			{
				$cl = array();
				while( $crow = $Qp->fetchRow() )
				{
					$name = $crow['pr_name'];
					if( $pos = strrpos( $name, '(' ) )
						$name = substr( $name, 0, $pos );

					$name = escape( trim( $name ) );
					$cl[] = "(pr_name LIKE '%$name%')";	
				}
				if( count($cl) )
					$keywordsSQL .= " and pr_id != $pr_id and (".implode(' OR ', $cl ).' )';
				else
					$keywordsSQL .= ' and false ';
			}
			else
				$keywordsSQL .= ' and false ';

		}
		else
		{
			$name = $product['pr_name'];
			if( $pos = strrpos( $name, '(' ) )
				$name = substr( $name, 0, $pos );

			$name = escape( trim( $name ) );
			$keywordsSQL .= " and pr_name LIKE '%$name%' and pr_id != $pr_id";	
		}
		$keywordsSQL .= " and pro_stock_available > 0 ";
	}

	if( strlen($this->ATTRIBUTES['SameFormat'] ) )
	{
		$template = 'QuickOrderList';
		$pricesType = 'SmallHTML';

		$pr_id = (int) $this->ATTRIBUTES['SameFormat'];
		$product = GetRow( "select pr_combo, pr_type from shopsystem_products where pr_id = $pr_id" );
		if( $product['pr_combo'] )
		{
			// each product in turn (OR'd together)
			if( $Qp = query( "select * from shopsystem_combo_products join shopsystem_products on cpr_pr_id = pr_id where cpr_element_pr_id = $pr_id" ) )
			{
				$cl = array();
				while( $crow = $Qp->fetchRow() )
				{
					$type = (int)$crow['pr_type'];
					$cl[] = "(pr_type = $type)";	
				}
				if( count($cl) )
					$keywordsSQL .= " and pr_id != $pr_id and (".implode(' OR ', $cl ).' )';
				else
					$keywordsSQL .= ' and false ';
			}
			else
				$keywordsSQL .= ' and false ';

		}
		else
		{
			$type = (int)$product['pr_type'];
			$keywordsSQL .= " and pr_type = $type and pr_id != $pr_id";	
		}
		$keywordsSQL .= " and pro_stock_available > 0 ";
	}

	if ($this->ATTRIBUTES['OrderBy'] == 'Wishlist' )
	{
		$template = 'WishList';
		$pricesType = 'SmallHTML';
		if (!array_key_exists('NoOverrideRows',$this->ATTRIBUTES)) {
			$this->ATTRIBUTES['RowsPerPage'] = 99999;
		}
		$asset->display->layout = 'none';
		$latjoin = "join shopsystem_stock_notifications on stn_stock_code = pro_stock_code and stn_us_id = ".((int)ss_getUserID());
		$instockSQL = "1=1";
	}

	if ($this->ATTRIBUTES['OrderBy'] == 'Updates' )
	{
		if( $this->ATTRIBUTES['NoHusk'] == 0 )
			$latjoin = "left join lastest_product_additions on la_pr_id = pr_id";
		else
		{
			$template = 'QuickOrderList';
			$pricesType = 'SmallHTML';

			if (!array_key_exists('NoOverrideRows',$this->ATTRIBUTES))
				$this->ATTRIBUTES['RowsPerPage'] = 99999;

			$latjoin = "join lastest_product_additions on la_pr_id = pr_id";
		}

		$instockSQL = "pro_stock_available > if( pro_typical_daily_sales IS NULL, 0, pro_typical_daily_sales*3)";
	}

	if (strlen($this->ATTRIBUTES['Gateway']))
	{
		$gw = ((int) $this->ATTRIBUTES['Gateway']);
		$specialsSQL .= " AND (pr_restrict_special_to_gateway = $gw OR pr_restrict_product_to_gateway = $gw)";
	}

	$catjoin = "join shopsystem_categories on (pr_ca_id = ca_id or pr_sub_ca_id = ca_id)";
	if( is_numeric($this->ATTRIBUTES['pr_qoc_id'] ) )
		$catjoin = "join shopsystem_categories on pr_ca_id = ca_id";

	if (strlen($this->ATTRIBUTES['Specials']))
		$catjoin = "join shopsystem_categories on pr_ca_id = ca_id join product_dropdown on pd_ca_id = ca_id join product_heading on pd_ph_id = ph_id";

	if (strlen($this->ATTRIBUTES['Offers']))
	{
		$vendorList = array();

		for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
		{
			$entry = $_SESSION['Shop']['Basket']['Products'][$index];

			if ($entry['Qty'] > 0)
				if( array_key_exists( 'pr_ve_id', $entry['Product'] ) )
					if( !in_array( $entry['Product']['pr_ve_id'], $vendorList ) )
						$vendorList[] = $entry['Product']['pr_ve_id'];
		}

		$vendors = implode(', ', $vendorList );

		$categoryMask = "";
		$catjoin = "join shopsystem_categories on (pr_ca_id = ca_id or pr_sub_ca_id = ca_id)";
		$specialsSQL .= " AND pr_upsell = 1 and pro_stock_available > 0";
		if( count( $vendorList ) )
			$specialsSQL .= " and pr_ve_id in ($vendors)";
		$offlineSQL = '1=1';
		$template = 'Offers';
		$asset->display->layout = 'offers';
//		$_SESSION['doneUpsell'] = true;
	}

	// Generate some SQL for finding specials only products
	$notSpecialsSQL = '';
	if (strlen($this->ATTRIBUTES['NotSpecials'])) {
		$Q_NotSpecials = query("
				SELECT DISTINCT pr_id FROM shopsystem_products, shopsystem_product_extended_options
				WHERE pr_id = pro_pr_id AND pr_deleted IS NULL
					AND pro_special_price IS NULL
			");	
		if ($Q_NotSpecials->numRows()) {
			$notSpecialsSQL = "AND pr_id IN (".$Q_NotSpecials->columnValuesList('pr_id',',','').")";
		} else {
			$notSpecialsSQL = 'AND 1=0';	
		}
		$Q_NotSpecials->free();
	}


	// Featured products SQL
	$featuredSQL = '1=1';
	if (strlen($this->ATTRIBUTES['Featured'])) {
		$f = (int) $this->ATTRIBUTES['Featured'];
		if (strlen($this->ATTRIBUTES['Specials']))
			$featuredSQL = "pr_featured != 'no' ";	
		else
			$featuredSQL = "pr_featured = $f and pro_special_price IS NULL ";	
	}


	// pr0_883_f is the number of items in the box....

	$externalSQL = '';
	if( strlen( $_SESSION['ForceCountry']['cn_sales_zones'] ) )
		$externalSQL = " AND $zonefield in ({$_SESSION['ForceCountry']['cn_sales_zones']})";	

	if( $_SESSION['ForceCountry']['cn_generic_limit'] > 0 )
		$externalSQL .= " and pr_id in (select pro_pr_id from shopsystem_product_extended_options where pro_weight  > 0 and pro_weight <= ".(int)$_SESSION['ForceCountry']['cn_generic_limit'].")";

	// Featured products SQL
	if (strlen($this->ATTRIBUTES['pr_ve_id']))
		$externalSQL .= ' and pr_ve_id = '.safe($this->ATTRIBUTES['pr_ve_id']);	

	$loyaltySQL = '1=1';
	if (ss_optionExists('Shop Acme Rockets') and strlen($this->ATTRIBUTES['Loyalty'])) {
		$loyaltySQL = 'pr_points = 1';
	}

	// Group products by category 
	$orderBySQL = 'ORDER BY 1=1';
	if (strlen($this->ATTRIBUTES['Specials'])) {
		$orderBySQL .= ', pr_combo, ph_sort, pro_stock_available desc';
	}
	if ($this->ATTRIBUTES['GroupBy'] == 'Category') {
		$cats = '';
		if (strlen($this->ATTRIBUTES['pr_ca_id'])) {
			$catsArray = $subCategoriesResult->value->columnValuesArray('ca_id');
//			unset( $_SESSION['doneUpsell'] );
		} else {
			$catsArray = $allCategoriesResult->value->columnValuesArray('ca_id');
		}
		foreach($catsArray as $caID) {
			$cats .= ",ca_id!=$caID";
		}
		$orderBySQL .= $cats;
	} else if ($this->ATTRIBUTES['GroupBy'] == 'QuickOrderCategory') {
		if (ListLen($this->ATTRIBUTES['pr_qoc_id']) > 1) {
			foreach (ArrayToList($this->ATTRIBUTES['PrQuickOrderCategory']) as $quickOrderCategory) {
				$orderBySQL .= ',ca_id!='.safe($quickOrderCategory);
			}
		} else {
			$orderBySQL .= ',ca_id!='.safe($this->ATTRIBUTES['pr_qoc_id']);
		}
	}

	// Order products by...

//	ss_DumpVarDie( debug_backtrace() );
	switch ($this->ATTRIBUTES['OrderBy']) {
		case 'Default'	:	
			$orderBySQL .= ",pr_sort_order,pr_name";
			break;
		case 'ProductName' :
			$orderBySQL .= ",pr_name";
			break;
		case 'Updates' :
			$orderBySQL .= ", la_id desc";
			break;
		case 'Price' :
			$orderBySQL .= ", pro_price";
			break;
		case 'BoxSize' :
			$orderBySQL .= ",pr0_883_f";
			break;
		case 'Random' :
			$orderBySQL = "ORDER BY Rand(Now())";
			break;
		case 'Category' :
			$orderBySQL = "ORDER BY pr_ca_id";
			break;
		case 'Avail.Price' :
			//$orderBySQL = "ORDER BY (pro_stock_available > if( pro_typical_daily_sales IS NULL, 0, pro_typical_daily_sales*3)) DESC, pro_price DESC, pr_name";
			$orderBySQL = "ORDER BY (pro_stock_available > 0) DESC, pro_price DESC, pr_name";
	}

	$vipSQL = '';
	$vipGroup = ss_optionExists('Shop VIP Products');
	if ($vipGroup !== false) {
		if (array_key_exists($vipGroup,$_SESSION['User']['user_groups'])) {
			// No need to remove VIP products
			if (strlen($this->ATTRIBUTES['VIP'])) {
				// searching for VIP products only
				$vipSQL = 'AND pr_vip IS NOT NULL';	
			}
		} else {
			// Remove VIP products
			$vipSQL = 'AND pr_vip IS NULL';
		}
	}

//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop'] );
	$countryHideSQL = '1=1';
	if (ss_optionExists("Shop Products Block Individual countries")) {
		$countryHideSQL = "(PrExcludeCountries NOT LIKE '%{$_SESSION['Shop']['MultiCurrencyCountryDef']['CountryCode']}%' OR PrExcludeCountries IS NULL)";
	}

	$restrictedSQL = ss_optionExists('Restricted Shop Products') ? 'PrRestricted IS NULL' : '1=1';

	// Search for the products 
	$startRow = ($this->ATTRIBUTES['CurrentPage']-1)*$this->ATTRIBUTES['RowsPerPage'];
	$maxRows = $this->ATTRIBUTES['RowsPerPage'];

	if( $show_categories )
	{
		$query = "
			SELECT DISTINCT * FROM shopsystem_categories
			WHERE $categoriesSQL
			    and ca_parent_ca_id IS NOT NULL
				".ss_shopRestrictedCategoriesSQL(strlen($this->ATTRIBUTES['Offers']))."	
			LIMIT $startRow,$maxRows
		";
		$tagQuery = "select NULL";
	}
	else
		if( array_key_exists( 'cfg', $GLOBALS )
		 && array_key_exists( 'currentLanguage', $GLOBALS['cfg'] ) )
		{
		 	if( $GLOBALS['cfg']['currentLanguage'] > 0 )
			{
				$query = "
					SELECT * FROM shopsystem_products
					 left join product_type on pr_type = pt_id
					 left join shopsystem_product_descriptions on prd_pr_id = pr_id AND prd_language = ".$GLOBALS['cfg']['currentLanguage']."
					 $catjoin
					 $categoryMask
					 join shopsystem_product_extended_options on pr_id = pro_pr_id
					 $latjoin
					WHERE pr_deleted IS NULL
						AND $offlineSQL
						AND $restrictedSQL
						AND $categoriesSQL
						AND $keywordsSQL
						AND $branchSearchSQL
						AND $specialsSQL
						AND $tagSQL
						AND $discountSQL
						AND $featuredSQL
						$externalSQL
						AND $quickOrderCategoriesSQL
						AND $instockSQL
						AND $countryHideSQL
						AND $loyaltySQL
						$notSpecialsSQL
						".ss_shopRestrictedCategoriesSQL(strlen($this->ATTRIBUTES['Offers']))."	
						$vipSQL
					group by pr_id
					$orderBySQL
					LIMIT $startRow,$maxRows
				";
				$tagQuery = "
					SELECT distinct ta_text as tag FROM tags join product_tags on pa_ta_id = ta_id
						join shopsystem_products on pa_pr_id = pr_id
						 left join product_type on pr_type = pt_id
						 left join shopsystem_product_descriptions on prd_pr_id = pr_id AND prd_language = ".$GLOBALS['cfg']['currentLanguage']."
					 $catjoin
					 $categoryMask
					 join shopsystem_product_extended_options on pr_id = pro_pr_id
					 $latjoin
					WHERE pr_deleted IS NULL
						AND $offlineSQL
						AND $restrictedSQL
						AND $categoriesSQL
						AND $keywordsSQL
						AND $branchSearchSQL
						AND $specialsSQL
						AND $discountSQL
						AND $featuredSQL
						$externalSQL
						AND $quickOrderCategoriesSQL
						AND $instockSQL
						AND $countryHideSQL
						AND $loyaltySQL
						$notSpecialsSQL
						".ss_shopRestrictedCategoriesSQL(strlen($this->ATTRIBUTES['Offers']))."	
						$vipSQL
					ORDER BY tag
					LIMIT 32
				";
			}
			else
			{
				$query = "
					SELECT * FROM shopsystem_products
					 left join product_type on pr_type = pt_id
					 $catjoin
					 $categoryMask
					 join shopsystem_product_extended_options on pr_id = pro_pr_id
					 $latjoin
					WHERE pr_deleted IS NULL
						AND $offlineSQL
						AND $restrictedSQL
						AND $categoriesSQL
						AND $keywordsSQL
						AND $branchSearchSQL
						AND $specialsSQL
						AND $tagSQL
						AND $discountSQL
						AND $featuredSQL
						$externalSQL
						AND $quickOrderCategoriesSQL
						AND $instockSQL
						AND $countryHideSQL
						AND $loyaltySQL
						$notSpecialsSQL
						".ss_shopRestrictedCategoriesSQL(strlen($this->ATTRIBUTES['Offers']))."	
						$vipSQL
					group by pr_id
					$orderBySQL
					LIMIT $startRow,$maxRows
				";
				$tagQuery = "
					SELECT distinct ta_text as tag, ta_id FROM tags join product_tags on pa_ta_id = ta_id
						join shopsystem_products on pa_pr_id = pr_id
					 left join product_type on pr_type = pt_id
					 $catjoin
					 $categoryMask
					 join shopsystem_product_extended_options on pr_id = pro_pr_id
					 $latjoin
					WHERE pr_deleted IS NULL
						AND $offlineSQL
						AND $restrictedSQL
						AND $categoriesSQL
						AND $keywordsSQL
						AND $branchSearchSQL
						AND $specialsSQL
						AND $discountSQL
						AND $featuredSQL
						$externalSQL
						AND $quickOrderCategoriesSQL
						AND $instockSQL
						AND $countryHideSQL
						AND $loyaltySQL
						$notSpecialsSQL
						".ss_shopRestrictedCategoriesSQL(strlen($this->ATTRIBUTES['Offers']))."	
						$vipSQL
					ORDER BY tag
					LIMIT 32
				";
			}
		}

	ss_log_message( $query );
	$Q_Products = query($query);
	$Q_Taglist = query($tagQuery);

	if( $show_categories )
	// Count how many products in total for this search query 
		$query = "
			SELECT COUNT(DISTINCT ca_id) AS Total FROM shopsystem_categories
			$categoryMask
			WHERE $categoriesSQL
			    and ca_parent_ca_id IS NOT NULL
				".ss_shopRestrictedCategoriesSQL(strlen($this->ATTRIBUTES['Offers']));
	else
		$query = "
			SELECT COUNT(DISTINCT pr_id) AS Total FROM shopsystem_products
			 join shopsystem_product_extended_options on pr_id = pro_pr_id
			$catjoin
			$categoryMask
			WHERE pr_deleted IS NULL
				AND $offlineSQL
				AND $categoriesSQL
				AND $restrictedSQL
				AND $keywordsSQL
				AND $branchSearchSQL
				AND $specialsSQL
				AND $tagSQL
				AND $discountSQL
				AND $featuredSQL
				$externalSQL
				AND $quickOrderCategoriesSQL
				AND $countryHideSQL
				AND $loyaltySQL
				$notSpecialsSQL
				".ss_shopRestrictedCategoriesSQL(strlen($this->ATTRIBUTES['Offers']))."	
				$vipSQL
		";

	$productCount = getRow($query);

	//ss_log_message( "Returning {$productCount['Total']} products" );

	// Generate a page thru
	$backURL = $_SESSION['BackStack']->getURL();
	$pageThru = new Request('PageThru.Display',array(
		'ItemCount'		=>	$productCount['Total'],	
		'ItemsPerPage'	=>	$this->ATTRIBUTES['RowsPerPage'],
		'CurrentPage'	=>	$this->ATTRIBUTES['CurrentPage'],
		'PagesPerBlock'	=>	$this->ATTRIBUTES['PagesPerBlock'],
		'URL'			=>	$backURL."&NoStats=Yes",
	));

	// record the search keywords for stats.
//	if (!array_key_exists('NoStats', $this->ATTRIBUTES)) {
//		// based on search keywords
//		if (strlen($this->ATTRIBUTES['Keywords'])) {
//			// record the search
//			$Q_SearchStats = query("
//					INSERT INTO search_statistics 
//					(ss_timestamp, ss_keywords, ss_found, ss_ug_id,ss_type, ss_country) 
//					VALUES
//					(NOW(), '".escape($this->ATTRIBUTES['Keywords'])."', {$productCount['Total']}, '".ArrayKeysToList($_SESSION['User']['user_groups'])."', 'Shop', '".ss_getCountry(null,'cn_name')."')
//			");
//		} else {
//			// based on a category
//			$categoryBreadCrumbs = '';		
//			if (strlen($this->ATTRIBUTES['pr_ca_id'])) {					
//				$currentCategory = $this->ATTRIBUTES['pr_ca_id'];
//				while (strlen($currentCategory)) {
//					$category = getRow("
//						SELECT * FROM shopsystem_categories
//						WHERE ca_id = $currentCategory
//					");
//					$categoryBreadCrumbs = $category['ca_name'].(strlen($categoryBreadCrumbs)?' > ':'').$categoryBreadCrumbs;
//					//$categoryBreadCrumbs = $category['ca_name'];
//					$currentCategory = $category['ca_parent_ca_id'];
//				}			
//			} else {
//				$categoryBreadCrumbs = 'All Categories';
//			}
//			$Q_ViewCategoryStats = query("
//					INSERT INTO shopsystem_statistics 
//					(sst_timestamp,sst_ca_id, sst_country) 
//					VALUES
//					(NOW(), '".escape($categoryBreadCrumbs)."', '".escape(ss_getCountry(null,'cn_name'))."')
//			");			
//		}
//	}


	// Add some extra columns :-)
	$Q_Products->addColumn('NiceProductName');
	$Q_Products->addColumn('NiceCategoryName');
	$Q_Products->addColumn('ProductDetailLink');
	$Q_Products->addColumn('ProductPopupLink');
	$Q_Products->addColumn('Image');
	$Q_Products->addColumn('WideImage');
	$Q_Products->addColumn('FullImage');
	$Q_Products->addColumn('ActualPrice');
	$Q_Products->addColumn('PricesHTML');	
	$Q_Products->addColumn('PricesSmall');	
	$Q_Products->addColumn('OptionsHTML');
	$Q_Products->addColumn('AttributesHTML');
	if( $show_categories )
	{
	//	do the fake product thing...
        $Q_Products->addColumn('pr_id');
        $Q_Products->addColumn('pr_name');
        $Q_Products->addColumn('pr_short');
        $Q_Products->addColumn('pr_long');
        $Q_Products->addColumn('pr_image1_thumb');
        $Q_Products->addColumn('pr_image1_normal');
        $Q_Products->addColumn('pr_image1_large');
        $Q_Products->addColumn('pr_image2_normal');
        $Q_Products->addColumn('pr_image2_large');
        $Q_Products->addColumn('pr_image3_normal');
        $Q_Products->addColumn('pr_image3_large');
        $Q_Products->addColumn('pr_flash');
        $Q_Products->addColumn('pr_ca_id');
        $Q_Products->addColumn('pr_as_id');
        $Q_Products->addColumn('pr_sort_order');
        $Q_Products->addColumn('pr_keywords');
        $Q_Products->addColumn('pr_deleted');
        $Q_Products->addColumn('pr_dig_id');
        $Q_Products->addColumn('pr_featured');
        $Q_Products->addColumn('pr_donation');
        $Q_Products->addColumn('pr_data_collection_link');
        $Q_Products->addColumn('pr_window_title');
        $Q_Products->addColumn('Pr0_2368');
        $Q_Products->addColumn('pr_qoc_id');
        $Q_Products->addColumn('pr_vip');
        $Q_Products->addColumn('pr0_883_f');
        $Q_Products->addColumn('pr_combo');
        $Q_Products->addColumn('pr_offline');
        $Q_Products->addColumn('pr_customer_rating');
        $Q_Products->addColumn('pr_customer_rating_count');
        $Q_Products->addColumn('pr_points');
        $Q_Products->addColumn('pr_needs_extra_padding');
        $Q_Products->addColumn('pr_add_gift');
        $Q_Products->addColumn('pr_ve_id');
        $Q_Products->addColumn('OptionFieldsArray');
        $Q_Products->addColumn('ProductOptions');
        $Q_Products->addColumn('OptionFieldDefs');
        $Q_Products->addColumn('pro_id');

	}


	// Insert the data 
	$currentRow = 0;
	$assetPath = ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath()));
	$assetStore = ss_EscapeAssetPath(ss_storeForAsset($asset->getID()));

	// Get the thumbsize
	$thumbSize = "120x120";
	if (strlen(ss_optionExists('Shop Engine Image Size'))) {
		$thumbSize = ss_optionExists('Shop Engine Image Size');
	}

	// Check for discount codes
	$discountCodes = ss_optionExists('Shop Discount Codes');

//	ss_log_message_stack( "Foobar" );

	while ($row = $Q_Products->fetchRow()) {
	//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $row );
//		ss_log_message_stack( "Line $currentRow" );
		if( $show_categories )
		{
			$Q_Products->setCell('pr_ca_id',$row['ca_id'],$currentRow);
			$Q_Products->setCell('pr_name',$row['ca_name'],$currentRow);
//			$Q_Products->setCell('pr_short',$row['ca_name'],$currentRow);
			$Q_Products->setCell('pr_id',1-$row['ca_id'],$currentRow);
		}
		if (array_key_exists('ShowFirstProduct',$this->ATTRIBUTES)) {
			locationRelative($assetPath."/Service/Detail/Product/{$row['pr_id']}");
		}
		// This must be done at the start of this loop for every product
		$niceProductName = ss_alphaNumeric($row['pr_name'],'_');
		$niceCategoryName = ss_alphaNumeric($row['ca_name'],'_');
		$Q_Products->setCell('NiceProductName',$niceProductName,$currentRow);
		$Q_Products->setCell('NiceCategoryName',$niceCategoryName,$currentRow);
		if( $show_categories )
		{
			$Q_Products->setCell('ProductDetailLink',$assetPath."/Service/Engine/pr_ca_id/".$row['ca_id'], $currentRow);
			$Q_Products->setCell('ProductPopupLink',$assetPath."/Service/Engine/pr_ca_id/".$row['ca_id']."/NoHusk/1", $currentRow);
		}
		else
		{
			$Q_Products->setCell('ProductDetailLink',$assetPath."/Service/Detail/Product/{$row['pr_id']}/{$niceCategoryName}/{$niceProductName}.html",$currentRow);
			$Q_Products->setCell('ProductPopupLink',$assetPath."/Service/SingleProduct/Product/{$row['pr_id']}/{$niceCategoryName}/{$niceProductName}.html",$currentRow);
		}

		$image = null;	

		// Figure out the image
		if( $show_categories )
		{
			if (strlen($row['ca_image']))
				$image = $assetStore."/CategoryImages/".$row['ca_image'];
			$Q_Products->setCell('WideImage',$image,$currentRow);
			$Q_Products->setCell('FullImage',$image,$currentRow);
		}
		else
		{
			$wideImage = null;
			if( $row['pr_ve_id'] == 1 )
				$image_size = "MaxHeight=100&MaxWidth=180&";
//				$image_size = "";
			else
				$image_size = "MaxHeight=80&MaxWidth=180&";

			$image = "index.php?act=ImageManager.get&ProductV=".$row['pr_id'];
			$wideImage = "index.php?act=ImageManager.get&ProductThumb=".$row['pr_id'];
			$fullImage = "index.php?act=ImageManager.get&ProductFull=".$row['pr_id'];
/*
			if (strlen($row['pr_image1_thumb'])) {
				$image = $assetStore."/ProductImages/".$row['pr_image1_thumb'];
				$wideImage = "index.php?act=ImageManager.get&{$image_size}Image=".ss_URLEncodedFormat( $image );
			} elseif (strlen($row['pr_image1_normal'])) {
				$image = 'index.php?act=ImageManager.get&Size='.$thumbSize.'&Image='.ss_URLEncodedFormat($assetStore."/ProductImages/".$row['pr_image1_normal']);
				$wideImage = "index.php?act=ImageManager.get&{$image_size}Image=".ss_URLEncodedFormat($assetStore."/ProductImages/".$row['pr_image1_normal']);
			} elseif (strlen($row['pr_image1_large'])) {
				$image = 'index.php?act=ImageManager.get&Size='.$thumbSize.'&Image='.ss_URLEncodedFormat($assetStore."/ProductImages/".$row['pr_image1_large']);
				$wideImage = "index.php?act=ImageManager.get&{$image_size}Image=".ss_URLEncodedFormat($assetStore."/ProductImages/".$row['pr_image1_large']);
			} 
*/
			$Q_Products->setCell('WideImage',$wideImage,$currentRow);
			$Q_Products->setCell('FullImage',$fullImage,$currentRow);
		}
		$Q_Products->setCell('Image',$image,$currentRow);

		if (!$discountCodes) $row['pr_dig_id'] = null;

		// Figure out the prices table
		if( !$show_categories )
		{
			$pricesHTML = $this->getPrice($row['pr_id'],$row['pr_dig_id'],null,$pricesType);
			$Q_Products->setCell('PricesHTML',$pricesHTML,$currentRow);
			$Q_Products->setCell('PricesSmall',$this->getPrice($row['pr_id'],$row['pr_dig_id'],null,'SmallHTML'),$currentRow);
			$Q_Products->setCell('ActualPrice',$this->getPrice($row['pr_id'],$row['pr_dig_id'],null,'PriceOnly'),$currentRow);
		}
		else
		{
			$Q_Products->setCell('PricesHTML',"",$currentRow);
			$Q_Products->setCell('PricesSmall',"",$currentRow);
			$Q_Products->setCell('ActualPrice',"",$currentRow);
		}

		$currentRow++;
//		ss_log_message_r($row);
	}
//	ss_log_message_stack( "Barfoo" );

	$searchCategoryAll = null;

	$setWindowTitle = false;
	$windowTitle = "";

	$assetTitle = "";

	if (strlen($this->ATTRIBUTES['pr_ca_id'])) {

		if( array_key_exists( 'cfg', $GLOBALS )
		 && array_key_exists( 'currentLanguage', $GLOBALS['cfg'] )
		 && $GLOBALS['cfg']['currentLanguage'] > 0 )
			$row = getRow("SELECT * FROM shopsystem_categories left join shopsystem_category_descriptions on cad_ca_id = ca_id AND cad_language = ".$GLOBALS['cfg']['currentLanguage']." WHERE ca_id = ".(int)($this->ATTRIBUTES['pr_ca_id']));
		else
			$row = getRow("SELECT * FROM shopsystem_categories WHERE ca_id = ".(int)($this->ATTRIBUTES['pr_ca_id']));

		//$row = $Q_Categories->getRow($Q_Categories->getRowWithValue('ca_id',$this->ATTRIBUTES['pr_ca_id']));
		if( !is_array( $row ) )
			die;

		$searchCategory = $row['ca_name'];

		if (strlen($row['ca_window_title']))
		{
			$asset->layout['LYT_WINDOWTITLE'] = $row['ca_window_title'];
			$setWindowTitle = true;
		}

		if (strlen($row['ca_banner']))
			$asset->layout['LYT_BANNER'] = $row['ca_banner'];

		if (strlen($row['ca_metadata_keywords']))
		{
			$asset->display->keywords = $row['ca_metadata_keywords'];
		}

		if (strlen($row['ca_metadata_description']))
		{
			$asset->display->description = $row['ca_metadata_description'];
		}

		if ( array_key_exists( 'cad_window_title', $row) && strlen($row['cad_window_title']))
		{
			$asset->layout['LYT_WINDOWTITLE'] = $row['cad_window_title'];
			$setWindowTitle = true;
		}

		if (array_key_exists( 'cad_metadata_keywords', $row) && strlen($row['cad_metadata_keywords']))
		{
			$asset->display->keywords = $row['cad_metadata_keywords'];
		}

		if (array_key_exists( 'cad_metadata_description', $row) && strlen($row['cad_metadata_description']))
		{
			$asset->display->description = $row['cad_metadata_description'];
		}


//		ss_DumpVarDie( $asset );

		$searchCategoryAll = $row;
		$searchCategoryID = $this->ATTRIBUTES['pr_ca_id'];
	} else if (strlen($this->ATTRIBUTES['Featured'])) {
		$searchCategory = "Featured Products";
		$searchCategoryID = null;
	} else {
		if (strlen($this->ATTRIBUTES['Specials'])) {
			$searchCategory = "Products on Sale";			
		} else {
			if( $template == 'Offers' )
				$searchCategory = "Free shipping on these products";			
			else
				$searchCategory = "Search Results";			
		}
		$searchCategoryID = null;
	}

	// Set the window title, if we haven't already
	if (!$setWindowTitle) {
		ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_CATEGORY_WINDOW_TITLE_TEMPLATE','[SiteName] - [Category]');
		$windowTitle = $asset->cereal['AST_SHOPSYSTEM_CATEGORY_WINDOW_TITLE_TEMPLATE'];
		$windowTitle = stri_replace('[SiteName]',$GLOBALS['cfg']['website_name'],$windowTitle);
		$windowTitle = stri_replace('[Category]',$searchCategory,$windowTitle);
		$asset->layout['LYT_WINDOWTITLE'] = $windowTitle;
	}

	$asset->display->assetLayoutSettings = $asset->layout;

	// Load up the details of options	
	ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_PRODUCT_OPTIONS','');
	if (strlen($asset->cereal['AST_SHOPSYSTEM_PRODUCT_OPTIONS'])) {
		$optionFieldsArray = unserialize($asset->cereal['AST_SHOPSYSTEM_PRODUCT_OPTIONS']);
	} else {
		$optionFieldsArray = array();	
	}		
	$result = new Request("Security.Sudo",array('Action'=>'Finish'));

?>
