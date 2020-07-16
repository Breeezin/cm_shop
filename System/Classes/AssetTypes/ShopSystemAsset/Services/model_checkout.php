<?php

	// relies on /usr/sbin/p0f -d -l -o /tmp/p0f -f /dev/null
	function getFingerprint( $search_ip )
	{
		define( 'P0F_LOG', '/tmp/p0f' );	// filename
		define( 'CHECK_TIME', 60 );			// seconds
		define( 'SEEK_BACK_BYTES', -10000 );		// last chunk of file to inspect
		define( 'MAX_FINGERPRINT', 1024 );			// max URL to compare

		$fd = fopen( P0F_LOG, "r" );
		if( $fd )
		{
			fseek( $fd, SEEK_BACK_BYTES, SEEK_END );
			fgets( $fd );		 // read in rubbish line
			$ts = '';
			$ip = '';
			$fingerprint = '';

			while( !feof( $fd ) )
			{
				$line = fgets( $fd );

				if( $line[0] == '<' )
				{
					if( $pos = strpos( $line, "> " ) )		// end of ts
					{
						$ts = substr( $line, 1, $pos );
						$line = substr( $line, $pos+2 );
						if( $pos = strpos( $line, ":" ) )	// ip:port
						{
							$ip = substr( $line, 0, $pos );
							if( $search_ip == $ip )
							{
								if( $pos = strpos( $line, "[" ) )
								{
									$line = substr( $line, $pos+1 );
									if( $pos = strpos( $line, "]" ) )	// end of fingerprint
									{
										$fingerprint = substr( $line, 0, $pos );
									}
								}
								if( strlen( $fingerprint ) )
									break;
							}
						}
					}
				}
			}
			fclose( $fd );
			return $fingerprint;
		}
		else
			return NULL;
	}// end function

	//print_r( $_POST );



 	$local_tax = $_SESSION['ForceCountry'][ 'cn_tax_x100' ];
 
	if( ss_isAdmin() )
	{
		echo "Not as admin please";
		die;
	}

	// load user information if they are logged in
	$usID = ss_getUserID();

	$theUser = getRow("SELECT * FROM users WHERE us_id = ".(int)$usID );

	if( !$theUser )
		$usID = -1;

	unset( $_SESSION['Shop']['Basket']['Discounts']['Account Credit'] );
	if( $usID )
	{
		$previousOrders = getField( "select count(*) from shopsystem_orders
												JOIN transactions ON tr_id = or_tr_id 
											where or_us_id = $usID
												AND tr_completed = 1
												and or_cancelled IS NULL
												and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );

		// refresh this
		$_SESSION['User']['us_credit_from_gateway_option'] = $theUser['us_credit_from_gateway_option'];
		if( ( $_SESSION['User']['us_account_credit'] = $theUser['us_account_credit'] ) != 0 )
			if( $foo = getCurrencyEntry( $theUser['us_credit_from_gateway_option'] ) )
				$_SESSION['Shop']['Basket']['Discounts']['Account Credit'] = -$_SESSION['User']['us_account_credit'] * ss_getExchangeRate($foo['po_currency'], getDefaultCurrencyCode( ) );
		ss_log_message_r( "_SESSION", $_SESSION );
	}
	else
		$previousOrders = 0;

	// check for stock levels again...

	$onHold = false;
	$notes = array();
	$altered = false;
	$definitely_available = true;

	// split out combo products NOW.

	$splitComboBasket = array();

//	ss_log_message( 'Basket' );
//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket']['Products'] );

	if( array_key_exists( 'Shop', $_SESSION )
	 &&  array_key_exists( 'Basket', $_SESSION['Shop'] )
	 &&  array_key_exists( 'Products', $_SESSION['Shop']['Basket'] )
	 && is_array( $_SESSION['Shop']['Basket']['Products'] ) )
	{
		ss_log_message( "Looking for combo's in basket" );
		foreach ($_SESSION['Shop']['Basket']['Products'] as $index => $aProduct)
		{
			if (array_key_exists( 'pr_combo', $aProduct['Product'] ) && $aProduct['Product']['pr_combo'])
			{
				$notes[] = "Splitting combo product up {$aProduct['Product']['pr_id']}:{$aProduct['Product']['pr_name']}";

				$numParts = getField( "select sum(cpr_qty) from shopsystem_combo_products, shopsystem_products, shopsystem_product_extended_options
						WHERE cpr_element_pr_id = {$aProduct['Product']['pr_id']}
							AND cpr_pr_id = pr_id
							AND pro_pr_id = pr_id ");

				if( $numParts > 0 )
				{
					$eachPrice = $aProduct['Product']['Price'] / $numParts;

					if( $eachPrice == 0 )		// free?
						$eachIncludedFreight = 0;
					else
						$eachIncludedFreight = includedFreight( $aProduct['Product'], $_SESSION['ForceCountry']['cn_id'] ) * ss_getExchangeRate( 'USD', getDefaultCurrencyCode( ) ) / $numParts;

//						if( $local_tax > 0 )
//							$eachPrice = $eachPrice / (1+$local_tax/10000);

					ss_log_message( "Combo total is {$aProduct['Product']['Price']}, each is $eachPrice, each included_freight is $eachIncludedFreight" );
					$notes[] = "Combo total is {$aProduct['Product']['Price']}, each is $eachPrice, each included_freight is $eachIncludedFreight";

					// Need to find all the products in the combo and then add to the basket
					$Q_ComboProducts = query("
						SELECT * FROM shopsystem_combo_products, shopsystem_products, shopsystem_product_extended_options
						WHERE cpr_element_pr_id = {$aProduct['Product']['pr_id']}
							AND cpr_pr_id = pr_id
							AND pro_pr_id = pr_id
						");

					while ($cp = $Q_ComboProducts->fetchRow())
					{
						// Grab each product and make a new entry in the basket
						$key = $cp['pr_id'].'_'.$cp['pro_id'];
						$found = false;
						foreach($splitComboBasket as $nbIndex => $nbProduct)
						{
							if ($nbProduct['Key'] == $key)
							{
								$splitComboBasket[$nbIndex]['Qty'] += $cp['cpr_qty']*$aProduct['Qty'];
								$found = true;
								break;	
							}	
						}
						if (!$found)
						{
							$product = getRow("
								SELECT pr_id,pr_short,pr_name,pro_uuids, pro_stock_code,
									NULL as pro_price,NULL as pro_special_price,pro_member_price,
									pr_ve_id
								FROM shopsystem_products,shopsystem_product_extended_options
								WHERE pr_id = ".safe($cp['pr_id'])."
									AND pr_id = pro_pr_id
									AND pro_id = ".safe($cp['pro_id'])."
								");

							if ($product !== null)
							{
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
								$product['Price'] = $eachPrice;
								$product['included_freight'] = $eachIncludedFreight;
								$product['pro_price'] = $eachPrice;

								$newProduct = array(
									'Key'	=>	$key,
									'Qty'	=>	$cp['cpr_qty']*$aProduct['Qty'],
									'Product'	=>	$product,
									);
								if( array_key_exists( 'AddService', $aProduct ) )
									$newProduct['AddService'] = $aProduct['AddService'];
								array_push($splitComboBasket,$newProduct);
							}
							else
								ss_log_message( "Unable.2 to split combo ".$aProduct['Product']['pr_id'] );
						}
					}
				}
				else
					ss_log_message( "Unable.1 to split combo ".$aProduct['Product']['pr_id'] );
			}
			else
			{
				// NOT a combo product, merge it into the array, fix included_freight now
				$found = false;
				foreach($splitComboBasket as $nbIndex => $nbProduct)
				{
					if ($nbProduct['Key'] == $aProduct['Key'])
					{
						$splitComboBasket[$nbIndex]['Qty'] += $aProduct['Qty'];
						$found = true;
						break;	
					}	
				}				
				if (!$found)
				{
					if( $aProduct['Product']['Price'] > 0 )
							$aProduct['Product']['included_freight'] = includedFreight( $aProduct['Product'], $_SESSION['ForceCountry']['cn_id'] ) * ss_getExchangeRate( 'USD', getDefaultCurrencyCode( ) );
						else
							$aProduct['Product']['included_freight'] = 0;

						array_push($splitComboBasket,$aProduct);	
					}
				}
			}

			ss_log_message( 'splitComboBasket' );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $splitComboBasket );

			//foreach($_SESSION['Shop']['Basket']['Products'] as $ind=>$aProduct)
			foreach($splitComboBasket as $ind=>$aProduct)
			{
				if( array_key_exists( 'Product', $aProduct )
				 && array_key_exists( 'pr_id', $aProduct['Product'] ) )
				{
					$ProductOption = getRow("
						SELECT * FROM shopsystem_products, shopsystem_product_extended_options
						WHERE pro_pr_id = pr_id and pro_pr_id = '{$aProduct['Product']['pr_id']}'
					");

					if( array_key_exists( 'Qty', $aProduct)
					 && ($aProduct['Qty'] > 0)
					 && $ProductOption['pro_stock_available'] < $aProduct['Qty'] )		// all gone, someone else has grabbed them.  redirect back.
					{
						ss_log_message( "Checkout: reducing basket amount of pr_id ".$aProduct['Product']['pr_id']." from ".$_SESSION['Shop']['Basket']['Products'][$ind]['Qty']." to ".$ProductOption['pro_stock_available'] );
						if( $ProductOption['pro_stock_available'] <= 0 )
							$_SESSION['Shop']['Basket']['Products'][$ind]['Qty'] = 0;
						else
							$_SESSION['Shop']['Basket']['Products'][$ind]['Qty'] = $ProductOption['pro_stock_available'];

						$altered = true;
					}

					if( array_key_exists( 'Qty', $aProduct) && ($aProduct['Qty'] > 0) )
					{
						if( $ProductOption['pro_typical_daily_sales'] )
						{
							$days_held = $GLOBALS['cfg']['DaysStockHeld'];
							$ProductOption['pro_stock_available'] -= $ProductOption['pro_typical_daily_sales']*$days_held;
						}
						else
						{
							$ProductOption['pro_stock_available'] -= 15;
						}

						ss_log_message( "Checkout: reduced available to ".$ProductOption['pro_stock_available'] );

						/// need to make this clearer to the customer....

						if( $ProductOption['pro_stock_available'] < $aProduct['Qty'] )
							$definitely_available = false;
					}

					if( array_key_exists( 'pr_quote_shipping', $aProduct['Product']) && ($aProduct['Product']['pr_quote_shipping'] == 1 ) )
					{
						$notes[] = "Please email quote for shipping {$aProduct['Product']['pr_name']}";
						$onHold = true;
					}
				}
			}
		}
		else
			locationRelative("$assetPath/Service/Checkout");

		if( $altered )
			locationRelative("$assetPath/Service/Checkout");

		// person wants to check out
		if (array_key_exists('Do_Service', $this->ATTRIBUTES) )
		{
			// needs a reload rather than actually checking out
			if( $this->ATTRIBUTES['Do_Service'] == 'Reload' )
			{
				$userAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);	
				$shipping->fieldSet->loadFieldValuesFromForm($this->ATTRIBUTES);
		//		ss_DumpVarDie( $userAdmin );
			}
			else
			{
				$errors = array();

				// check email address DISABLED
				if( false && ( !array_key_exists( 'email_verified', $_SESSION ) or ($_SESSION['email_verified'] != $this->ATTRIBUTES['us_email']) )
				  && !strpos( $this->ATTRIBUTES['us_email'], "admin.com" ) )
				{

					require_once( "System/Libraries/SMTPVerify/SMTPVerify.php" );
					$sender = 'admin@acmerockets.com';
					$SMTP_Validator = new SMTP_validateEmail();
					$SMTP_Validator->debug = true;
					$results = $SMTP_Validator->validate(array($this->ATTRIBUTES['us_email']), $sender);

					// check user whether he/she is in the db already

					if( !$results[$this->ATTRIBUTES['us_email']] )
					{
						ss_log_message( "invalid ".$this->ATTRIBUTES['us_email'] );
						$errors['Password'] = array("Your email address is unusable, please provide another<br />I got this >".$results[$this->ATTRIBUTES['us_email'].'.error']);
					}
					else
					{
						$Q = getRow( "select count(*) as count from unusable_emails where email_address like '".$this->ATTRIBUTES['us_email']."'" );
						if( $Q['count'] > 0 )
						{
							$errors['Password'] = array("Your email address is unreachable, please provide another");
						}
						else
						{
							ss_log_message( "valid ".$this->ATTRIBUTES['us_email'] );
							$_SESSION['email_verified'] = $this->ATTRIBUTES['us_email'];
						}
					}
				}

				// check login details
				if($usID >= 0)
				{
					if (ss_optionExists('Shop Checkout No Password')) {
						$theUser = getRow("SELECT * FROM users WHERE us_email LIKE '".escape($this->ATTRIBUTES['us_email'])."'");				
						if (strlen($theUser['us_id'])) {
							$tempE = array();
							ss_login($theUser['us_id'], $tempE);
							$userAdmin->primaryKey = $theUser['us_id'];
						} else {
							$userAdmin->primaryKey = $usID;
						}
						
					}  else {
						$userAdmin->primaryKey = $usID;
					}
					$this->ATTRIBUTES['us_id'] = $usID;						
					$this->ATTRIBUTES['DoAction'] = 'Yes';						
					$userAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);	
					
					// Validate and then write to the database		     		
					//$errors = $userAdmin->update();				
					$errors = array_merge( $errors, $userAdmin->validate());

					if (!count($errors)) {
						$userAdmin->update();		
						$loginerror = array();		
						ss_login($userAdmin->primaryKey, $loginerror);
					}
				}
				else		// not logged in yet, log them in?
				{
					$Q_User = getRow("SELECT * FROM users WHERE us_email LIKE '".escape($this->ATTRIBUTES['us_email'])."'");			
					if (strlen($Q_User['us_id']))
					{
						ss_log_message_r( "old user from email ", $Q_User );
						$this->param('us_password','');
						if (!ss_optionExists('Shop Checkout No Password'))
							if (strlen($Q_User['us_password']) and strtolower($Q_User['us_password']) != strtolower($this->ATTRIBUTES['us_password'])) 
							{
								// If they have a password, they must enter the correct one..
								$errors['Password'] = array('Please enter the correct password for your account.');
							}

						if (!count($errors))
						{
							// We should check that they are not an adminstrator account,
							// as this would be a security risk since passwords are not checked
							// so we don't want to log them in as a user
							$Q_UserGroups = query("
								SELECT * FROM user_user_groups
								WHERE uug_us_id = {$Q_User['us_id']}
							");
							while ($ug = $Q_UserGroups->fetchRow()) {
								if ($ug['uug_ug_id'] == 1) {
									$errors['Password'] = array('Please use a different email address.');
									break;
								}	
							}
						}
						
						if (!count($errors))
						{
							$userAdmin->primaryKey = $Q_User['us_id'];				
							$usID = $Q_User['us_id'];
							$this->ATTRIBUTES['us_id'] = $usID;						
							$this->ATTRIBUTES['DoAction'] = 'Yes';					
							$userAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);	
							$errors = array_merge($errors, $userAdmin->validate() );

							if (!count($errors))
								$userAdmin->update();		

							$errorsTemp = '';
							$usID = $userAdmin->primaryKey;
							ss_login($userAdmin->primaryKey,$errorsTemp);

							if( $usID )
								$previousOrders = getField( "select count(*) from shopsystem_orders
																		JOIN transactions ON tr_id = or_tr_id 
																	where or_us_id = $usID
																		AND tr_completed = 1
																		and or_cancelled IS NULL
																		and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );

							// update the basket again to reflect the currently logged in user's account
							$argh = new Request('Asset.Display',array(
								'as_id'	=>	$asset->getID(),
								'Service'	=>	'UpdateBasket',
								'AsService'	=>	true,
								'Mode'		=>	'Refresh',
							));		
						}
					}
					else		// new user
					{
						$temp = new Request("Security.Sudo",array('Action'=>'start'));
						$this->ATTRIBUTES['DoAction'] = 'Yes';
						$userAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);			
						// Validate and then write to the database		
						$errors = array_merge($errors, $userAdmin->validate() );

						if (!count($errors))
						{
							$userAdmin->insert();		
							$usID = $userAdmin->primaryKey;
							ss_log_message( "New user ID is $usID" );
							$errorsTemp = '';
							ss_login($usID,$errorsTemp);

							if( $usID )
								$previousOrders = getField( "select count(*) from shopsystem_orders
													JOIN transactions ON tr_id = or_tr_id 
												where or_us_id = $usID
													AND tr_completed = 1
													and or_cancelled IS NULL
													and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );
						}
						
						$temp = new Request("Security.Sudo",array('Action'=>'stop'));
					}
				}
				
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $errors );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this->ATTRIBUTES );

				$shipping->fieldSet->loadFieldValuesFromForm($this->ATTRIBUTES);
				$errors = array_merge($errors,$shipping->fieldSet->validate());	

				if (!count($errors))			// no errors, continue with save
				{
					// Add customer user group
					if ($this->ATTRIBUTES['JoinNewsletter'] == 'checked')
					{
						$Q_Group = getRow("SELECT * FROM user_groups WHERE ug_name LIKE 'Mailing List'");

						$Q_UpdateUser = query("
							UPDATE users
							SET 
								us_html_email	= 1,
								us_no_spam = NULL
							WHERE us_id = {$usID}
						");				
						
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
					}

					$displayCurrency = $this->getDisplayCurrency();
	//				$enterCurrency = $this->getEnterCurrency();
					$chargeCurrency = $this->getChargeCurrency();

					$token = md5(rand());			

					// store the shipping detail and purchaser details
					// because the purchaser details can be changed later.. 
					// so the order store the current values.												
					$purchaserDetails = array();
					$shippingDetails = array();
					$shippingValues = array();

					//ss_log_message_r($userAdmin->fields,'user,',true);
					//ss_log_message_r($shipping->notSelectedFieldNames,'shipping not selected,',true);
					
					foreach($userAdmin->fields as $field) {				
						if (array_search($field->name, $shipping->notSelectedFieldNames) === false) {
							$fieldName = substr($field->name,3);
							if ($fieldName == 'name') {
								$purchaserDetails[$fieldName] = $userAdmin->getFieldDisplayValue($field->name);
								$purchaserDetails['first_name'] = $userAdmin->fields[$field->name]->displayFirstName($userAdmin->fields[$field->name]->value);
								$purchaserDetails['last_name'] = $userAdmin->fields[$field->name]->displayLastName($userAdmin->fields[$field->name]->value);					
							} else {
								$purchaserDetails[$fieldName] = $userAdmin->getFieldDisplayValue($field->name);
							}
						}	
					}
					
					ss_log_message_r( "fields", $shipping );
					foreach($shipping->fieldSet->fields as $field) {
						$shippingValues[$field->name] = $field->value;
						$fieldName = substr($field->name,4);				
						if ($fieldName == 'name') {
							$shippingDetails[$fieldName] = $shipping->fieldSet->getFieldDisplayValue($field->name);
							$shippingDetails['first_name'] = $shipping->fieldSet->fields[$field->name]->displayFirstName($field->value);
							$shippingDetails['last_name'] = $shipping->fieldSet->fields[$field->name]->displayLastName($field->value);					
						} else {
							$shippingDetails[$fieldName] = $shipping->fieldSet->getFieldDisplayValue($field->name);			
						}
					}

					ss_log_message_r( "shippingValues", $shippingValues );
					$cancelled = false;

					//  check chipping country details

					$state_country = $shippingValues['ShDe0_50A4'];
					$pos = strpos( $state_country, "&" );
					if( $pos )
						$country = (int)substr( $state_country, 0, $pos );
					else
						$country = (int)$state_country;

					ss_log_message( "Shipping country = $country" );

					$ShippingCountry = getRow( "Select * from countries where cn_id = $country" );

					// check cn_hold_status for Country
					if( $ShippingCountry[ 'cn_hold_status' ] == 'Yes' )
					{
						ss_log_message( "Country/hold true" );
						$onHold = true;
						$notes[] = "Shipping ".$ShippingCountry[ 'cn_hold_status' ]." has hold status";
					}

					$boxes = 0.0;
					for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
					{
						$entry = $_SESSION['Shop']['Basket']['Products'][$index];
						if ($entry['Qty'] > 0)
						{
							$num_in_box = getField( "select pr0_883_f from shopsystem_products where pr_id = ".$entry['Product']['pr_id'] );
							if( ( $num_in_box > 0 ) && ( ($entry['Product']['pr_ve_id'] == 2) ) )
							{
								if( $num_in_box >= 25 )
									$boxes += $entry['Qty'];
								else
									$boxes += $entry['Qty'] / (25 / $num_in_box);
							}
						}
					}

					$boxes = round( $boxes + 0.499 );

					// embedded business logic here

					ss_log_message( "is ".$ShippingCountry['cn_max_order_boxes']." < $boxes ?" );
					if( $ShippingCountry['cn_max_order_boxes'] && ( $ShippingCountry['cn_max_order_boxes'] > 0 ) && ( $boxes > $ShippingCountry['cn_max_order_boxes'] ) )
					{
						ss_log_message( "Max boxes by country triggered ".$ShippingCountry[ 'cn_max_order_boxes'] );
						$errors['Boxes'] = array('Please order '.$ShippingCountry['cn_max_order_boxes'].' boxes or less');
						return;
					}

					ss_log_message( "User ID $usID has ".$previousOrders." previous orders" );
					ss_log_message( "Ordering ".getDefaultCurrencyCode()." ".$_SESSION['Shop']['Basket']['Total'] );
					ss_log_message( "Converted is EUR ".ss_getExchangeRate(getDefaultCurrencyCode(), 'EUR') * $_SESSION['Shop']['Basket']['Total'] );

				if( !array_key_exists( 'Account Credit', $_SESSION['Shop']['Basket']['Discounts'] )
						|| ( $_SESSION['Shop']['Basket']['Discounts']['Account Credit'] == 0 ) )		// skip people with account credits.
					if( ss_getExchangeRate(getDefaultCurrencyCode(), 'EUR') * $_SESSION['Shop']['Basket']['Total'] < 50 )		// Minimum minimum order quantity
					{
						$errors['Total'] = array('Please order at least 50 Euro or equivalent.');
						return;
					}

				$chosenGatewayOption = getDefaultCurrencyEntry( );
				if( $previousOrders == 0 )		// no paid for orders yet
				{
					if( ( $chosenGatewayOption['po_card_type'] > 0 ) && ( ss_getExchangeRate(getDefaultCurrencyCode(), 'EUR') * $_SESSION['Shop']['Basket']['Total'] > 400.0 ) )
					{
						ss_log_message( "getDefaultCurrencyCode() = ".getDefaultCurrencyCode() );
						ss_log_message( "exch = to EUR = ".ss_getExchangeRate(getDefaultCurrencyCode(), 'EUR') );
						ss_log_message( "EUR total is ".ss_getExchangeRate(getDefaultCurrencyCode(), 'EUR') * $_SESSION['Shop']['Basket']['Total'] );
						$errors['Total'] = array('This appears to be your first order, please order less than &euro;400 or do not use a credit card.');
						ss_log_message_r($errors);
						return;
					}
				}
				else
				{
					// accounts newer than 5 months cannot order more than EUR 1000
					// get oldest order
					$oldestNewerThan4Months = getField( "select min(or_recorded) > NOW() - INTERVAL 4 MONTH from shopsystem_orders
												JOIN transactions ON tr_id = or_tr_id 
											where or_us_id = $usID
												AND tr_completed = 1
												and or_cancelled IS NULL
												and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );

					if( $oldestNewerThan4Months )
					{
						if( ( $chosenGatewayOption['po_card_type'] > 0 ) && ( ss_getExchangeRate(getDefaultCurrencyCode(), 'EUR') * $_SESSION['Shop']['Basket']['Total'] > 1000.0 ) )
						{
							$errors['Total'] = array('Your account is too new, please order less than &euro;1000 or do not use a credit card.');
							ss_log_message_r($errors);
							return;
						}
					}
				}

				$personHold = false;
				if( array_key_exists( 'User', $_SESSION )
				 && array_key_exists( 'us_confirm_receipt', $_SESSION['User'] )
				 && (int) $_SESSION['User']['us_confirm_receipt'] > 0 )
				{
					ss_log_message( "Person/hold true" );
					$personHold = true;
				}

				// code fragment also in System/Classes/AssetTypes/ShopSystemAsset/Services/model_recheckout.php
				if( ( $theUser['us_bl_id'] != -1 ) && ( ($previousOrders == 2) || ($previousOrders == 1) ) )		// NOT whitelisted and 1 or 2 orders previously
				{
					// check to see if previous order all received
					$can_chargeback_this = getField( "select pg_can_chargeback from payment_gateways where pg_id = ".$chosenGatewayOption['po_pg_id'] );

					ss_log_message( "can charge back this order? $can_chargeback_this" );

					$previousOrID = (int) getField( "select max( or_id )
													from shopsystem_orders
														JOIN transactions ON tr_id = or_tr_id 
														left join payment_gateways on tr_bank = pg_id
													where or_us_id = $usID
														AND tr_completed = 1
														AND or_actioned IS NULL
														and or_reshipment IS NULL"
														.($can_chargeback_this?"":" and pg_can_chargeback != 0")		// if they can't charge back this order, ignore earlier ones that can't be charged back
														." and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );

					ss_log_message( "user has 1-2 previous Orders, looking at or_id $previousOrID" );
					$when = getField( "select or_recorded > NOW() - INTERVAL 4 WEEK from shopsystem_orders where or_id = $previousOrID" );

					if( $when )
					{
						ss_log_message( "last order is less than 4 weeks ago" );
						$SheetQ = query( "select * from shopsystem_order_sheets_items where orsi_or_id = $previousOrID" );

						$all_received = true;

						if( $SheetQ->numRows() > 0 )
						{
							ss_log_message( " and has been packed" );
							while( $SheetItems = $SheetQ->fetchRow() )
							{
								ss_log_message( " item {$SheetItems['orsi_stock_code']}" );

								if( strlen( $SheetItems['orsi_no_stock'] ) )
									continue;

								if( getField( "select pr_is_service from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id where pro_stock_code = '{$SheetItems['orsi_stock_code']}'"
											) == 'false' )
								{
									ss_log_message( " isn't a service" );
									if( strlen( $SheetItems['orsi_date_shipped'] ) )
										if( !strlen( $SheetItems['orsi_received'] ) )
										{
											ss_log_message( " has been sent and not received" );
											$all_received = false;
										}
								}
							}
						}
						else
						{
							// anything to pack ?
							$topack = getField( "select count(*) from shopsystem_order_items where oi_or_id = $previousOrID" );

							if( $topack > 0 )
								$all_received = false;
							else
								$all_received = true;
						}

						if( !$all_received )
						{
							$previousTrID = getField( "select or_tr_id from shopsystem_orders where or_id = $previousOrID" );
							$errors['Total'] = array('Please mark off your order '.$previousTrID.' as being received in your members page.');
							return;
						}
					}
					else
						ss_log_message( "last order is more than 12 weeks ago" );

				}

				if( $personHold )
				{
					// check to see if all previous orders all received

					$previousOrdersQ = query( "select or_id, tr_total from shopsystem_orders JOIN transactions ON tr_id = or_tr_id 
													where or_us_id = $usID
													AND tr_completed = 1
													AND or_actioned IS NULL
													and or_reshipment IS NULL
													and ( or_shipped IS NULL OR or_shipped > NOW() - INTERVAL 12 WEEK )
													and or_recorded > NOW() - INTERVAL 12 WEEK
													and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );

					while( $previousOrderRows = $previousOrdersQ->fetchRow() )
					{
						// has this order been completely refunded?
						$fully_refunded = false;

						if( $refunded = GetField( "select sum(rfd_amount) from shopsystem_refunds where rfd_or_id = {$previousOrderRows['or_id']}" ) )
							if( $previousOrderRows['tr_total'] - $refunded < 0.01 )
								$fully_refunded = true;

						if( !$fully_refunded )
						{
							$all_received = true;
							$SheetQ = query( "select * from shopsystem_order_sheets_items where orsi_or_id = {$previousOrderRows['or_id']}" );

							if( $SheetQ->numRows() > 0 )
							{
								ss_log_message( "or_id {$previousOrderRows['or_id']} has been packed" );
								while( $SheetItems = $SheetQ->fetchRow() )
								{
									ss_log_message( " item {$SheetItems['orsi_stock_code']}" );

									if( strlen( $SheetItems['orsi_no_stock'] ) )
										continue;

									if( getField( "select pr_is_service from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id where pro_stock_code = '{$SheetItems['orsi_stock_code']}'"
												) == 'false' )
									{
										ss_log_message( " isn't a service" );
										if( strlen( $SheetItems['orsi_date_shipped'] ) && strlen( $SheetItems['orsi_received'] ) )
											;
										else
										{
											ss_log_message( " has been sent and not received" );
											$all_received = false;
										}
									}
								}
							}
							else
							{
								if( GetField( "select count(*) from shopsystem_order_items where oi_or_id = {$previousOrderRows['or_id']}" ) > 0 )	// somehting to be packed
									$all_received = false;
								else						// nothing to be packed
									$all_received = true;
							}

							if( !$all_received )
							{
								$previousTrID = getField( "select or_tr_id from shopsystem_orders where or_id = {$previousOrderRows['or_id']}" );
								$errors['Total'] = array('Please mark off your order '.$previousTrID.' as being received in your members page.');
								return;
							}
						}
					}
				}

				// check countries order interval
				if( $ShippingCountry[ 'cn_min_reorder_interval'] > 0 )
				{
					// need to check last paid order to make sure it's greater than this number of days prior.
												//	and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)
												// want this?
					$lastOrder = getRow( "select to_days(now()) - to_days(or_recorded) as since, or_id from shopsystem_orders
													JOIN transactions ON tr_id = or_tr_id 
												where or_us_id = {$usID}
													AND tr_completed = 1
													and or_card_denied IS NULL
													and or_reshipment IS NULL
													and or_cancelled IS NULL
												order by or_id desc limit 1" );
					if( $lastOrder )
					{
						if( array_key_exists('since',$lastOrder) && strlen( $lastOrder['since'] ) )
						{
							ss_log_message( "last order was {$lastOrder['since']} days ago" );

							if( $lastOrder['since'] < 4 )
							{
								$errors['Total'] = array('You have ordered again too soon.');
								return;
							}

							if( $lastOrder['since'] < $ShippingCountry[ 'cn_min_reorder_interval'] )
							{
								// check to see if all this order has been marked as received

								$nr = getField( "select count(*) as count from shopsystem_order_sheets_items join shopsystem_products on pr_id = orsi_pr_id where orsi_or_id = {$lastOrder['or_id']} and orsi_no_stock IS NULL and orsi_date_shipped IS NOT NULL and orsi_received IS NULL and pr_is_service = 'false'" );

								ss_log_message( "Packlist list $nr as sent and not received" );

								if( $nr > 0 )
								{
									ss_log_message( "too soon by country triggered ".$ShippingCountry[ 'cn_min_reorder_interval']." days" );
	//								$onHold = true;
	//								$notes[] = "Shipping ".$ShippingCountry[ 'cn_hold_status' ]." order interval";
									$errors['Total'] = array('You have ordered again too soon.');
									return;
								}
							}
						}
					}
				}

				// ss_getExchangeRate( $_SESSION['ForceCountry']['cn_box_tracking_cost_currency'], getDefaultCurrencyCode( ) )
				if( !array_key_exists('us_has_import_license', $theUser) || ( $theUser['us_has_import_license'] == 'false' ) )
					if( $ShippingCountry['cn_max_order_total']*ss_getExchangeRate( $_SESSION['ForceCountry']['cn_box_tracking_cost_currency'], getDefaultCurrencyCode( ) ) < $_SESSION['Shop']['Basket']['Total'] )
					{
						ss_log_message( "Max order by country triggered ".$ShippingCountry[ 'cn_max_order_total'] );
	//					$onHold = true;
	//					$notes[] = "Shipping ".$ShippingCountry[ 'cn_hold_status' ]." total";
						$errors['Total'] = array('Please order less than &euro;'.$ShippingCountry['cn_max_order_total']);
						return;
					}

				if( $theUser['us_bl_id'] > 0 )
				{
					ss_log_message( "Blacklist customer" );
					if( !ss_isAdmin() )
						$_SESSION['Blacklist'] = true;
					$cancelled = true;
				}

				// embedded business logic ends here

				$saveShippingDetailsSerialized = escape(serialize($shippingDetails) );
				ss_log_message( "Save shipping details? '$saveShippingDetailsSerialized'" );
				$thereAlready = getField( "Select count(*) from user_addresses where ua_us_id = $usID and ua_shipping_details = '$saveShippingDetailsSerialized'" );

				if( !$thereAlready )
					query( "insert into user_addresses (ua_us_id, ua_shipping_details, ua_country) values ($usID, '$saveShippingDetailsSerialized', $country)" );

				$shippingDetailsSerialized = escape(serialize(array('ShippingDetails' => $shippingDetails, 'PurchaserDetails' => $purchaserDetails)));
				$shippingValuesSerialized = escape(serialize($shippingValues));

				$assetID = $this->asset->getID();
				$orTotal = $this->formatPrice('display',$_SESSION['Shop']['Basket']['Total']);
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

				$giftmsg = '';
				$this->param('GiftMessage', '');
				if (ss_OptionExists('Gift Message')) {
					$giftmsg = $this->ATTRIBUTES['GiftMessage'];
				}

				$insertShippingValuesField = '';
				$insertShippingValuesValue = '';
				if (ss_optionExists('Shop Edit Orders')) {
					$insertShippingValuesField = ',or_shipping_values';
					$insertShippingValuesValue = ",'".$shippingValuesSerialized."'";
				}
				
				$currency = getRow("SELECT * FROM countries WHERE cn_currency_code LIKE '".$displayCurrency['CurrencyCode']."'");

				// insert into transactions .....
				$prepareTransaction = new Request("WebPay.PreparePayment", 
						array(	'tr_currency_link' => $currency['cn_id'], 
								'tr_client_name' => '',)
					);

				$this->ATTRIBUTES['tr_id'] = $prepareTransaction->value['tr_id'];
				$this->ATTRIBUTES['tr_token'] = $prepareTransaction->value['tr_token'];

				// insert into shopsystem_orders ...

				if( array_key_exists( 'Blacklist', $_SESSION ) && $_SESSION['Blacklist'] && count( $_SESSION['Shop']['Basket']['Products'] ) )
					die;


				$newerBasket = array();
				
				$inclShipping = 0;

				// push array $splitComboBasket into $newerBasket making sure $prices are OK
				for	($index=0;$index<count($splitComboBasket);$index++) {
					$entry = $splitComboBasket[$index];
					if ($entry['Qty'] > 0) {
						
						// Figure out the price to charge

						$prices = $this->getPrice($entry['Product']['pr_id'],NULL,NULL,'Complete');
						if( !array_key_exists( 'Price', $entry['Product'] ) )
							$entry['Product']['Price'] = $prices['MinSellPrice'];

						ss_log_message( "new basket entry" );
						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $entry );
						
						array_push($newerBasket,$entry);
						//$total += $entry['Qty']*$entry['Product']['Price'];
						//$totalUnits += $entry['Qty'];
					}
				}

				ss_log_message( 'newerBasket' );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $newerBasket );


				$exclShipping = $_SESSION['Shop']['Basket']['Freight']['Amount'];
				$Q_UpdateTransaction = query( "Update transactions set tr_incl_shipping = $inclShipping, tr_excl_shipping = $exclShipping
						WHERE tr_id = {$this->ATTRIBUTES['tr_id']}" );

				$_SESSION['Shop']['Basket']['Products'] = $newerBasket;

				require( "checkout_discount.php" );

				// this generates some HTML to stick in the display field.  It gets saved in the DB.  Nuts.
				$result = new Request('Asset.Display',array(
					'Service'	=>	'Basket',
					'as_id'	=>	$this->asset->getID(),
					'ExtraInfo'	=>	$shippingDetails,
					'Style'		=>	'NoInputs',
					'NoHusk'	=>	true,
				));

				// now we need to remove <td class="onlineShopBasketTotalNZ">[^<]*</td>
				$basketHTML = str_replace(chr(10),'',$result->display);
				$basketHTML = preg_replace( '/<td class="onlineShopBasketTotalNZ">[^<]*<\/td>/', '', $basketHTML );

				$orderDetails = array('OrderProducts' =>$_SESSION['Shop']['Basket']['Products'], "BasketHTML" => $basketHTML, 'GiftMessage' => $giftmsg);
				$basketSerialized = escape(serialize($orderDetails));

				$sessionBasket = escape(serialize($_SESSION['Shop']));

				// Redlane wanted this in a separate field. Might as well do it for all shops
				$insertDiscountCodeField = '';
				$insertDiscountCodeValue = '';
				if (ss_optionExists('Shop Discount Codes')) {
					if ($_SESSION['Shop']['DiscountCode'] !== null) {
						$insertDiscountCodeField = ', or_discount_code';
						$insertDiscountCodeValue = ", '".escape($_SESSION['Shop']['DiscountCode'])."'";
					}
				}

				ss_log_message( "inserting new order for user:$usID" );
				$or_site_folder = $GLOBALS['cfg']['folder_name'];
				$Q_InsertOrder = query("
						INSERT INTO shopsystem_orders 
						(or_us_id,or_tr_id, or_as_id, or_shipping_details, 
							or_total, or_purchaser_email, or_recorded, 
							or_purchaser_firstname, or_purchaser_lastname, or_basket,
							or_details, or_site_folder, or_country $insertDiscountCodeField 
							$insertShippingValuesField
						)
						VALUES
						($usID,{$this->ATTRIBUTES['tr_id']},{$assetID}, '$shippingDetailsSerialized', 
							'$orTotal', '$email', Now(),
							'$firstName', '$lastName', '$sessionBasket',
							'$basketSerialized', '$or_site_folder', $country $insertDiscountCodeValue
							$insertShippingValuesValue
						)
				");


/*				if( $theUser['us_permanent_tracking'] > 0 )	*/
				if( array_key_exists( 'TrackingChoice', $_SESSION ) && $_SESSION['TrackingChoice'] )
				{
					ss_log_message( "Tracking enabled for user:$usID" );
					$Q_UpdateOrder = query("
						UPDATE shopsystem_orders
						SET or_tracked_and_traced = now()
						WHERE or_tr_id = {$this->ATTRIBUTES['tr_id']}
					");
				}

				// find the order id.. -_-
				$or_id = getField("SELECT or_id FROM shopsystem_orders WHERE or_tr_id = {$this->ATTRIBUTES['tr_id']}");
				
				$ttlExtraFreight = 0;
				$numProd = 0;
				foreach($newerBasket as $product)
				{
					if( array_key_exists( 'ExtraShipping', $product ) )
						if( $product['ExtraShipping']  > 0 )
							$ttlExtraFreight += $product['ExtraShipping'];

					if( !array_key_exists( 'pr_is_service', $product['Product'] ) || ( $product['Product']['pr_is_service'] == 'false' ) )
						$numProd += $product['Qty'];
				}

				// Record the order into separate fields...
				// need to pro-rata shipping and tracking as needed
				// $exclShipping is extra added on freight
				// TODO you are here.
				$Q_Clean = query("delete from ordered_products where op_or_id = $or_id");
//				if( $numProd == 0 )		// only services?
//					die;
				$tracking = $exclShipping - $ttlExtraFreight;
				if( $numProd > 0 )
					$each_tracking = $tracking / $numProd;
				else
					$each_tracking = 0;
				ss_log_message( "Tracking = $tracking = $exclShipping - $ttlExtraFreight, each $each_tracking = $tracking / $numProd" );

				foreach($newerBasket as $product)
				{
					$extraFreight = 0;
					if( array_key_exists( 'ExtraShipping', $product ) )
						if( $product['ExtraShipping']  > 0 )
							$extraFreight = $product['ExtraShipping'] / $product['Qty'];

					if( !array_key_exists( 'pr_is_service', $product['Product'] ) || ( $product['Product']['pr_is_service'] == 'false' ) )
						$this_each_tracking = $each_tracking;
					else
						$this_each_tracking = 0;

					$fullproduct = getRow( "select * from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id where pro_stock_code = '".escape($product['Product']['pro_stock_code'])."'" );
					// TODO.  this is wrong for combo products, needs to be split across
					$supplierPrice = $fullproduct['pro_supplier_price'] * ss_getExchangeRate( $fullproduct['pro_source_currency'], getDefaultCurrencyCode( ) );
					if( array_key_exists( 'included_freight',  $product['Product'] ) && ( $product['Product']['included_freight'] > 0 ) )
						$incf = $product['Product']['included_freight'];
					else
						$incf = 0;

					$Q_InsertOrderProduct = query("
						insert into ordered_products
							(op_or_id, op_pr_id, op_stock_code, 
							 op_quantity, op_currency_code, op_price_paid,
							 op_supplier_price, op_included_freight, op_extra_freight, op_tracking, 
							 op_pr_name, op_site_folder, op_usd_rate )
						values 
							($or_id, {$product['Product']['pr_id']}, '".escape($product['Product']['pro_stock_code'])."',
							{$product['Qty']}, '".getDefaultCurrencyCode()."', {$product['Product']['Price']},
							$supplierPrice, $incf, $extraFreight, $this_each_tracking,
							'".escape($product['Product']['pr_name'])."', '".escape($or_site_folder)."', ".ss_getExchangeRate( 'USD', getDefaultCurrencyCode() )."
						)
					");
				}
				$totalPrice = $_SESSION['Shop']['Basket']['Total'];

				/*
				if ($enterCurrency != $chargeCurrency) {
					$totalPrice = $totalPrice * ss_getExchangeRate($enterCurrency['CurrencyCode'],$chargeCurrency['CurrencyCode']);
					$totalPrice = sprintf("%01.2f",$totalPrice);
				}
				*/

				$currency = getRow("SELECT * FROM countries WHERE cn_currency_code LIKE '".getDefaultCurrencyCode()."'");

				$orderTotal = $totalPrice;															// tr_order_total doesn't include any credits
				foreach($_SESSION['Shop']['Basket']['Discounts'] as $discount => $amount)
					$orderTotal -= $amount;

				ss_log_message( "inserting new transaction:{$this->ATTRIBUTES['tr_id']} for user:$usID" );
				ss_audit( 'other', 'users', $usID, "User created a new Order ".((int)$this->ATTRIBUTES['tr_id'])." for ".$totalPrice);
				$updateTransaction  = new Request("WebPay.PreparePayment", array(
					'tr_id' => $this->ATTRIBUTES['tr_id'], 
					'tr_total' => $totalPrice, 
					'tr_order_total' => $orderTotal, 
					'tr_currency_link' =>$currency['cn_id'], 
					'tr_client_name' =>$firstName.' '.$lastName,
					'tr_client_email' => $email
				));

				$normalSite = $GLOBALS['cfg']['plaintext_server'];
				$normalSite = ss_withTrailingSlash($normalSite);
				$backURL = ss_URLEncodedFormat("{$normalSite}$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID");
//				$this->param("PaymentOption");

				$secureSite = $GLOBALS['cfg']['secure_server'];
				$secureSite = ss_withTrailingSlash($secureSite);

				$accessCode = '';
				if (array_key_exists('AccessCode', $_REQUEST))
					$accessCode = $_REQUEST['AccessCode'];
				else if (array_key_exists('AccessCode', $_SESSION)) 
					$accessCode = $_SESSION['AccessCode'];

/*
				if ($this->ATTRIBUTES['PaymentOption'] == 'ByConfirm')
					if ($_SESSION['Shop']['Basket']['Total'] > 0)
						die('Invalid order/payment combination');
*/

				if( $this->ATTRIBUTES['Do_Service'] == 'Yes' )		// huh?
				{
					$Q_Transaction = getRow("SELECT * FROM transactions WHERE tr_id = {$this->ATTRIBUTES['tr_id']} AND tr_token = '{$this->ATTRIBUTES['tr_token']}'");
					$Q_Order = getRow("SELECT * FROM shopsystem_orders join users on us_id = or_us_id WHERE or_tr_id = {$Q_Transaction['tr_id']}");

					// get browser ident, ip and fingerprint for later possible adding to blacklist.
					$browserident = addslashes( $_SERVER['HTTP_USER_AGENT'] );
					$ip = $_SERVER['REMOTE_ADDR'];
					$fingerprint = addslashes( @getFingerprint( $ip ) );
					ss_log_message( "ip address:$ip fingerprint:$fingerprint" );

					$Q_Rate = $GLOBALS['commonDB']->query("SELECT * FROM exchange_rate order by er_id desc");
					while( $r = $Q_Rate->fetchRow() )
						$GLOBALS['exchangeRates'][$r['er_source'].'_'.$r['er_destination']] = $r['er_rate'];

					$tmp = escape(serialize( $GLOBALS['exchangeRates'] ) );

/*
					$lastEx = getRow( "select * from OldExchangeRates order by OERID desc limit 1" );
					$exIndex = $lastEx['OERID'];
					if( escape( $lastEx['OERValues'] ) != $tmp )
					{
						// insert another one
						query( "insert into OldExchangeRates (OERValues) values ('$tmp')" );
						$exIndex = getLastAutoIncInsert();
					}
	*/
					$exIndex = 0;

					$usd_rate = ss_getExchangeRate( 'USD', getDefaultCurrencyCode() );

					query( "Update transactions set 
						tr_ip_address = '$ip', tr_browser_ident = '$browserident',
						tr_fingerprint = '$fingerprint', tr_exchange_rate_index = $exIndex,
						tr_timestamp = now(), tr_usd_rate = $usd_rate
					where tr_id = {$this->ATTRIBUTES['tr_id']} and tr_token = '{$this->ATTRIBUTES['tr_token']}'" );

					$fraudScore = 0;	
					$fraudService = '';
					$fraudText = array();

					$b_country = 840;		// default US

					if ( strlen($Q_Order['or_shipping_details']))
					{
						$sdetails = unserialize($Q_Order['or_shipping_details']);
							
						ss_paramKey($sdetails['PurchaserDetails'],'0_50A1','');
						ss_paramKey($sdetails['ShippingDetails'],'0_50A1','');
						
						$billingName = escape(rtrim(ltrim($sdetails['PurchaserDetails']['Name'])));
						$billingAddress = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A1'])));
						
						$shippingName = escape(rtrim(ltrim($sdetails['ShippingDetails']['Name'])));
						$shippingAddress = escape(rtrim(ltrim($sdetails['ShippingDetails']['0_50A1'])));

						$s_state_country = $sdetails['ShippingDetails']['0_50A4'];
						$pos = strpos( $s_state_country, "<BR>" );
						if( $pos !== false )
						{
							$s_state = substr( $s_state_country, 0, $pos );
							$s_country = substr( $s_state_country, $pos + 4 );
						}
						else
						{
							$s_state = $s_state_country;
							$s_country = $s_state_country;
						}

						$b_state_country = $sdetails['PurchaserDetails']['0_50A4'];
						$pos = strpos( $b_state_country, "<BR>" );
						if( $pos !== false )
						{
							$b_state = substr( $b_state_country, 0, $pos );
							$b_country = substr( $b_state_country, $pos + 4 );
						}
						else
						{
							$b_state = $b_state_country;
							$b_country = $b_state_country;
						}

						if ($Q_Order['us_bl_id'] != -1)
						{
							ss_log_message( "Performing blacklist check" );

							$blackListcheck = new Request('shopsystem_blacklist.CheckOrder' , array( 'tr_id' => $Q_Transaction['tr_id'] ) );

							ss_log_message( "shopsystem_blacklist.CheckOrder returning" );
							ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $blackListcheck );

							if( count( $blackListcheck->value ) )
							{
								$fraudScore = $blackListcheck->value[0]['score'];
								$fraudService = "blacklist";
								if( $fraudScore > 20 )
									$onHold = true;

								foreach( $blackListcheck->value as $check )
									$fraudText[] = 'Score '.$check['score'].' as '.$check['note'];
							}
						}
						else
							ss_log_message( "NOT Performing blacklist check" );
					}

					ss_log_message( "fraudScore = $fraudScore" );
					ss_log_message( "fraudService = $fraudService" );
					ss_log_message( "fraudText = " );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fraudText );

					if( $totalPrice <= 0 )		// must have used credit to get this far
					{
						$pg_id = 0;
						$po_id = 0;
					}
					else
					{
						{
							// default
							// function getUserPaymentGateway( $us_id, $billingRecord = NULL, $amount = 0)		// 2 == visa
							$foo = getUserPaymentGateway($usID, NULL, $totalPrice);
							$payrow = $foo['Gateway'];
							if( !$payrow )
							{
								ss_log_message( "Failed to find any gateway for customer" );
								query( "Update shopsystem_orders set or_cancelled = NOW() where or_tr_id = {$this->ATTRIBUTES['tr_id']}" );
								echo $foo['NoChargeBlurb'];
								die;
							}
							else
							{
								$pg_id = $payrow['pg_id'];
								$po_id = $payrow['po_id'];
							}
						}
					}

					query( "update transactions set tr_bank = $pg_id, tr_gateway_option = $po_id where tr_id = {$this->ATTRIBUTES['tr_id']} AND tr_token = '{$this->ATTRIBUTES['tr_token']}'" );

					if( ($totalPrice <= 0) || $payrow['pg_reserve_stock'] )
					{
						foreach($_SESSION['Shop']['Basket']['Products'] as $aProduct) 
						{
							$ProductOption = getRow("
								SELECT * FROM shopsystem_products, shopsystem_product_extended_options
								WHERE pro_pr_id = pr_id and pro_pr_id = '{$aProduct['Product']['pr_id']}' ");

							if ($ProductOption['pro_stock_available'] !== null)
							{
								// If the product option is using the stock level management..
								$Q_UpdateProductOption = query("
									UPDATE shopsystem_product_extended_options
									SET pro_stock_available = ".($ProductOption['pro_stock_available']-$aProduct['Qty'])."
									WHERE pro_id = {$ProductOption['pro_id']}
								");

								ss_audit( 'update', 'Products', $aProduct['Product']['pr_id'], "Order {$this->ATTRIBUTES['tr_id']} on credit, stock less ".$aProduct['Qty'] );

								// Removed by Rex 20081123 at Patricks request
								if( false && ($ProductOption['pr_stock_warning_level'] !== null)
								 && ($ProductOption['pr_stock_warning_level'] <= $ProductOption['pro_stock_available']-$aProduct['Qty'] ) )
									{
									// send off an email to the stock order
									$result = new Request('Email.Send',array(
										'to'	=>	$asset->cereal[$this->fieldPrefix.'ADMINEMAIL'],
										'from'	=>	$asset->cereal[$this->fieldPrefix.'ADMINEMAIL'],
										'subject'	=>	"Product number ".$aProduct['Product']['pr_id']." ".$aProduct['Product']['pr_name']." has reached your minimum stock level",
										'html'	=>	''
									));
									}
							}
						}			
					}

					if( array_key_exists( 'WARNING', $_SESSION ) )
					{
						$onHold = true;
						query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('{$_SESSION['WARNING']}', NOW(), $or_id )" );
					}

					// check product services selected vs default
					foreach ($_SESSION['Shop']['Basket']['Products'] as $index => $aProduct)
					{
						if( !array_key_exists( 'pr_is_service', $aProduct['Product'] ) || ( $aProduct['Product']['pr_is_service'] == 'false' ) )
						{
							// get product options for this ...
							$selectedServices = query( 'select * from product_service_options join shopsystem_products on sv_pr_id_service = pr_id join shopsystem_product_extended_options on pro_pr_id = pr_id where sv_pr_id = '.$aProduct['Product']['pr_id'].' and pr_offline IS NULL' );
							while( $service = $selectedServices->fetchRow() )
							{
								if( $service['pr_service_default'] == "true" )
								{
									if( !array_key_exists('AddService', $aProduct)
										|| !is_array($aProduct['AddService'])
										|| !in_array( $service['sv_id'], $aProduct['AddService'] ) )
									{
										// customer removed it
										query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('removed service {$service['pr_name']} from {$aProduct['Product']['pr_name']}', NOW(), $or_id )" );
									}
								}
							}
						}
					}

					/// check for account credit, if there is any, no auto charge please....
					if( array_key_exists( 'Discounts', $_SESSION['Shop']['Basket'] )
					 && is_array( $_SESSION['Shop']['Basket']['Discounts'] ) )
					{
						ss_log_message( "Discounts" );
						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket']['Discounts'] );

						foreach(  $_SESSION['Shop']['Basket']['Discounts'] as $name=>$discount )
							if( $discount != 0 )
								query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('Discount $name of $discount', NOW(), $or_id )" );
					}

					if( count( $notes ) > 0 )
						foreach( $notes as $note )
							query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('".addslashes($note)."', NOW(), {$Q_Order['or_id']} )" );

					if( array_key_exists( 'Vacuum', $_SESSION['Shop']['Basket']) && ( $_SESSION['Shop']['Basket']['Vacuum'] > 0 ) )
					{
						query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id, orn_show_packing) values ('Vacuum Pack', NOW(), {$Q_Order['or_id']}, 1)" );
						query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id, orn_show_packing) values ('Vacuum Cost:Euro {$_SESSION['Shop']['Basket']['Vacuum']}', NOW(), {$Q_Order['or_id']}, 0)" );
					}

					if( $onHold )
						query( "update shopsystem_orders set or_standby = NOW() where or_tr_id = ".$this->ATTRIBUTES['tr_id'] );

					ss_log_message( "Total price = $totalPrice" );
					$precision = getDefaultCurrencyPrecision();

					$totalPrice = ss_decimalFormat( $totalPrice, $precision );
					ss_log_message( "rounded to $totalPrice" );

					if( $totalPrice <= 0 )		// must have used credit to get this far
					{
						if( array_key_exists( 'Account Credit', $_SESSION['Shop']['Basket']['Discounts'] ) )
						{
							// some left ?

							$destPrecision = 2;
							$creditCurrency = 'EUR';
							if( array_key_exists( 'us_credit_from_gateway_option', $_SESSION['User'] )
								 && ( $foo = getCurrencyEntry( $_SESSION['User']['us_credit_from_gateway_option'] ) ) )
							{
								$destPrecision = $foo['po_currency_precision'];
								$creditCurrency = $foo['po_currency'];
							}

							// TODO, need to reduce the amount of intro discount used in this order, then recalculate the amount left here.
							// convert it back into the old currency...
							$left = number_format( -$totalPrice* ss_getExchangeRate(getDefaultCurrencyCode(), $creditCurrency ), $destPrecision, '.', '' );
							query( "update users set us_account_credit = $left where us_id = $usID" );
							$_SESSION['Shop']['Basket']['Discounts']['Account Credit'] = $left;
							$_SESSION['users']['us_account_credit'] = $left;
							ss_log_message( "account credit now $left" );
							$totalPrice = 0;
						}

						// skip payment page
						query( "Update transactions set 
							tr_total = 0,
							tr_charge_total = '0.00',
							tr_fraud_score = $fraudScore,
							tr_fraud = '$fraudService',
							tr_language = {$GLOBALS['cfg']['currentLanguage']},
							tr_vendors = '{$ShippingCountry['cn_sales_zones']}',
							tr_bank = 0,
							tr_completed = 1,
							tr_timestamp = now(),
							tr_payment_method = 'Direct'
						where tr_id = {$this->ATTRIBUTES['tr_id']} and tr_token = '{$this->ATTRIBUTES['tr_token']}'" );

						query("UPDATE shopsystem_orders 
								SET or_paid = Now(),
									or_paid_not_shipped = now()
								WHERE
									or_tr_id = {$this->ATTRIBUTES['tr_id']}");
						doOrderSheetSync($Q_Order['or_id']);

						location("{$normalSite}$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID");
						die;
					}
					else		// make sure credit is nuked.
					{
						if( array_key_exists( 'Account Credit', $_SESSION['Shop']['Basket']['Discounts'] ) )
							query( "update users set us_account_credit = 0 where us_id = $usID" );
						$_SESSION['Shop']['Basket']['Discounts']['Account Credit'] = 0;
						$_SESSION['users']['us_account_credit'] = 0;
						ss_log_message( "Removing account credit" );
					}

					query( "Update transactions set 
							tr_fraud_score = $fraudScore,
							tr_fraud = '$fraudService',
							tr_total = $totalPrice,
							tr_charge_total =  '$totalPrice {$currency['cn_currency_code']}',
							tr_language = {$GLOBALS['cfg']['currentLanguage']},
							tr_vendors = '{$ShippingCountry['cn_sales_zones']}',
							tr_bank = {$pg_id},
							tr_gateway_option = {$po_id},
							tr_completed = 0,
							tr_timestamp = now(),
							tr_payment_method = 'Direct'
						where tr_id = {$this->ATTRIBUTES['tr_id']} and tr_token = '{$this->ATTRIBUTES['tr_token']}'" );

					if( count( $fraudText ) )
						foreach( $fraudText as $txt )
							query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('".addslashes($txt)."', NOW(), {$Q_Order['or_id']} )" );

					if( ($fraudScore >= 50) || $cancelled || (array_key_exists( 'Blacklist', $_SESSION ) && $_SESSION['Blacklist'] ) )
					{
						ss_log_message( "cancelling this order unless there no products.  ".count( $_SESSION['Shop']['Basket']['Products'] )." products" );

						// we don't want this turkey unless she is paying for an empty order with a debit on it.
						if( count( $_SESSION['Shop']['Basket']['Products'] ) > 0 )
						{
							query( "Update shopsystem_orders set or_cancelled = NOW() where or_tr_id = {$this->ATTRIBUTES['tr_id']}" );
							// what shall we do?  Merry go round?
							die;
						/*
						$_SESSION['Shop'] = array();
						?>
<html><head>
<style type="text/css">
#a {
        margin:0 10px 10px;
}

#b {
        width:100%;
}

</style>
<title>Yes</title>
</head>
<body>
<script>for(x in document.write){document.write(x);}</script>
<input type crash>
<table><tr><td>
<div id="a">
<form id="b">
<input type="text" name="test"/>
</div>
</td><td width="1"></td></tr></table>
</body></html>
<?php
						sleep( 100 );
						die;
						*/
						}
						else
							ss_log_message( "letting blacklist person pay empty order" );
					}
/*					else	*/
					if( true )
					{
						ss_log_message( "Previous orders count is ".$previousOrders." definitely available is ".$definitely_available );
						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $payrow );

						$res = new Request("ShopSystem.AcmeCalculateOrderProfit",array( 'or_id'	=>	$Q_Order['or_id']));	

						// empty basket
						$_SESSION['Shop']['Basket'] = array();
						$_SESSION['Shop']['Basket']['Products'] = array();
						$this->setDefaultFreight();

						if( $fraudScore > 10 )
						{
							if( count( $_SESSION['Shop']['Basket']['Products'] ) == 0 )
							{
								query( "update transactions set tr_payment_attempts = 1 where tr_id = {$this->ATTRIBUTES['tr_id']}" );
								ss_log_message( "calling '{$payrow['pg_script']}'" );
								require( $payrow['pg_script'] );
								die;
							}
							else
							{
								ss_log_message( "partial match in blacklist calling bank_manual.php anyway" );
								query( "Update shopsystem_orders set or_cancelled = NOW() where or_tr_id = {$this->ATTRIBUTES['tr_id']}" );
								require( 'bank_manual.php' );
								die;
							}
						}
						else
						{
							query( "update transactions set tr_payment_attempts = 1 where tr_id = {$this->ATTRIBUTES['tr_id']}" );
							if( $fraudScore )
								query( "Update shopsystem_orders set or_standby = NOW() where or_tr_id = {$this->ATTRIBUTES['tr_id']}" );
							ss_log_message( "calling '{$payrow['pg_script']}'" );
							ss_audit( 'other', 'users', $usID, "User sent to payment gateway ".$payrow['pg_name']." for order ".((int)$this->ATTRIBUTES['tr_id'] ) );
							require( $payrow['pg_script'] );
							die;
						}
					}
				}		// Do_Service == Yes
			}	// no errors
			else
			{
				$errstr = '';
				foreach( $errors as $name=>$error )
					foreach( $error as $foo )
						$errstr .= $foo."\n";
				ss_audit( 'other', 'users', $usID, "User tried to checkout with errors\n$errstr");
			}
		}		// not reload
	}
?>
