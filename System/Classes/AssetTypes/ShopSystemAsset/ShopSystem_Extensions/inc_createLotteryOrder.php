<?
	// we have $winningOrderID and $Freebox['lotw_pr_id']

	$Shop = array();
	$Shop['as_id'] = 514;
	
	// First step.. empty the basket
	$result = new Request("Asset.Display",array(
		'as_id'	=>	$Shop['as_id'],
		'Service'	=>	'UpdateBasket',
		'Mode'	=>	'Empty',
		'AsService'	=>	true,
		'NoHusk'	=>	1,
	));
	
	
	// Add the winning box to the basket
					
	$pr_id = $FreeBox['lotw_pr_id'];	
	$optionID = getRow("
		SELECT pro_id FROM shopsystem_product_extended_options
		WHERE pro_pr_id = {$pr_id}
	");
	
	// add the product to the basket
	$result = new Request("Asset.Display",array(
		'as_id'	=>	$Shop['as_id'],
		'Service'	=>	'UpdateBasket',
		'Mode'	=>	'Add',
		'Key'	=>	$pr_id.'_'.$optionID['pro_id'],
		'Qty'	=>	1,
		'AsService'	=>	true,
		'NoHusk'	=>	1,
	));

	$Order = getRow("
		SELECT or_shipping_details, or_shipping_values, or_us_id, or_purchaser_firstname, or_purchaser_lastname, or_purchaser_email FROM shopsystem_orders
		WHERE or_id = $winningOrderID
	");
	
	$shippingDetails = unserialize($Order['or_shipping_details']);
	$shippingValues = unserialize($Order['or_shipping_values']);
	

	// add the order
	$result = new Request("Asset.Display",array(
		'as_id'	=>	$Shop['as_id'],
		'Service'	=>	'GenerateOrder',
		'ShippingDetails'	=>	$shippingDetails['ShippingDetails'],
		'PurchaserDetails'	=>	$shippingDetails['PurchaserDetails'],
		'ShippingValues'	=>	$shippingValues,
		'us_id'	=>	$Order['or_us_id'],
		'us_name'	=>	array(
			'first_name'	=>	$Order['or_purchaser_firstname'],
			'last_name'	=>	$Order['or_purchaser_lastname'],
		),
		'us_email'	=>	$Order['or_purchaser_email'],
		'NoHusk'	=>	1,
	));
	
	// Update the product's stock availability since this product
	// option has been sold.
	// We do this always.. to prevent people over-ordering products, instead of
	// when the product has been paid for
	foreach($_SESSION['Shop']['Basket']['Products'] as $aProduct) {
		$ProductOption = getRow("
			SELECT * FROM shopsystem_product_extended_options
			WHERE pro_stock_code LIKE '{$aProduct['Product']['pro_stock_code']}'
		");
		if ($ProductOption['pro_stock_available'] !== null) {
			// If the product option is using the stock level management..
			$Q_UpdateProductOption = query("
				UPDATE shopsystem_product_extended_options
				SET pro_stock_available = ".($ProductOption['pro_stock_available']-$aProduct['Qty'])."
				WHERE pro_id = {$ProductOption['pro_id']}
			");
		}
	}						
	
	$NewOrder = getRow("
		SELECT or_id FROM shopsystem_orders
		WHERE or_tr_id = ".safe($result->display)."
	");
	$UpdateOrder = query("
		UPDATE shopsystem_orders
		SET or_lottery = ".ss_TimeStampToSQL(now())."
		WHERE or_tr_id = ".safe($result->display)."
	");
	$UpdateTransaction = query("
		UPDATE transactions
		SET tr_completed = 1,
			tr_timestamp = NOW()				
		WHERE tr_id = ".safe($result->display)."
	");

?>