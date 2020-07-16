<?php

	ss_paramKey($_SESSION,'Shop',array());
	$this->param('Product');

	$this->param('Template','SingleProduct');

	if ($this->ATTRIBUTES['Product'] == '..') die('Invalid Product');
	if (!strlen($this->ATTRIBUTES['Product'])) {
		locationRelative($assetPath);
	}
	$countryHideSQL = '1=1';
	if (ss_optionExists("Shop Products Block Individual countries")) {
		$countryHideSQL = "(PrExcludeCountries NOT LIKE '%{$_SESSION['Shop']['MultiCurrencyCountryDef']['CountryCode']}%' OR PrExcludeCountries IS NULL)";
	}

	$Q_Product = query("
		SELECT * FROM shopsystem_products join shopsystem_categories on pr_ca_id = ca_id
			left join product_type on pr_type = pt_id
		WHERE pr_id = ".safe($this->ATTRIBUTES['Product'])."
			AND pr_deleted IS NULL
			AND pr_offline IS NULL
			AND pr_is_service = 'false'
			AND pr_as_id = ".$asset->getID()."
			AND $countryHideSQL
			".ss_shopRestrictedCategoriesSQL()."
	");

	if ($Q_Product->numRows() != 0) {

    	$assetPath = ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath()));
    	$assetStore = ss_EscapeAssetPath(ss_storeForAsset($asset->getID()));



    		// Check for discount codes
    	$discountCodes = ss_optionExists('Shop Discount Codes');

    	// Generate prices table and also the category breadcrumb trail
    	$Q_Product->addColumn('PricesHTML');
    	$Q_Product->addColumn('ThumbImage');
    	$Q_Product->addColumn('Image');
    	$Q_Product->addColumn('FullImage');

       	$thumbSize = "80x80";
    	if (strlen(ss_optionExists('Shop Single Product Image Size'))) {
    		$thumbSize = ss_optionExists('Shop Single Product Image Size');
    	}

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

    		$currentCategory = $row['pr_ca_id'];
    		while (strlen($currentCategory)) {
    			$category = getRow("
    				SELECT * FROM shopsystem_categories
    				WHERE ca_id = $currentCategory
    			");
    			$categoryBreadCrumbs = "<a href=\"{$assetPath}/Service/Engine/pr_ca_id/{$category['ca_id']}\">".ss_HTMLEditFormat($category['ca_name'])."</a> ".(strlen($categoryBreadCrumbs)?'<span>&gt;</span>':'').' '.$categoryBreadCrumbs;
    			$categoryBreadCrumbsNoHtml = $category['ca_name'].(strlen($categoryBreadCrumbsNoHtml)?' > ':'').$categoryBreadCrumbsNoHtml;
    			$currentCategory = $category['ca_parent_ca_id'];
    		}

    		// Figure out the image
			/*
    		if (strlen($row['pr_image1_thumb'])) {
    			$image = $assetStore."/ProductImages/".$row['pr_image1_thumb'];
    		} elseif (strlen($row['pr_image1_normal'])) {
    			$image = 'index.php?act=ImageManager.get&Size='.$thumbSize.'&Image='.ss_URLEncodedFormat($assetStore."/ProductImages/".$row['pr_image1_normal']);
    		} elseif (strlen($row['pr_image1_large'])) {
    			$image = 'index.php?act=ImageManager.get&Size='.$thumbSize.'&Image='.ss_URLEncodedFormat($assetStore."/ProductImages/".$row['pr_image1_large']);
    		} else {
    			$image = null;
    		}
    		$Q_Product->setCell('Image',$image,0);
			*/
    		$Q_Product->setCell('ThumbImage','index.php?act=ImageManager.get&ProductThumb='.$row['pr_id'],0);
    		$Q_Product->setCell('Image','index.php?act=ImageManager.get&Product='.$row['pr_id'],0);
    		$Q_Product->setCell('FullImage','index.php?act=ImageManager.get&ProductFull='.$row['pr_id'],0);

    	}

    	// Get a product administration object so we have a list of all attribute
    	// and option fields
    	requireClass('ShopSystem_ProductsAdministration');
    	$temp = new Request("Security.Sudo",array('Action'=>'start'));
    	$productAdmin = new ShopSystem_ProductsAdministration($asset->getID(),$this->ATTRIBUTES['Product']);
    	$productAdmin->primaryKey = $this->ATTRIBUTES['Product'];
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

	}

?>
