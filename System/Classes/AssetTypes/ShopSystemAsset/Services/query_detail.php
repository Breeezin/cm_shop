<?php
	
	ss_paramKey($_SESSION,'Shop',array());
	$this->param('Product');
	$getProduct = (int) $this->ATTRIBUTES['Product'];

	if( !($getProduct > 0 ) )
		locationRelative($assetPath);	

	$countryHideSQL = '1=1';
	if (ss_optionExists("Shop Products Block Individual countries")) {
		$countryHideSQL = "(PrExcludeCountries NOT LIKE '%{$_SESSION['Shop']['MultiCurrencyCountryDef']['CountryCode']}%' OR PrExcludeCountries IS NULL)";
	}	

	$externalSQL = '1=1';

	if( array_key_exists( 'cfg', $GLOBALS )
	 && array_key_exists( 'currentLanguage', $GLOBALS['cfg'] )
	 && $GLOBALS['cfg']['currentLanguage'] > 0 )
	{
		$Q_Product = query("
			SELECT * FROM shopsystem_products
				 join shopsystem_product_extended_options on pro_pr_id = pr_id
				 left join product_type on pr_type = pt_id
				 left join shopsystem_product_descriptions on prd_pr_id = pr_id AND prd_language = ".$GLOBALS['cfg']['currentLanguage'].",
				 shopsystem_categories
				 left join shopsystem_category_descriptions on cad_ca_id = ca_id AND cad_language = ".$GLOBALS['cfg']['currentLanguage']."
			WHERE pr_id = $getProduct
				AND pr_deleted IS NULL
				AND pr_offline IS NULL
				AND pr_is_service = 'false'
				AND pr_as_id = ".$asset->getID()."
				And ( pr_ca_id = ca_id OR pr_sub_ca_id = ca_id )
				AND $countryHideSQL
				AND $externalSQL
				".ss_shopRestrictedCategoriesSQL()."
		");
	}
	else
	{
		$Q_Product = query("
			SELECT * FROM shopsystem_products
				 join shopsystem_product_extended_options on pro_pr_id = pr_id
				 left join product_type on pr_type = pt_id,
				 shopsystem_categories
			WHERE pr_id = $getProduct
				AND pr_deleted IS NULL
				AND pr_offline IS NULL
				AND pr_is_service = 'false'
				AND pr_as_id = ".$asset->getID()."
				And ( pr_ca_id = ca_id )
				AND $countryHideSQL
				AND $externalSQL
				".ss_shopRestrictedCategoriesSQL()."
		");
	}

	if ($Q_Product->numRows() == 0) {
		locationRelative('');	
	}
	
	$assetPath = ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath()));
	$assetStore = ss_EscapeAssetPath(ss_storeForAsset($asset->getID()));
	
	
	
		// Check for discount codes
	$discountCodes = ss_optionExists('Shop Discount Codes');	
	
	// Generate prices table and also the category breadcrumb trail
	$Q_Product->addColumn('PricesHTML');
	$Q_Product->addColumn('PricesSmall');
	$categoryBreadCrumbs = '';
	$categoryBreadCrumbsNoHtml = '';
	while ($row = $Q_Product->fetchRow()) {
		
		// VIP Products
		$vipSQL = '';
		$vipGroup = ss_optionExists('Shop VIP Products');
		if ($vipGroup !== false) {
			if (array_key_exists($vipGroup,$_SESSION['User']['user_groups'])) {
				// No need to remove VIP products
			} else {
				// Remove VIP products
				if ($row['pr_vip'] == 1) {
					locationRelative('');	
				}
			}
		}		
		
		if (!$discountCodes) $row['pr_dig_id'] = null;
		$Q_Product->setCell('PricesHTML',$this->getPrice($row['pr_id'],$row['pr_dig_id'],null,'TableHTML'),0);	
		$Q_Product->setCell('PricesSmall',$this->getPrice($row['pr_id'],$row['pr_dig_id'],null,'SmallPrice'),0);	
		
		$currentCategory = $row['pr_ca_id'];
		ss_log_message( "breadcrumb for $currentCategory" );
		while (strlen($currentCategory)) {
			$category = getRow("
				SELECT * FROM shopsystem_categories
				WHERE ca_id = $currentCategory
			");
			$categoryBreadCrumbs = "<a href=\"{$assetPath}/Service/Engine/pr_ca_id/{$category['ca_id']}\" class=\"onlineShop_categoryBreadcrumbLink\">".ss_HTMLEditFormat($category['ca_name'])."</a> ".(strlen($categoryBreadCrumbs)?'<span class="onlineShop_categoryBreadcrumbText">&gt;</span>':'').' '.$categoryBreadCrumbs;
			$categoryBreadCrumbsNoHtml = $category['ca_name'].(strlen($categoryBreadCrumbsNoHtml)?' > ':'').$categoryBreadCrumbsNoHtml;
			$currentCategory = $category['ca_parent_ca_id'];
		}
	}

	// Get a product administration object so we have a list of all attribute
	// and option fields
	requireClass('ShopSystem_ProductsAdministration');	
	$temp = new Request("Security.Sudo",array('Action'=>'start'));			
	$productAdmin = new ShopSystem_ProductsAdministration($asset->getID(),$getProduct);		
	$productAdmin->primaryKey = $getProduct;
	$row = null;
	$productAdmin->loadFieldValuesFromDB($row);					
	$temp = new Request("Security.Sudo",array('Action'=>'stop'));	
	
	// Get all the attribute field names
	ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_ATTRIBUTES','');
	if (strlen($asset->cereal['AST_SHOPSYSTEM_ATTRIBUTES'])) {
		$fieldsArray = unserialize($asset->cereal['AST_SHOPSYSTEM_ATTRIBUTES']);
	} else {
		$fieldsArray = array();	
	}

	// Get the attribute field values
	$attributes = array();
	foreach($fieldsArray as $fieldDef) {
		
		// Param all the settings we might have
		ss_paramKey($fieldDef,'uuid','');
		ss_paramKey($fieldDef,'type','');		
		ss_paramKey($fieldDef,'options',array());		
		ss_paramKey($fieldDef,'name','unknown');
								
		// Check the field is existing in the users database table
		$dbFieldName = 'Pr'.$fieldDef['uuid'];		
		
		if (array_key_exists($dbFieldName,$productAdmin->fields)) {			
			$attributes[$fieldDef['name']] = $productAdmin->fields[$dbFieldName]->displayValue($row[$dbFieldName]);			
		}
				
	}	

	// Prepare some values for the options
	
	// Load up the details of options	
	ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_PRODUCT_OPTIONS','');
	if (strlen($asset->cereal['AST_SHOPSYSTEM_PRODUCT_OPTIONS'])) {
		$fieldsArray = unserialize($asset->cereal['AST_SHOPSYSTEM_PRODUCT_OPTIONS']);
	} else {
		$fieldsArray = array();	
	}

	// Get the product details for options
	$product = $Q_Product->fetchRow();
	$Q_Product->reset();

	// default the currently selected option
	$this->param('Options','');

	if( array_key_exists('pr_meta_description', $product) && strlen($product['pr_meta_description']) )
		$asset->display->description = trim($product['pr_meta_description']);
	if( array_key_exists('pr_metadata_keywords', $product) && strlen($product['pr_metadata_keywords']) )
		$asset->display->keywords = trim($product['pr_metadata_keywords']);

	if( array_key_exists('prd_metadata_description', $product) && strlen($product['prd_metadata_description']) )
		$asset->display->description = trim($product['prd_metadata_description']);
	if( array_key_exists('prd_keywords', $product) && strlen($product['prd_keywords']) )
		$asset->display->keywords = trim($product['prd_keywords']);

	if( array_key_exists('pr_window_title', $product) && strlen($product['pr_window_title'])) {
		if( array_key_exists('prd_window_title', $product) && strlen($product['prd_window_title']))
			$asset->layout['LYT_WINDOWTITLE'] = trim($product['prd_window_title']);
		else
			$asset->layout['LYT_WINDOWTITLE'] = trim($product['pr_window_title']);
	} else {
		// Set the window title, if we haven't already
		ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_PRODUCT_WINDOW_TITLE_TEMPLATE','[SiteName] - [Product]');
		$windowTitle = $asset->cereal['AST_SHOPSYSTEM_PRODUCT_WINDOW_TITLE_TEMPLATE'];
		$windowTitle = stri_replace('[SiteName]',$GLOBALS['cfg']['website_name'],$windowTitle);
		$windowTitle = stri_replace('[Product]',$product['pr_name'],$windowTitle);
		$asset->layout['LYT_WINDOWTITLE'] = $windowTitle;
	}
	$asset->display->assetLayoutSettings = $asset->layout;
?>
