<?php

	if ($_SESSION['Shop']['EditingOrder'] != null)
		$Order = getRow("
			SELECT * FROM shopsystem_orders
			WHERE or_id = {$_SESSION['Shop']['EditingOrder']}
			");
	else
		die;

	if (array_key_exists("SaveAddress", $this->ATTRIBUTES))
	{

//		print_r( $this->ATTRIBUTES);
//		die;

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

		$OrderTransactionID = getRow("
			SELECT or_tr_id, or_site_folder FROM shopsystem_orders
			WHERE or_id = {$_SESSION['Shop']['EditingOrder']}
		");
		
		$this->ATTRIBUTES['tr_id'] = $OrderTransactionID['or_tr_id'];

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
						/*
						if( $fieldName == '0_50A4' )
						{
							print_r( $userAdmin );
							print_r( $userAdmin->getFieldDisplayValue($field->name) );
							die;
						}
						*/
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

			$shippingDetailsSerialized = escape(serialize(array('ShippingDetails' => $shippingDetails, 'PurchaserDetails' => $purchaserDetails)));
			$shippingValuesSerialized = escape(serialize($shippingValues));
			
			$assetID = $this->asset->getID();
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

			$Q_UpdateOrder = query("
					UPDATE shopsystem_orders 
					SET 
						or_shipping_details = '$shippingDetailsSerialized', 
						or_purchaser_email = '$email',
						or_purchaser_firstname = '$firstName',
						or_purchaser_lastname = '$lastName'
						$updateDiscountCodeFieldValue
						$updateShippingValuesFieldValue
					WHERE or_id = {$_SESSION['Shop']['EditingOrder']}
			");

			$secureSite = ss_withTrailingSlash($GLOBALS['cfg']['secure_server']);

			$accessCode = '';
			if (array_key_exists('AccessCode', $_REQUEST))
				$accessCode = $_REQUEST['AccessCode'];
			else if (array_key_exists('AccessCode', $_SESSION)) 
				$accessCode = $_SESSION['AccessCode'];			

			location($secureSite."index.php?act=ShopSystem.ViewOrder&AccessCode=$accessCode&or_id={$_SESSION['Shop']['EditingOrder']}&tr_id={$this->ATTRIBUTES['tr_id']}&as_id=".$asset->getID());	
		}
	}		// end of address edits

	if (array_key_exists("UpdateFreight", $this->ATTRIBUTES)) {

		$OrderID = $_SESSION['Shop']['EditingOrder'];

		# sanitise and save $_POST['FreightAmount'] as ['Basket']['Freight']['Amount']

		extract( $Order );

		$basket = unserialize( $or_basket );

		$exclShipping = $_SESSION['Shop']['Basket']['Freight']['Amount'] = $basket['Basket']['Freight']['Amount'] = (int)$_POST['FreightAmount'];

		$total = $_SESSION['Shop']['Basket']['Total'] = $basket['Basket']['Total'] = $basket['Basket']['SubTotal'] + (int)$_POST['FreightAmount'];

		if( strlen( $Order['or_reshipment'] ) )
			$total = 0;

		Query("update transactions set tr_total = $total, tr_charge_total = '&euro;$total EUR', tr_excl_shipping = $exclShipping where tr_id = $or_tr_id" );
	}

	if (array_key_exists("SaveBasket", $this->ATTRIBUTES))
	{
		$usID = $_SESSION['Shop']['OrderingFor'];

		$assetID = $this->asset->getID();
		$sessionBasket = escape(serialize($_SESSION['Shop']));
		$orTotal = $this->formatPrice('display',$_SESSION['Shop']['Basket']['Total']);

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

		$updateDiscountCodeFieldValue = '';
		if (ss_optionExists('Shop Discount Codes')) {
			if ($_SESSION['Shop']['DiscountCode'] !== null) {
				$updateDiscountCodeFieldValue = ", or_discount_code = '".escape($_SESSION['Shop']['DiscountCode'])."'";
			}
		}

		$Q_UpdateOrder = query("
				UPDATE shopsystem_orders 
				SET 
					or_total = '$orTotal',
					or_basket = '$sessionBasket',
					or_details = '$basket'
					$updateDiscountCodeFieldValue
				WHERE or_id = {$_SESSION['Shop']['EditingOrder']}
		");

		$OrderID = $_SESSION['Shop']['EditingOrder'];
		
		$OrderTransactionID = getRow("
			SELECT or_tr_id, or_site_folder FROM shopsystem_orders
			WHERE or_id = {$_SESSION['Shop']['EditingOrder']}
		");
		
		$this->ATTRIBUTES['tr_id'] = $OrderTransactionID['or_tr_id'];
		
		$enterCurrency = $this->getEnterCurrency();
		$currency = getRow("SELECT * FROM countries WHERE cn_currency_code LIKE '".$enterCurrency['CurrencyCode']."'");
			

		if( strlen( $Order['or_reshipment'] ) )
			$total = 0;
		else
			$total = $_SESSION['Shop']['Basket']['Total'];

		$updateTransaction  = new Request("WebPay.PreparePayment", array(
			'tr_id' => $this->ATTRIBUTES['tr_id'], 
			'tr_total' => $total,
			'tr_currency_link' =>$currency['cn_id']
/*			'tr_client_name' =>$firstName.' '.$lastName	*/
		));			

		require('inc_completeOrder.php');
		
		$secureSite = ss_withTrailingSlash($GLOBALS['cfg']['secure_server']);

		$accessCode = '';
		if (array_key_exists('AccessCode', $_REQUEST))
			$accessCode = $_REQUEST['AccessCode'];
		else if (array_key_exists('AccessCode', $_SESSION)) 
			$accessCode = $_SESSION['AccessCode'];			

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
		location($secureSite."index.php?act=ShopSystem.ViewOrder&AccessCode=$accessCode&or_id={$OrderID}&tr_id={$this->ATTRIBUTES['tr_id']}&as_id=".$asset->getID());	
	}
?>
