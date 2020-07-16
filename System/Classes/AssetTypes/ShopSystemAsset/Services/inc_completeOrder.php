<?
	$Q_Transaction = getRow("SELECT * FROM transactions WHERE tr_id = {$this->ATTRIBUTES['tr_id']}");
		// now update the order record as paid.
		$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_tr_id = {$this->ATTRIBUTES['tr_id']}");

		$basket = unserialize($Q_Order['or_details']);

		
		// check if the customer used a points discount and if they're allowed to use it
		if (ss_optionExists('Shop Acme Rockets')) {
			if (strpos($Q_Order['or_details'],'Frequent Buyer Program Points Discount') !== false) {
				$canUsePoints = false;
				$usID = $Q_Order['or_us_id'];
				$CheckPoints = getRow("
					SELECT SUM(up_points) AS TotalPoints FROM shopsystem_user_points
					WHERE UsPouug_us_id = $usID
						AND up_used IS NULL
				");		
				if ($CheckPoints['TotalPoints'] >= 4000) {
					$canUsePoints = true;	
				}		
				if ($canUsePoints) {
					// success - mark the points as used
					$Q_UsePoints = query("
						UPDATE shopsystem_user_points
						SET up_used = {$Q_Order['or_id']}
						WHERE UsPouug_us_id = $usID
							AND up_used IS NULL
					");		
						
				} else {
					// failed	
					$Q_FailTransaction = query("
						UPDATE transactions
						SET tr_completed = 0,
							tr_status_link = 3
						WHERE tr_id = {$this->ATTRIBUTES['tr_id']} AND tr_token LIKE '{$this->ATTRIBUTES['tr_token']}'
					");
					locationRelative("$assetPath/Service/ThankYou");
				}
			}
		}
		
		
		ss_paramKey($asset->cereal, $this->fieldPrefix.'CUSTOMER_USERGROUPS', array());				
		foreach ($asset->cereal[$this->fieldPrefix.'CUSTOMER_USERGROUPS'] as $aGroup) {
			$Q_UserGroups = query("
				SELECT * FROM user_user_groups 
				WHERE uug_us_id = {$Q_Order['or_us_id']} AND uug_ug_id = {$aGroup}
			");
			//if the user doenst have the group, then add one
			if (!$Q_UserGroups->numRows()) {
				$Q_UpdateGroup = query("
					INSERT INTO user_user_groups 
						(uug_us_id, uug_ug_id) 
					VALUES 
						({$Q_Order['or_us_id']},  {$aGroup})
				");
			}
		}
		// check the customer has the 'Customers' user group 
		
		
		
//		$basket = unserialize(str_replace(chr(10),'',$Q_Order['or_details']));
		
		if (ss_optionExists('Shop Acme Rockets') or ss_optionExists('Shop Combo Products')) {

			// we need to loop through the product and split out any combo products
			$newBasket = array();
			foreach ($basket['OrderProducts'] as $index => $aProduct) {
				if ( array_key_exists( 'pr_combo', $aProduct['Product'])
						&& $aProduct['Product']['pr_combo'] ) {
					// Need to find all the products in the combo and then add to the basket
					$Q_ComboProducts = query("
						SELECT * FROM shopsystem_combo_products, shopsystem_products LEFT JOIN shopsystem_product_extended_options ON pro_pr_id = pr_id
						WHERE cpr_element_pr_id = {$aProduct['Product']['pr_id']}
							AND cpr_pr_id = pr_id
					");
					while ($cp = $Q_ComboProducts->fetchRow()) {
						// Grab each product and make a new entry in the basket
						$key = $cp['pr_id'].'_'.$cp['pro_id'];
						$found = false;
						foreach($newBasket as $nbIndex => $nbProduct) {
							if ($nbProduct['Key'] == $key) {
								$newBasket[$nbIndex]['Qty'] += $cp['cpr_qty']*$aProduct['Qty'];
								$found = true;
								break;	
							}	
						}
						if (!$found) {
							$product = getRow("
								SELECT pr_id,pr_short,pr_name,pro_uuids, pro_stock_code,pro_price,pro_special_price,
									pro_member_price, pr_ve_id
								FROM shopsystem_products,shopsystem_product_extended_options
								WHERE pr_id = ".safe($cp['pr_id'])."
									AND pr_id = pro_pr_id
									AND pro_id = ".safe($cp['pro_id'])."
							");

							if ($product !== null) {
								// Figure out the description of the options.. there arent any anyway.. :S
								$options = '';
								foreach(ListToArray($product['pro_uuids'],',') as $option) {
									$parent = ListFirst($option,'=');	
									$uuid = ListLast($option,'=');	
									$option = getRow("
										SELECT * FROM select_field_options
										WHERE sfo_uuid = '".escape($uuid)."'
									");
									if ($option !== null) {
										$options .= ss_comma($options,', ').$option['sfo_value'];
									}
								}
								$product['Options'] = $options;
								
								$newProduct = array(
									'Key'	=>	$key,
									'Qty'	=>	$cp['cpr_qty']*$aProduct['Qty'],
									'Product'	=>	$product,
								);
								array_push($newBasket,$newProduct);
							}
						}
					}
				} else {
					$found = false;
					foreach($newBasket as $nbIndex => $nbProduct) {
						if ($nbProduct['Key'] == $aProduct['Key']) {
							$newBasket[$nbIndex]['Qty'] += $aProduct['Qty'];
							$found = true;
							break;	
						}	
					}				
					if (!$found) {	
						array_push($newBasket,$aProduct);	
					}
				}
			}
			

			$newerBasket = array();

			$sdetails = unserialize($Q_Order['or_shipping_details']);
			$first_name = escape(rtrim(ltrim($sdetails['PurchaserDetails']['first_name'])));
			$last_name = escape(rtrim(ltrim($sdetails['PurchaserDetails']['last_name'])));
			$billingAddress = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A1'])));
			$City = $sdetails['PurchaserDetails']['0_50A2'];
			$b_state_country = ' '.$sdetails['PurchaserDetails']['0_50A4'];
			$pos = strpos( $b_state_country, "<BR>" );
			if( $pos )
			{
				$b_state = substr( $b_state_country, 0, $pos );
				$b_country = substr( $b_state_country, $pos + 4 );
			}
			else
			{
				$b_state = $b_state_country;
				$b_country = $b_state_country;
			}
			$destinationZone = getField( "select cn_post_zone from countries where cn_name = '$b_country'");

			for	($index=0;$index<count($newBasket);$index++) {
				$entry = $newBasket[$index];
				if ($entry['Qty'] > 0) {
					
					// Figure out the price to charge
					$price = $entry['Product']['pro_price'];
					if ($entry['Product']['pro_special_price'] !== null) $price = $entry['Product']['pro_special_price'];

					// TODO ?????

					// add included freight 
//					$shippingMethod = getField( "select ve_shipping_method from vendor where ve_id = {$entry['Product']['pr_ve_id']}" );
//					$includedFreight = getField( "select if_cost from included_freight where if_shipping_method = '$shippingMethod' and if_destination_zone = '$destinationZone'" );
//					$price += $includedFreight;
//
					// Round the amount
					if (ss_optionExists('Shop Round Prices')) {
						$price = ss_roundMoney($price);
					}
					
					$entry['Product']['Price'] = $price;
					
					array_push($newerBasket,$entry);
					//$total += $entry['Qty']*$entry['Product']['Price'];
					//$totalUnits += $entry['Qty'];
				};
			}
			
			
			
			// Update and return it back into the order
			$basket['OrderProducts'] = $newerBasket;
			$newOrDetails = escape(serialize($basket));
			
			$theBasket = unserialize($Q_Order['or_basket']);
			$theBasket['Basket']['Products'] = $newerBasket;
			$theBasket = escape(serialize($theBasket));

			$Q_UpdateOrder = query("
				UPDATE shopsystem_orders
				SET or_details = '{$newOrDetails}',
					or_basket = '{$theBasket}'
				WHERE or_tr_id = {$this->ATTRIBUTES['tr_id']}			
			");

			// find the order id.. -_-
			$orderRow = getRow("
				SELECT or_id AS ID, or_site_folder  FROM shopsystem_orders
				WHERE or_tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
			");
			
			$Q_Clean = query("
				DELETE FROM ordered_products WHERE op_or_id ={$orderRow['ID']}
			");
			
			// Record the order into separate fields...
			foreach($newerBasket as $product) {
				$Q_InsertOrderProduct = query("
					INSERT INTO ordered_products
						(op_or_id, op_pr_id, op_stock_code, op_quantity, op_price_paid, op_pr_name, op_site_folder)
					VALUES 
						({$orderRow['ID']}, {$product['Product']['pr_id']}, '".escape($product['Product']['pro_stock_code'])."',
						{$product['Qty']}, {$product['Product']['Price']}, '".escape($product['Product']['pr_name'])."',
						'".escape($orderRow['or_site_folder'])."'
					)
				");
			}
			
			
		}
		
//		ss_log_message_r($Q_Transaction,'trans',true);
		
		
		if ($Q_Transaction['tr_payment_method'] != 'WebPay_CreditCard_Manual' 
			and $Q_Transaction['tr_payment_method'] != 'Cheque'
			and $Q_Transaction['tr_payment_method'] != 'Direct'
			and $Q_Transaction['tr_payment_method'] != 'Invoice'
			and $Q_Transaction['tr_payment_method'] != 'Collection'
			) {			
				
//			if (ss_isItUs()) die('arrrgh');
				
			$Q_UpdateOrder = query("
					UPDATE shopsystem_orders 
					SET 
						or_paid = Now()
					WHERE
						or_tr_id = {$this->ATTRIBUTES['tr_id']}
						AND 
						or_us_id = {$Q_Order['or_us_id']}
			");	
			
		
			//ss_DumpVarDie($basket['OrderProducts']['Products']);
			// add order products into the db.
			
			foreach($basket['OrderProducts'] as $aProduct) {
				$name = escape("{$aProduct['Product']['pr_name']} ({$aProduct['Product']['Options']})");
				$price = $aProduct['Qty'] * $aProduct['Product']['Price'];
				$price = escape($this->formatPrice('display', $price));
			
				
				// Update the product's stock availability since this product
				// option has been sold.
				/*$ProductOption = getRow("
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
				}*/
					
				$Q_Insert = query("
						INSERT INTO shopsystem_order_products 
							(orpr_or_id, orpr_pr_id, orpr_pr_name, orpr_price, orpr_qty, orpr_timestamp, orpr_site_folder) 
						VALUES
							({$Q_Order['or_id']}, {$aProduct['Product']['pr_id']}, '$name', '$price', {$aProduct['Qty']}, Now(), '{$GLOBALS['cfg']['folder_name']}')		
				");
			}			
			

			// Update the product's stock availability since this product
			// option has been sold.
			// We do this always.. to prevent people over-ordering products, instead of
			// when the product has been paid for

			foreach($basket['OrderProducts'] as $aProduct) {
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
					ss_audit( 'update', 'Products', $aProduct['Product']['pr_id'], 'new admin order, reducing available stock by '.$aProduct['Qty'] );
				}
			}			
		}

		if (ss_optionExists('Shop Acme Rockets')) {
			// calculate profit for the order immediately
			$res = new Request("ShopSystem.AcmeCalculateOrderProfit",array(
				'or_id'	=>	$Q_Order['or_id'],
			));	
		}
		
		
?>		
