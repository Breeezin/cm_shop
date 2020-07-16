<?php
	// function getOptions($product,$fieldsArray,$currentOptions=-1)

	// Find out what option names this product category supports
	$opsUsed = array();
	$uuids = "'1'";
	foreach(ListToArray($product['ca_option_setting']) as $optionUUID) {
		$uuids .= ",'".escape($optionUUID)."'";
		$opsUsed[$optionUUID] = array();
	}

	// Add options that apply to all categories
	foreach ($fieldsArray as $fieldDef) {
		ss_paramKey($fieldDef,'ShowTo');
		ss_paramKey($fieldDef,'uuid');
		if ($fieldDef['ShowTo'] == 'all') {
			$uuids .= ",'".escape($fieldDef['uuid'])."'";
			$opsUsed[$fieldDef['uuid']] = array();
		}
	}
	
	// Find out all the option values availabe to the options in this product
	/*$Q_Options = query("
		SELECT * FROM select_field_options
		WHERE sfo_parent_uuid IN ($uuids)
	");
	$options = array();
	while ($op=$Q_Options->fetchRow()) {
		$options[$op['sfo_uuid']] = $op['sfo_value'];
	}*/

	// Make an array of all the options and option values used by this product	
	$Q_ProductOptions = query("
		SELECT * FROM shopsystem_product_extended_options
		WHERE pro_pr_id = {$product['pr_id']}
	");
	$productOptions = array();
	
	// Check for discount codes
	$discountCodes = ss_optionExists('Shop Discount Codes');	
	
	while ($ops=$Q_ProductOptions->fetchRow()) {
		
		$priceOptions = array();
		foreach(ListToArray($ops['pro_uuids']) as $op) {
			$opParent = ListFirst($op,"=");	
			$opUUID = ListLast($op,"=");	
			if (array_key_exists($opParent,$opsUsed)) {
				$priceOptions[$opParent] = $opUUID;
			}
		}

		if (!$discountCodes) $product['pr_dig_id'] = null;
		$price = $this->getPrice($product['pr_id'],$product['pr_dig_id'],$ops['pro_id'],'Complete');
		
		if( $ops['pro_typical_daily_sales'] )
		{
			$days_held = $GLOBALS['cfg']['DaysStockHeld'];
//			ss_log_message( "Munging stock level on pr_id {$product['pr_id']} by {$ops['pro_typical_daily_sales']} * $days_held" );
			$ops['pro_stock_available'] -= $ops['pro_typical_daily_sales']*$days_held;
		}

		$productOption = array(
			'Price'		=>	$price['NormalPrice'],
			'SpecialPrice'	=>	$price['SpecialPrice'],
			'RRP'	=>	$price['RRP'],
			'rawPrice'		=>	$price['rawNormalPrice'],
			'rawSpecialPrice'	=>	$price['rawSpecialPrice'],
			'rawRRP'	=>	$price['rawRRP'],
			'Options'	=>	array(),
			'ID'		=>	$ops['pro_id'],
			'currency_converter'	=>	false,
			'StockAvailable'	=>	$ops['pro_stock_available'],
		);
		//$imgDir = ss_secretStoreForAsset($assetID,"ProductImages");
			
		if ($price['currency_converter']) {
			$productOption['currency_converter'] = true;	
			$productOption['rawPriceApprox'] = $price['NormalPriceApprox'];	
			$productOption['rawSpecialPriceApprox'] = $price['SpecialPriceApprox'];	
			$productOption['rawRRPApprox'] = $price['RRPApprox'];	
		} else {
			$productOption['rawPriceApprox'] = 0;	
			$productOption['rawSpecialPriceApprox'] = 0;	
			$productOption['rawRRPApprox'] = 0;	
		}
		if (ss_optionExists('Shop Product Option Images')) {
			$productOption['optionImage'] = $ops['pro_image'];	
		}
		if (ss_optionExists('Shop Product Option StockCode')) {
			$productOption['stockCode'] = $ops['pro_stock_code'];	
		}		
		foreach(ListToArray($ops['pro_uuids']) as $op) {
			$opParent = ListFirst($op,"=");	
			$opUUID = ListLast($op,"=");	
			if (array_key_exists($opParent,$opsUsed)) {
				$opsUsed[$opParent][$opUUID] = 1;
				array_push($productOption['Options'],array(
					'parent'	=>	$opParent,
					'uuid'	=>	$opUUID,
				));
			}
		}
		
		array_push($productOptions,$productOption);

	}
	$optionFieldDefs = array();
	foreach ($fieldsArray as $field) {
		ss_paramKey($field,'uuid','');
		ss_paramKey($field,'name','');
		$Q_Options = query("
			SELECT * FROM select_field_options
			WHERE sfo_parent_uuid = '{$field['uuid']}'
		");
		if (array_key_exists($field['uuid'],$opsUsed) and count($opsUsed[$field['uuid']])) {
			array_push($optionFieldDefs,$field);
		}
	}
	
	if (strlen($currentOptions) == 0) $currentOptions = -1;	
	
	$data = array(
		'pr_id'				=>	$product['pr_id'],
		'pro_stock_available'	=>	$product['pro_stock_available'],
		'ProductOptions'	=>	$productOptions,
		'OptionFieldDefs'	=>	$optionFieldDefs,
		'OptionValuesUsed'	=>	$opsUsed,
		'Currency'	=>	$this->getDisplayCurrency(),
		'CurrentOptions'	=>	$currentOptions,
	);

?>
