<?php

	if( ss_isAdmin() )
	{
		echo "Not as admin please";
		die;
	}

	$this->param('Chosen','0');

	$usID = ss_getUserID();

	$theUser = getRow("SELECT * FROM users WHERE us_id = ".(int)$usID );

	$lastGatewayName = NULL;

	if( $usID )
	{
		$previousOrders = getField( "select count(*) from shopsystem_orders JOIN transactions ON tr_id = or_tr_id 
											where or_us_id = $usID
												AND tr_completed = 1
												and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );

		$_SESSION['User']['us_credit_from_gateway_option'] = $theUser['us_credit_from_gateway_option'];
		if( ( $_SESSION['User']['us_account_credit'] = $theUser['us_account_credit'] ) > 0 )
			if( $foo = getCurrencyEntry( $theUser['us_credit_from_gateway_option'] ) )
				$_SESSION['Shop']['Basket']['Discounts']['Account Credit'] = -$_SESSION['User']['us_account_credit'] * ss_getExchangeRate($foo['po_currency'], getDefaultCurrencyCode( ) );

		if( $lastOrder = getRow( "select * from shopsystem_orders JOIN transactions ON tr_id = or_tr_id
									where or_us_id = $usID AND tr_completed = 1 and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)
									order by or_id desc limit 1" ) )
		{

			ss_log_message( "last gateway used was {$lastOrder['tr_bank']}" );
			if( $lastOrder['tr_bank'] )
			{
				$lastGatewayName = GetField( "select pg_name from payment_gateways where pg_id = ".((int)$lastOrder['tr_bank']) );
			}

			if( $lastCountry = $lastOrder['or_country'] )
			{  /*
				$Cn = getRow( "select * from countries where cn_id = $lastCountry" );
				// swapping vendors....
				if( $_SESSION['ForceCountry']['cn_sales_zones'] != $Cn['cn_sales_zones'] )
					$_SESSION['Shop']['Basket'] = array();

				$_SESSION['ForceCountry'] = $Cn;
				*/
			}	
		}
	}
	else
		$previousOrders = 0;

	// check for stock levels again...

	$onHold = false;
	$notes = array();
	$altered = false;
	$definitely_available = true;

	if( array_key_exists( 'Shop', $_SESSION )
	 &&  array_key_exists( 'Basket', $_SESSION['Shop'] )
	 &&  array_key_exists( 'Products', $_SESSION['Shop']['Basket'] )
	 && is_array( $_SESSION['Shop']['Basket']['Products'] ) )
		foreach($_SESSION['Shop']['Basket']['Products'] as $ind=>$aProduct)
		{
			if( array_key_exists( 'Product', $aProduct )
			 && array_key_exists( 'pr_id', $aProduct['Product'] ) )
			{
				$ProductOption = getRow("
					SELECT * FROM shopsystem_products, shopsystem_product_extended_options
					WHERE pro_pr_id = pr_id and pro_pr_id = '{$aProduct['Product']['pr_id']}'
				");

				if( !( $ProductOption['pr_combo'] >= 1 ) )
				{
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
				}

				if( array_key_exists( 'Qty', $aProduct)
				 && ($aProduct['Qty'] > 0) )
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

	if( $altered )
		locationRelative("$assetPath/Service/Login");

	if (array_key_exists('Do_Service', $this->ATTRIBUTES) )
	{
		if( $this->ATTRIBUTES['Do_Service'] == 'Reload' )
		{
			$userAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);	
		}
		else
		{
			$errors = array();

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
			else
			{
				$Q_User = getRow("SELECT * FROM users WHERE us_email LIKE '".escape($this->ATTRIBUTES['us_email'])."'");			
				if (strlen($Q_User['us_id']))
				{
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
						$errorsTemp = '';
						ss_login($usID,$errorsTemp);

						if( $usID )
							$previousOrders = getField( "select count(*) from shopsystem_orders
												JOIN transactions ON tr_id = or_tr_id 
											where or_us_id = $usID
												AND tr_completed = 1
												and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );
					}
					
					$temp = new Request("Security.Sudo",array('Action'=>'stop'));
				}
			}
			
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $errors );

			if (!count($errors))
			{
				$displayCurrency = $this->getDisplayCurrency();
				$chargeCurrency = $this->getChargeCurrency();

			}	// no errors
		}		// not reload
	}
?>
