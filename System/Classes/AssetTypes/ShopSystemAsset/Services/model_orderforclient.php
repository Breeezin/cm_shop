<?php

	$_SESSION['Shop']['DiscountCode'] = NULL;

	if ($_SESSION['Shop']['EditingOrder'] != null)
	{
		$Order = getRow("
			SELECT * FROM shopsystem_orders
			WHERE or_id = {$_SESSION['Shop']['EditingOrder']}
			");
		$Transaction = getRow( "select * from transactions where tr_id = {$Order['or_tr_id']}" );
	}
	else
	{
		$Order = array();
		$Transaction = array();
	}

	if (array_key_exists("UpdateDiscount", $this->ATTRIBUTES)) {

		$OrderID = $_SESSION['Shop']['EditingOrder'];

		extract( $Order );

		$basket = unserialize( $or_basket );

		$total_discount = (float)$_POST['DiscountAmount'];

		$_SESSION['Shop']['Basket']['Discounts'] = array( 'Personal Discount' => $total_discount );
		$basket['Basket']['Discounts'] = array( 'Personal Discount' => $total_discount );

		$total = $_SESSION['Shop']['Basket']['Total'] = $basket['Basket']['Total'] = $basket['Basket']['SubTotal'] + $total_discount + $basket['Basket']['Freight']['Amount'];

		Query("update transactions set tr_total = $total, tr_charge_total = '&euro;$total EUR' where tr_id = $or_tr_id" );

		$result = new Request('Asset.Display',array(
				'Service'	=>	'Basket',
				'as_id'	=>	$this->asset->getID(),
				'Style'		=>	'NoInputs',
				'NoHusk'	=>	true,
			));

		$giftmsg = '';
		$this->param('GiftMessage', '');
		if (ss_OptionExists('Gift Message')) {
			$giftmsg = $this->ATTRIBUTES['GiftMessage'];
		}
		
		$orderDetails = array('OrderProducts' =>$_SESSION['Shop']['Basket']['Products'], "BasketHTML" =>str_replace(chr(10),'',$result->display), 'GiftMessage' => $giftmsg);
		$details = escape(serialize($orderDetails));

		// need to update transactions too.

		Query( "update shopsystem_orders set or_basket = '".escape(serialize($basket))."', or_details = '$details' where or_id = {$_SESSION['Shop']['EditingOrder']}" );

		$accessCode = '';
		if (array_key_exists('AccessCode', $_REQUEST))
			$accessCode = $_REQUEST['AccessCode'];
		else if (array_key_exists('AccessCode', $_SESSION)) 
					$accessCode = $_SESSION['AccessCode'];		

		$secureSite = ss_withTrailingSlash($GLOBALS['cfg']['secure_server']);

		location($secureSite."index.php?act=ShopSystem.ViewOrder&AccessCode=$accessCode&or_id={$OrderID}&tr_id={$or_tr_id}&as_id=".$asset->getID());	
	}

	if (array_key_exists("UpdateFreight", $this->ATTRIBUTES)) {


		# sanitise and save $_POST['FreightAmount'] as ['Basket']['Freight']['Amount']

		extract( $Order );

		$basket = unserialize( $or_basket );

		$exclShipping = $_SESSION['Shop']['Basket']['Freight']['Amount'] = $basket['Basket']['Freight']['Amount'] = (float)$_POST['FreightAmount'];

		$total_discount = 0;
		if (array_key_exists('Discounts',$basket['Basket']))
			foreach($basket['Basket']['Discounts'] as $DiscountName => $DiscountAmount )
				$total_discount += $DiscountAmount;

		$total = $_SESSION['Shop']['Basket']['Total'] = $basket['Basket']['Total'] = $basket['Basket']['SubTotal'] + $exclShipping + $total_discount;

		if( strlen( $Order['or_reshipment'] ) )
			$total = 0;

		if( $OrderID = $_SESSION['Shop']['EditingOrder'] )
		{
			Query("update transactions set tr_total = $total, tr_charge_total = '&euro;$total EUR', tr_excl_shipping = $exclShipping where tr_id = $or_tr_id" );

			$result = new Request('Asset.Display',array(
					'Service'	=>	'Basket',
					'as_id'	=>	$this->asset->getID(),
					'Style'		=>	'NoInputs',
					'NoHusk'	=>	true,
				));

			$giftmsg = '';
			$this->param('GiftMessage', '');
			if (ss_OptionExists('Gift Message')) {
				$giftmsg = $this->ATTRIBUTES['GiftMessage'];
			}
			
			$orderDetails = array('OrderProducts' =>$_SESSION['Shop']['Basket']['Products'], "BasketHTML" =>str_replace(chr(10),'',$result->display), 'GiftMessage' => $giftmsg);
			$details = escape(serialize($orderDetails));

			// need to update transactions too.

			Query( "update shopsystem_orders set or_basket = '".escape(serialize($basket))."', or_details = '$details' where or_id = {$_SESSION['Shop']['EditingOrder']}" );

			$accessCode = '';
			if (array_key_exists('AccessCode', $_REQUEST))
				$accessCode = $_REQUEST['AccessCode'];
			else if (array_key_exists('AccessCode', $_SESSION)) 
						$accessCode = $_SESSION['AccessCode'];		

			$secureSite = ss_withTrailingSlash($GLOBALS['cfg']['secure_server']);

			location($secureSite."index.php?act=ShopSystem.ViewOrder&AccessCode=$accessCode&or_id={$OrderID}&tr_id={$or_tr_id}&as_id=".$asset->getID());	
		}
	}

	if (array_key_exists("Do_Service", $this->ATTRIBUTES)) {

		$shipping->fieldSet->loadFieldValuesFromForm($this->ATTRIBUTES);
		// record our new shipping values into the session
		ss_paramKey($_SESSION['Shop'],'ShippingDetails',array());
		foreach($shipping->fieldSet->fields as $fieldName => $field) {
			$_SESSION['Shop']['ShippingDetails'][$fieldName] = $field->value;
		}
		

		/*$this->ATTRIBUTES['us_id'] = $usID;						
		$this->ATTRIBUTES['DoAction'] = 'Yes';						*/
		$userAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);			
		// record our new purchaser details into the session
		ss_paramKey($_SESSION['Shop'],'PurchaserDetails',array());
		foreach($userAdmin->fields as $fieldName => $field) {
			$_SESSION['Shop']['PurchaserDetails'][$fieldName] = $userAdmin->fields[$fieldName]->value;
		}


		//$usID = ss_getUserID();
		$usID = $_SESSION['Shop']['OrderingFor'];
		
		// check user whether he/she is in the db already
		
		if($usID >= 0) {
			$userAdmin->primaryKey = $usID;
			
			// Validate and then write to the database		     		
			//$errors = $userAdmin->update();				
			$errors = $userAdmin->validate();
			if (!count($errors)) {
				$userAdmin->update();				
			}
			
		} else {
			$Q_User = getRow("SELECT * FROM users WHERE us_email LIKE '{$this->ATTRIBUTES['us_email']}'");
			if (strlen($Q_User['us_id'])) {
				
				// We should check that they are not an adminstrator account,
				// as this would be a security risk since passwords are not checked
				// so we don't want to log them in as a user
				$Q_UserGroups = query("
					SELECT * FROM user_user_groups
					WHERE uug_us_id = {$Q_User['us_id']}
				");
				while ($ug = $Q_UserGroups->fetchRow()) {
					if ($ug['uug_ug_id'] == 1) {
						$errors = array('Email' => array('Please use a different email address.'));
						break;
					}	
				}
				
				if (!count($errors)) {
					//$userAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);		
					$userAdmin->primaryKey = $Q_User['us_id'];				
					$usID = $Q_User['us_id'];				
					$errors = $userAdmin->validate();
					if (!count($errors)) {
						$userAdmin->update();				
					}
					$errorsTemp = '';
					$_SESSION['Shop']['OrderingFor'] = $userAdmin->primaryKey;
					//ss_login($userAdmin->primaryKey,$errorsTemp);
				}
			} else {
				$temp = new Request("Security.Sudo",array('Action'=>'start'));
				
				//$userAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);			
				// Validate and then write to the database		
				$errors = $userAdmin->validate();
				if (!count($errors)) {
					$userAdmin->insert();		
					$usID = $userAdmin->primaryKey;
					$errorsTemp = '';
					$_SESSION['Shop']['OrderingFor'] = $userAdmin->primaryKey;
//					ss_login($usID,$errorsTemp);
				}
				
				$temp = new Request("Security.Sudo",array('Action'=>'stop'));
			}
			
			
		}
		
		$errors = array_merge($errors,$shipping->fieldSet->validate());	
		
		if (!count($errors)) {
			
			// Add customer user group
			/*if ($this->ATTRIBUTES['JoinNewsletter'] == 'checked') {
				$Q_Group = getRow("SELECT * FROM user_groups WHERE ug_name LIKE 'Mailing List'");
					
				// check the customer has the 'Customers' user group 
				$Q_UserGroups = query("
						SELECT * FROM user_user_groups 
						WHERE uug_us_id = {$usID} AND uug_ug_id = {$Q_Group['ug_id']}
				");
				//if the user doenst have the group, then add one
				if (!$Q_UserGroups->numRows()) {
					$Q_UpdateGroup = query("
						INSERT INTO user_user_groups 
							(uug_us_id, uug_ug_id) 
						VALUES 
							({$usID},  {$Q_Group['ug_id']})
					");
				}		
			}*/
		
			//die('just added group');
			
			$token = md5(rand());			
			
			// store the shipping detail and purchaser details
			// because the purchaser details can be changed later.. 
			// so the order store the current values.												
			$purchaserDetails = array();
			$shippingDetails = array();
			$shippingValues = array();
			
			foreach($userAdmin->fields as $field) {				
				if (array_search($field->name, $shipping->notSelectedFieldNames) !== 0) {
					$fieldName = substr($field->name,2);
					if ($fieldName == 'Name') {
						$purchaserDetails[$fieldName] = $userAdmin->getFieldDisplayValue($field->name);
						$purchaserDetails['first_name'] = $userAdmin->fields[$field->name]->displayFirstName($userAdmin->fields[$field->name]->value);
						$purchaserDetails['last_name'] = $userAdmin->fields[$field->name]->displayLastName($userAdmin->fields[$field->name]->value);					
					} else {
						$purchaserDetails[$fieldName] = $userAdmin->getFieldDisplayValue($field->name);
					}
				}	
			}

			foreach($shipping->fieldSet->fields as $field) {
				$shippingValues[$field->name] = $field->value;
				$fieldName = substr($field->name,4);				
				if ($fieldName == 'Name') {
					$shippingDetails[$fieldName] = $shipping->fieldSet->getFieldDisplayValue($field->name);
					$shippingDetails['first_name'] = $shipping->fieldSet->fields[$field->name]->displayFirstName($field->value);
					$shippingDetails['last_name'] = $shipping->fieldSet->fields[$field->name]->displayLastName($field->value);					
				} else {
					$shippingDetails[$fieldName] = $shipping->fieldSet->getFieldDisplayValue($field->name);			
				}
			}
			$countryID = (int)$shippingValues['ShDe0_50A4'];

			$shippingDetailsSerialized = escape(serialize(array('ShippingDetails' => $shippingDetails, 'PurchaserDetails' => $purchaserDetails)));
			$shippingValuesSerialized = escape(serialize($shippingValues));
			
			$assetID = $this->asset->getID();
			$sessionBasket = escape(serialize($_SESSION['Shop']));
			//$sessionBasket = '';
			$orTotal = $this->formatPrice('display',$_SESSION['Shop']['Basket']['Total']);
			//$orTotal = "\${$_SESSION['Shop']['Basket']['Total']} NZD";
			$email = '';
			if(array_key_exists('us_email', $this->ATTRIBUTES)) {
				$email = escape($this->ATTRIBUTES['us_email']);
			}
			$firstName = '';
			$lastName = '';
			if(array_key_exists('us_name', $this->ATTRIBUTES)) {
				$firstName = escape($this->ATTRIBUTES['us_name']['first_name']);
				$lastName = escape($this->ATTRIBUTES['us_name']['last_name']);
			}
			$result = new Request('Asset.Display',array(
				'Service'	=>	'Basket',
				'as_id'	=>	$this->asset->getID(),
				'Style'		=>	'NoInputs',
				'NoHusk'	=>	true,
			));
			
			$giftmsg = '';
			$this->param('GiftMessage', '');
			if (ss_OptionExists('Gift Message')) {
				$giftmsg = $this->ATTRIBUTES['GiftMessage'];
			}
			
			$orderDetails = array('OrderProducts' =>$_SESSION['Shop']['Basket']['Products'], "BasketHTML" =>str_replace(chr(10),'',$result->display), 'GiftMessage' => $giftmsg);
			$basket = escape(serialize($orderDetails));
			
			// new order?
			if ($_SESSION['Shop']['EditingOrder'] === null) 
			{
				// Redlane wanted this in a separate field. Might as well do it for all shops
				$insertDiscountCodeField = '';
				$insertDiscountCodeValue = '';
				if (ss_optionExists('Shop Discount Codes')) {
					if ($_SESSION['Shop']['DiscountCode'] !== null) {
						$insertDiscountCodeField = ', or_discount_code';
						$insertDiscountCodeValue = ", '".escape($_SESSION['Shop']['DiscountCode'])."'";
					}
				}
	
				$insertShippingValuesField = '';
				$insertShippingValuesValue = '';
				if (ss_optionExists('Shop Edit Orders')) {
					$insertShippingValuesField = ',or_shipping_values';
					$insertShippingValuesValue = ",'".$shippingValuesSerialized."'";
				}
				
				// To make things easier, we look to see if the customer has any credit
				// card details still stored.. if so, copy them across
				$CreditCardCheck = getRow("
					SELECT * FROM shopsystem_orders, transactions
					WHERE or_us_id = $usID
						AND or_tr_id = tr_id
						AND tr_payment_details_szln IS NOT NULL
					ORDER BY tr_id DESC
					LIMIT 1
				");
				
				//if (!strlen($this->ATTRIBUTES['tr_id'])) {				
					//$asset->cereal['AST_SHOPSYSTEM_DISPLAY_CURRENCY']
					//ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_DISPLAY_CURRENCY',554);
						// asset->cereal['AST_SHOPSYSTEM_DISPLAY_CURRENCY']
					$displayCurrency = $this->getDisplayCurrency();
					$currency = getRow("SELECT * FROM countries WHERE cn_currency_code LIKE '".$displayCurrency['CurrencyCode']."'");
					$prepareTransaction = new Request("WebPay.PreparePayment", 
						array(	'tr_currency_link' => $currency['cn_id'], 
								'tr_client_name' => '',)
					);
					
					$this->ATTRIBUTES['tr_id'] = $prepareTransaction->value['tr_id'];
					$this->ATTRIBUTES['tr_token'] = $prepareTransaction->value['tr_token'];
				//}

				if ($CreditCardCheck !== null) {
					// We have the details, copy them across
					$Q_InsertDetails = query("
						UPDATE transactions
						SET tr_payment_details_szln = '".escape($CreditCardCheck['tr_payment_details_szln'])."'
						WHERE tr_id = {$this->ATTRIBUTES['tr_id']}
					");				
				}
				
				if( ($usID == 1605)		// auto mark paid not shipped
				 || ($usID == 19418) )

					$Q_InsertOrder = query("
							INSERT INTO shopsystem_orders 
							(or_us_id,or_tr_id, or_as_id, or_shipping_details, 
								or_total, or_purchaser_email, or_recorded, or_paid_not_shipped,
								or_purchaser_firstname, or_purchaser_lastname, or_basket,
								or_details, or_country, or_site_folder $insertDiscountCodeField 
								$insertShippingValuesField
							)
							VALUES
							($usID,{$this->ATTRIBUTES['tr_id']},{$assetID}, '$shippingDetailsSerialized', 
								'$orTotal', '$email', Now(), Now(),
								'$firstName', '$lastName', '$sessionBasket',
								'$basket', $countryID, '{$GLOBALS['cfg']['folder_name']}' $insertDiscountCodeValue
								$insertShippingValuesValue
							)
					");
				else
					$Q_InsertOrder = query("
							INSERT INTO shopsystem_orders 
							(or_us_id,or_tr_id, or_as_id, or_shipping_details, 
								or_total, or_purchaser_email, or_recorded, 
								or_purchaser_firstname, or_purchaser_lastname, or_basket,
								or_details, or_country, or_site_folder $insertDiscountCodeField 
								$insertShippingValuesField
							)
							VALUES
							($usID,{$this->ATTRIBUTES['tr_id']},{$assetID}, '$shippingDetailsSerialized', 
								'$orTotal', '$email', Now(),
								'$firstName', '$lastName', '$sessionBasket',
								'$basket', $countryID, '{$GLOBALS['cfg']['folder_name']}' $insertDiscountCodeValue
								$insertShippingValuesValue
							)
					");

//				$enterCurrency = $this->getEnterCurrency();
//				$currency = getRow("SELECT * FROM countries WHERE cn_currency_code LIKE '".$enterCurrency['CurrencyCode']."'");
					
				$updateTransaction  = new Request("WebPay.PreparePayment", array(
					'tr_id' => $this->ATTRIBUTES['tr_id'], 
					'tr_total' => $_SESSION['Shop']['Basket']['Total'], 
					'tr_currency_link' =>$currency['cn_id'], 
					'tr_client_name' =>$firstName.' '.$lastName
				));			

				$Q_SetPaymentMethod = query("
					UPDATE transactions
					SET tr_payment_method = 'WebPay_CreditCard_Manual'
					WHERE tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
				");
				
				require('inc_completeOrder.php');
				
				$normalSite = ss_withTrailingSlash($GLOBALS['cfg']['plaintext_server']);
				$backURL = ss_URLEncodedFormat("{$normalSite}$assetPath/Service/SimpleCompleted/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID");
				
				$secureSite = ss_withTrailingSlash($GLOBALS['cfg']['secure_server']);
				
				$accessCode = '';
				if (array_key_exists('AccessCode', $_REQUEST))
					$accessCode = $_REQUEST['AccessCode'];
				else if (array_key_exists('AccessCode', $_SESSION)) 
					$accessCode = $_SESSION['AccessCode'];
					
				if( $_SESSION['Shop']['Basket']['Total'] > 0 )
					location($secureSite."index.php?act=WebPay.ByCreditCard&Edit=1&AccessCode=$accessCode&tr_id={$this->ATTRIBUTES['tr_id']}&tr_token={$this->ATTRIBUTES['tr_token']}&us_id=$usID&BackURL={$backURL}&Type=Shop&as_id={$assetID}");
				else
				{
											// skip payment page
					query( "Update transactions set 
							tr_total = 0,
							tr_charge_total = '0.00',
							tr_bank = 0,
							tr_completed = 1,
							tr_timestamp = now(),
							tr_payment_method = 'Direct'
						where tr_id = {$this->ATTRIBUTES['tr_id']} and tr_token = '{$this->ATTRIBUTES['tr_token']}'" );

					$or_id = getField( "select or_id from shopsystem_orders where or_tr_id = {$this->ATTRIBUTES['tr_id']}" );
					doOrderSheetSync( $or_id, $_SESSION['Shop']['Basket']['Products'] );

					// reserve products
					for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
					{
						$stockCode = $_SESSION['Shop']['Basket']['Products'][$index]['Product']['pro_stock_code'];
						$qty = $_SESSION['Shop']['Basket']['Products'][$index]['Qty'];

						if( $usID == 1605 )
						{
							$Q_UpdateProductOption = query("
								UPDATE shopsystem_product_extended_options
								SET pro_stock_unavailable = pro_stock_unavailable - $qty
								WHERE pro_stock_code = '$stockCode'
								");

							ss_audit( 'update', 'Products', $_SESSION['Shop']['Basket']['Products'][$index]['Product']['pr_id'],
								"Broken Box order {$this->ATTRIBUTES['tr_id']}, unavailable stock less ".$qty );
						}
						else
						{
							$Q_UpdateProductOption = query("
								UPDATE shopsystem_product_extended_options
								SET pro_stock_available = pro_stock_available - $qty
								WHERE pro_stock_code = '$stockCode'
								");

							ss_audit( 'update', 'Products', $_SESSION['Shop']['Basket']['Products'][$index]['Product']['pr_id'],
								"Stock xfer order {$this->ATTRIBUTES['tr_id']}, stock less ".$qty );
						}
					}

					location("{$normalSite}$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID");
				}
			}
			else		// existing order
			{

				// Redlane wanted this in a separate field. Might as well do it for all shops
				$updateDiscountCodeFieldValue = '';
				if (ss_optionExists('Shop Discount Codes')) {
					if ($_SESSION['Shop']['DiscountCode'] !== null) {
						$updateDiscountCodeFieldValue = ", or_discount_code = '".escape($_SESSION['Shop']['DiscountCode'])."'";
					}
				}
	
				$updateShippingValuesFieldValue = '';
				if (ss_optionExists('Shop Edit Orders')) {
					$updateShippingValuesFieldValue = ",or_shipping_values = '".$shippingValuesSerialized."'";
				}
				
				//if (!strlen($this->ATTRIBUTES['tr_id'])) {				
					//$asset->cereal['AST_SHOPSYSTEM_DISPLAY_CURRENCY']
					//ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_DISPLAY_CURRENCY',554);
						// asset->cereal['AST_SHOPSYSTEM_DISPLAY_CURRENCY']
					/*$displayCurrency = $this->getDisplayCurrency();
					$currency = getRow("SELECT * FROM countries WHERE cn_currency_code LIKE '".$displayCurrency['CurrencyCode']."'");
					$prepareTransaction = new Request("WebPay.PreparePayment", 
						array(	'tr_currency_link' => $currency['cn_id'], 
								'tr_client_name' => '',)
					);*/
					
					//$this->ATTRIBUTES['tr_id'] = $prepareTransaction->value['tr_id'];
					//$this->ATTRIBUTES['tr_token'] = $prepareTransaction->value['tr_token'];
				//}
			
				$Q_UpdateOrder = query("
						UPDATE shopsystem_orders 
						SET 
							or_shipping_details = '$shippingDetailsSerialized', 
							or_total = '$orTotal',
							or_purchaser_email = '$email',
							or_purchaser_firstname = '$firstName',
							or_purchaser_lastname = '$lastName',
							or_basket = '$sessionBasket',
							or_details = '$basket'
							$updateDiscountCodeFieldValue
							$updateShippingValuesFieldValue
						WHERE or_id = {$_SESSION['Shop']['EditingOrder']}
				");

				$OrderID = $_SESSION['Shop']['EditingOrder'];
				
				$OrderTransactionID = getRow("
					SELECT or_tr_id, or_site_folder FROM shopsystem_orders
					WHERE or_id = {$_SESSION['Shop']['EditingOrder']}
				");
				
				$this->ATTRIBUTES['tr_id'] = $OrderTransactionID['or_tr_id'];
				
//				$enterCurrency = $this->getEnterCurrency();
//				$currency = getRow("SELECT * FROM countries WHERE cn_currency_code LIKE '".$enterCurrency['CurrencyCode']."'");
				$currencyLink = $Transaction['tr_currency_link'];
				$currency = getRow("SELECT * FROM countries WHERE cn_id = $currencyLink" );


				if( strlen( $Order['or_reshipment'] ) )
					$total = 0;
				else
					$total = $_SESSION['Shop']['Basket']['Total'];

				ss_log_message( "Admin saving order {$this->ATTRIBUTES['tr_id']}, Total is $total, currency is {$currency['cn_currency_code']}" );

				$updateTransaction  = new Request("WebPay.PreparePayment", array(
					'tr_id' => $this->ATTRIBUTES['tr_id'],
					'tr_total' => $total,
					'tr_currency_link' =>$currencyLink,
					'tr_client_name' =>$firstName.' '.$lastName
				));			

				require('inc_completeOrder.php');
				
				$secureSite = ss_withTrailingSlash($GLOBALS['cfg']['secure_server']);
	
				$accessCode = '';
				if (array_key_exists('AccessCode', $_REQUEST))
					$accessCode = $_REQUEST['AccessCode'];
				else if (array_key_exists('AccessCode', $_SESSION)) 
					$accessCode = $_SESSION['AccessCode'];			

				$needsShipping = false;

				// scope?
				$Q_Clean = query("
					DELETE FROM ordered_products WHERE op_or_id = $OrderID
				");
				
				// Record the order into separate fields...
				foreach($_SESSION['Shop']['Basket']['Products'] as $product) {
					$Q_InsertOrderProduct = query("
						INSERT INTO ordered_products
							(op_or_id, op_pr_id, op_stock_code, op_quantity, op_price_paid, op_pr_name, op_site_folder)
						VALUES 
							($OrderID, {$product['Product']['pr_id']}, '".escape($product['Product']['pro_stock_code'])."',
							{$product['Qty']}, {$product['Product']['Price']}, '".escape($product['Product']['pr_name'])."',
							'".escape($OrderTransactionID['or_site_folder'])."'
						)
					");
				}

				$StockCodesShipped = array();

				for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
				{
					$entry = $_SESSION['Shop']['Basket']['Products'][$index];

					if( array_key_exists( 'Shipped', $entry ) and count( $entry['Shipped'] ) > 0 )
						$StockCodesShipped[] = $entry['Product']['pro_stock_code'];
				}

				doOrderSheetSync( $Order['or_id'], $_SESSION['Shop']['Basket']['Products'] );

/*				if( $needsShipping )	*/
				if( false )
				{
					$transaction = getRow("select * from transactions where tr_id = ".$this->ATTRIBUTES['tr_id'] );
					$this->ATTRIBUTES['tr_token'] = $transaction['tr_token'];
					location("/index.php?act=Security.ChooseShipping&NextAction=ShopSystem.ViewOrder&or_id={$OrderID}&AccessCode=$accessCode&tr_id={$this->ATTRIBUTES['tr_id']}&tr_token={$this->ATTRIBUTES['tr_token']}&us_id=$usID&BackURL={$backURL}&Type=Shop&as_id=".$asset->getID());
				}
				else
					location($secureSite."index.php?act=ShopSystem.ViewOrder&AccessCode=$accessCode&or_id={$OrderID}&tr_id={$this->ATTRIBUTES['tr_id']}&as_id=".$asset->getID());	
				
			}
			
			
		}
	}
	
	// Load the shipping fields with our current values
	ss_paramKey($_SESSION['Shop'],'ShippingDetails',array());
	foreach($shipping->fieldSet->fields as $fieldName => $field) {
		if (array_key_exists($fieldName,$_SESSION['Shop']['ShippingDetails'])) {
			$shipping->fieldSet->fields[$fieldName]->value = $_SESSION['Shop']['ShippingDetails'][$fieldName];
		}
	}
	
	// Load the purchaser fields with our current values
	ss_paramKey($_SESSION['Shop'],'PurchaserDetails',array());
	foreach($userAdmin->fields as $fieldName => $field) {
		if (array_key_exists($fieldName,$_SESSION['Shop']['PurchaserDetails'])) {
			$userAdmin->fields[$fieldName]->value = $_SESSION['Shop']['PurchaserDetails'][$fieldName];
		}
	}
?>
