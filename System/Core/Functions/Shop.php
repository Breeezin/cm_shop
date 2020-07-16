<?php

	function getSwisspostLicenses( )
	{
		return ['A0271', 'A0272', 'A0273', 'A0271'];
	}

	function getSiteID( )
	{
		global $cfg;

		if( $rw = getRow( "select * from configured_sites where si_name = '".$cfg['multiSites'][$cfg['currentServer']]."'" ) )
			return $rw['si_id'];

		return 1;
	}

	function checkProductVendors()
	{
		// make sure $_SESSION['ForceCountry'] is well formed
		// default it to be the IP based country....
		if( !array_key_exists( 'ForceCountry', $_SESSION )
		 || !is_array( $_SESSION['ForceCountry'] )
		 || !array_key_exists( 'cn_sales_zones', $_SESSION['ForceCountry'] ) )
		{
			$_SESSION['ForceCountry'] = getRow( "select * from countries where cn_two_code = '".ss_getCountry(NULL, 'cn_two_code')."'");
			ss_log_message( "New country defaulting to ". $_SESSION['ForceCountry'] );
		}

		ss_log_message( "Valid zones for {$_SESSION['ForceCountry']['cn_name']} are {$_SESSION['ForceCountry']['cn_sales_zones']}" );

		$valid_zones = explode( ',', $_SESSION['ForceCountry']['cn_sales_zones'] );

		for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
			{
				$entry = $_SESSION['Shop']['Basket']['Products'][$index];
				if( ss_AuthdCustomer( ) )
					$zonefield = 'pr_authd_sales_zone';
				else
					$zonefield = 'pr_sales_zone';
				if( array_key_exists( $zonefield,  $entry['Product'] ) )
				{
					if( !in_array( $entry['Product'][$zonefield], $valid_zones ) )
					{
						ss_log_message( "Product ID :{$entry['Product']['pr_id']} in zone {$entry['Product'][$zonefield]}, removing" );
						unset( $_SESSION['Shop']['Basket']['Products'][$index] );
					}
				}
				else
				{
					ss_log_message( "Product ID :{$entry['Product']['pr_id']} missing sales zone, removing" );
					unset( $_SESSION['Shop']['Basket']['Products'][$index] );
				}
			}

			// compact array
			$_SESSION['Shop']['Basket']['Products'] = array_values( $_SESSION['Shop']['Basket']['Products'] );

		$_SESSION['Shop']['Basket']['CartNumber'] = count( $_SESSION['Shop']['Basket']['Products'] );
	}

	function includedFreight( $product, $destination )		// returns USD, $product array, destination cn_id
	{
//		ss_log_message( "includedFreight()" );
//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $product );

		if( ( !array_key_exists('pr_is_service', $product )
			 || !array_key_exists('pr_upsell', $product)
			 || !array_key_exists('pr_ve_id', $product)    )
		 && array_key_exists( 'pr_id', $product ) )
		{
			$pr_id = $product['pr_id'];		// replace
		 	$product = getRow( "select * from shopsystem_products where pr_id = $pr_id" );
		}

		// services exist as add-on to real products
		if( array_key_exists('pr_is_service', $product) && ($product['pr_is_service'] == 'true') )
		{
			ss_log_message( "includedFreight on service pr_id:{$product['pr_id']} is zero" );
			return 0;
		}

		if( array_key_exists('pr_upsell', $product) && ($product['pr_upsell'] == 1) )
		{
			ss_log_message( "includedFreight on upsell item pr_id:{$product['pr_id']} is zero" );
			return 0;
		}

		if( (int)$destination  > 0 )
			if( $zone = GetField( "select cn_post_zone from countries where cn_id = ".((int)$destination ) ) )
				if( array_key_exists( 'pr_ve_id', $product ) && ($product['pr_ve_id'] > 0) )
				{
					$method = getField( "Select ve_shipping_method from vendor where ve_id = {$product['pr_ve_id']}" );
					if( $val = GetField( "select if_cost from included_freight where if_shipping_method = '$method' and if_destination_zone = '$zone'" ) )
						return $val;
				}

		return 0;
	}

	function defaultGatewayOption( )
	{
		if( !array_key_exists( 'GatewayOption', $_SESSION ) || !strlen($_SESSION['GatewayOption']) )		 // 2 is visa, default EUR
		{

			// user override?
			$user = ss_getUser();

			if( is_array($user) 
				&& array_key_exists( 'us_account_credit', $user ) &&  ( $user['us_account_credit'] != 0 )
				&& array_key_exists( 'us_credit_from_gateway_option', $user ) &&  strlen( $user['us_credit_from_gateway_option'] )
				)
			{
				ss_log_message( "user has us_credit_from_gateway_option of ".$user['us_credit_from_gateway_option'] );
				$_SESSION['GatewayOption'] = $user['us_credit_from_gateway_option'];
			}
			else
			{
				$_SESSION['GatewayOption'] = GetField( "select po_id from payment_gateway_options where po_card_type = 2 and po_active = 1 and po_site = ".getSiteID( )." order by po_preference, po_currency limit 1" );
				if( ! $_SESSION['GatewayOption'] )
					$_SESSION['GatewayOption'] = GetField( "select po_id from payment_gateway_options where po_active = 1 and po_site = ".getSiteID( )." order by po_preference, po_currency limit 1" );
			}
		}
	}

	function getDefaultCurrencyCode( )
	{
		defaultGatewayOption( );
		$retval = GetField( "select po_currency from payment_gateway_options where po_id = ".((int)$_SESSION['GatewayOption']) );
		//ss_log_message( "getDefaultCurrencyCode returning $retval from ".((int)$_SESSION['GatewayOption']) );
		return $retval;
	}

	function getDefaultCurrencyDiscount( )
	{
		defaultGatewayOption( );
		return GetField( "select po_option_discountx100 from payment_gateway_options where po_id = ".((int)$_SESSION['GatewayOption']) )/100.0;
	}

	function getDefaultCurrencySymbol( )
	{
		defaultGatewayOption( );
		return GetField( "select po_currency_symbol from payment_gateway_options where po_id = ".((int)$_SESSION['GatewayOption']) );
	}

	function getDefaultCurrencyPrecision( )
	{
		defaultGatewayOption( );
		return GetField( "select po_currency_precision from payment_gateway_options where po_id = ".((int)$_SESSION['GatewayOption']) );
	}

	function getDefaultCurrencyName( )
	{
		defaultGatewayOption( );
		return GetField( "select po_currency_name from payment_gateway_options where po_id = ".((int)$_SESSION['GatewayOption']) );
	}

	function getDefaultCurrencyEntry( )
	{
		defaultGatewayOption( );
		if( $_SESSION['GatewayOption'] )
			return GetRow( "select * from payment_gateway_options where po_id = ".((int)$_SESSION['GatewayOption']) );
		else
		{
			ss_log_message( "No payment method for this site" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION );
			if( !ss_isAdmin() )
				die;
		}
	}

	function getCurrencyEntry( $po_id )
	{
		return GetRow( "select * from payment_gateway_options where po_id = ".((int)$po_id) );
	}

	function getCurrencySummary( $po_id, $total )
	{
		$currency = getCurrencyEntry( $po_id );

		$total = $total * ss_getExchangeRate( getDefaultCurrencyCode( ), $currency['po_currency'] );

		if( $currency['po_option_discountx100'] > 0 )
		{
			$ret = "<table><tr><td>Full&nbsp;Price&nbsp;</td><td align='right'>{$currency['po_currency_symbol']}".number_format( $total, $currency['po_currency_precision'] )."</td></tr>";
			$ret .= "<tr><td>Discount (".($currency['po_option_discountx100']/100.0).")%&nbsp;</td><td align='right'>-{$currency['po_currency_symbol']}".number_format( $total*$currency['po_option_discountx100']/10000.0, $currency['po_currency_precision'] )."</td></tr>";
			$ret .= "<tr><td><strong>Pay&nbsp;Only</strong></td><td align='right'>{$currency['po_currency_symbol']}".number_format( $total*(1-$currency['po_option_discountx100']/10000.0), $currency['po_currency_precision'] )."</td></tr>";
			$ret .= "</table>";
		}
		else
		{
			$ret = "<table><tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr><tr><td><strong>Full&nbsp;Price&nbsp;</strong></td><td align='right'> {$currency['po_currency_symbol']}".number_format( $total, $currency['po_currency_precision'] )."</td></tr></table>";

		}

		return $ret; 
	}

	function setDefaultCurrency( $currency = 'EUR' )
	{
		// choose a payment gateway option with this currency

		if( getDefaultCurrencyCode( ) != $currency )
		{
			ss_log_message( "changing default currency to $currency from ". getDefaultCurrencyCode( ) );
			$canWe = GetField( "select po_id from payment_gateway_options where po_currency = '$currency' and po_active = 1 and po_site = ".getSiteID( )." order by po_card_type limit 1" );
			if( strlen( $canWe ) )
			{
				ss_log_message( "which is gateway option $canWe" );
				$_SESSION['GatewayOption'] = $canWe;
			}
			else
			{
				ss_log_message( "unable to set GatewayOption to get currency $currency" );
				$_SESSION['GatewayOption'] = '';
				defaultGatewayOption( );
			}
		}
	}

	function getUserPaymentOptions( $site = NULL )
	{
		if( !$site )
			$site = getSiteID( );

		$us_id = (int)ss_getUserID();
		$amount = $_SESSION['Shop']['Basket']['Total'] * ss_getExchangeRate( getDefaultCurrencyCode( ), 'USD' );
		$cn_id = $_SESSION['ForceCountry']['cn_id'];

		ss_log_message( "Getting payment gateway options for  $us_id, country $cn_id, amount $amount USD, site $site" );

		$user_nocb = false;
		$country_nocb = false;

		if( $us_id > 0 )		// person is logged in etc
		{
			$userRow = getRow( "select * from users where us_id = $us_id" );
			$previousOrders = getField( "select count(*) from shopsystem_orders
												JOIN transactions ON tr_id = or_tr_id 
											where or_us_id = $us_id
												AND tr_completed = 1
												and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );

			$first_name = escape( $userRow['us_first_name'] );
			$last_name = escape( $userRow['us_last_name'] );
			ss_log_message( "User '$first_name' '$last_name' has $previousOrders previous orders");
			if( $userRow['us_no_chargeback_count'] > $previousOrders )
				$user_nocb = true;
		}
		else		// unknown person
		{
			$previousOrders = 0;
		}

		if( $_SESSION['ForceCountry']['cn_no_chargeback_count'] > 0 )
		{
			ss_log_message( "Country gateway selection in force" );
			if( $_SESSION['ForceCountry']['cn_no_chargeback_count'] > $previousOrders )
				$country_nocb = true;
		}

		// TODO, needs work, first bit, one for each available credit card type, then all the non-credit card gateways

		if( $user_nocb || $country_nocb )

			$sql = "select
						po_id, cct_id, po_preference, cct_name, po_currency, po_option_description as description, cct_image
							from payment_gateways join payment_gateway_options on pg_id = po_pg_id join credit_card_types on po_card_type = cct_id 
							where po_active = true
							 and po_site = $site
							 and po_restrict_to_person = false
							 and ( po_restrict_to_country IS NULL OR po_restrict_to_country = $cn_id )
							 and ( po_restrict_from_country IS NULL OR po_restrict_from_country != $cn_id )
							 and ( po_restrict_from_country2 IS NULL OR po_restrict_from_country2 != $cn_id )
							 and ( po_restrict_from_country3 IS NULL OR po_restrict_from_country3 != $cn_id )
							 and ( po_restrict_from_country4 IS NULL OR po_restrict_from_country4 != $cn_id )
							 and ( pg_order_max IS NULL or pg_order_max > $amount )
							 and (pg_limit IS NULL OR (pg_limit IS NOT NULL and pg_limit > pg_accumulation+$amount) )
							 and ( pg_minimum_total IS NULL or pg_minimum_total <= $amount )
							 and pg_can_chargeback = false
							 and ( pg_minimum_orders IS NULL OR pg_minimum_orders <= ".$previousOrders." )
					union 
					select
						-po_id, 999, po_preference, pg_name, po_currency, po_option_description, po_currency_image 
							from payment_gateways join payment_gateway_options on pg_id = po_pg_id 
							where po_active = true 
							 and po_site = $site
							 and po_restrict_to_person = false
							 and ( po_restrict_to_country IS NULL OR po_restrict_to_country = $cn_id )
							 and ( po_restrict_from_country IS NULL OR po_restrict_from_country != $cn_id )
							 and ( po_restrict_from_country2 IS NULL OR po_restrict_from_country2 != $cn_id )
							 and ( po_restrict_from_country3 IS NULL OR po_restrict_from_country3 != $cn_id )
							 and ( po_restrict_from_country4 IS NULL OR po_restrict_from_country4 != $cn_id )
							 and ( pg_order_max IS NULL or pg_order_max > $amount )
							 and (pg_limit IS NULL OR (pg_limit IS NOT NULL and pg_limit > pg_accumulation+$amount) )
							 and ( pg_minimum_total IS NULL or pg_minimum_total <= $amount )
							 and pg_can_chargeback = false
							 and po_card_type not in (select cct_id from credit_card_types)
							 and ( pg_minimum_orders IS NULL OR pg_minimum_orders <= ".$previousOrders." )
						order by 2, 3, 4";
		else
			$sql = "select
						po_id, cct_id, po_preference, cct_name, po_currency, po_option_description as description, cct_image
							from payment_gateways join payment_gateway_options on pg_id = po_pg_id join credit_card_types on po_card_type = cct_id 
							where po_active = true
							 and po_site = $site
							 and po_restrict_to_person = false
							 and ( po_restrict_to_country IS NULL OR po_restrict_to_country = $cn_id )
							 and ( po_restrict_from_country IS NULL OR po_restrict_from_country != $cn_id )
							 and ( po_restrict_from_country2 IS NULL OR po_restrict_from_country2 != $cn_id )
							 and ( po_restrict_from_country3 IS NULL OR po_restrict_from_country3 != $cn_id )
							 and ( po_restrict_from_country4 IS NULL OR po_restrict_from_country4 != $cn_id )
							 and ( pg_order_max IS NULL or pg_order_max > $amount )
							 and (pg_limit IS NULL OR (pg_limit IS NOT NULL and pg_limit > pg_accumulation+$amount) )
							 and ( pg_minimum_total IS NULL or pg_minimum_total <= $amount )
							 and ( pg_minimum_orders IS NULL OR pg_minimum_orders <= ".$previousOrders." )
					union 
					select
						-po_id, 999, po_preference, pg_name, po_currency, po_option_description, po_currency_image 
							from payment_gateways join payment_gateway_options on pg_id = po_pg_id 
							where po_active = true 
							 and po_site = $site
							 and po_restrict_to_person = false
							 and ( po_restrict_to_country IS NULL OR po_restrict_to_country = $cn_id )
							 and ( po_restrict_from_country IS NULL OR po_restrict_from_country != $cn_id )
							 and ( po_restrict_from_country2 IS NULL OR po_restrict_from_country2 != $cn_id )
							 and ( po_restrict_from_country3 IS NULL OR po_restrict_from_country3 != $cn_id )
							 and ( po_restrict_from_country4 IS NULL OR po_restrict_from_country4 != $cn_id )
							 and ( pg_order_max IS NULL or pg_order_max > $amount )
							 and (pg_limit IS NULL OR (pg_limit IS NOT NULL and pg_limit > pg_accumulation+$amount) )
							 and ( pg_minimum_total IS NULL or pg_minimum_total <= $amount )
							 and po_card_type not in (select cct_id from credit_card_types)
							 and ( pg_minimum_orders IS NULL OR pg_minimum_orders <= ".$previousOrders." )
						order by 2, 3, 4";

		//ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $sql );
		$Q_PaymentOptions = query( $sql );
		return $Q_PaymentOptions;
	}

	function getOtherSiteID( )
	{
		global $cfg;

		// merry go round
		switch( getSiteID( ) )
		{
		case 1:
			return 10;
		case 2:
			return 1;
		case 10:
			return 2;
		}
	}

	function getOtherSiteName()
	{
		$other_site = getOtherSiteID( );

		if( $rw = getRow( "select * from configured_sites where si_id = $other_site" ) )
				return "https://{$rw['si_base_url']}";

		return NULL;
	}

	function getUserPaymentOptionsOtherSite()
	{
		return getUserPaymentOptions( getOtherSiteID( ) );
	}

	function getUserPaymentGateway( $us_id, $billingRecord = NULL, $amount = 0)		// 2 == visa
	{
		$ret = array();
		$ret['Desc'] = array();

		$ret['Desc'][] = "Getting payment gateway for user ID $us_id, billingRecord ".print_r($billingRecord, true).", amount $amount";

		$chosenGatewayOption = getDefaultCurrencyEntry( );

		ss_log_message( "Getting payment gateway for user ID $us_id, amount $amount, (chosen is {$chosenGatewayOption['po_id']})" );
		

		$firstNameClause = '';
		$lastNameClause = '';

		$user_nocb = false;
		$country_nocb = false;


		if( $us_id > 0 )		// person is logged in etc
		{
			$userRow = getRow( "select * from users where us_id = $us_id" );

			if( $billingRecord == NULL )
				$billingRecord = $userRow;

			$ret['Desc'][] = "user $us_id hit checkout user gateway is {$userRow['us_payment_gateway']}";

			//if( ( $userRow['us_payment_gateway'] > 0 ) && ( $chosenGatewayOption['po_card_type'] > 0 ) && ( strstr( $userRow['us_email'], 'admin.com' ) ) ) // testing out payment gateways only....
			if( ( $userRow['us_payment_gateway'] > 0 ) ) // testing out payment gateways only....
			{
				// check if this person has non null us_payment_gateway?
				//  is there any allocation left for today on that gateway, yes -> use that.
				// enforce same credit card, no result if they want to use a different one.
				// no restriction on pg_restrict_to_person
					//	and po_card_type = {$chosenGatewayOption['po_card_type']}

						// and po_currency = '{$chosenGatewayOption['po_currency']}'
				$sql = "select * from payment_gateways join payment_gateway_options on pg_id = po_pg_id 
					where po_active = true 
						and po_site = ".getSiteID( )."
						and (pg_limit IS NULL OR (pg_limit IS NOT NULL and pg_limit > pg_accumulation+$amount) )
						and ( pg_order_max IS NULL or pg_order_max > $amount )
						and po_id = {$userRow['us_payment_gateway']}
						order by po_preference";

				$pg = getRow( $sql );

				if( $pg )
				{
					$ret['Desc'][] = "accumulation left, choosing this";
					$ret['Gateway'] = $pg;
					ss_log_message( "Returning" );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ret );
					return $ret;
				}
			}

			// check if this persons last order was with an available gateway
			//  is there any allocation left for today on that gateway, yes -> use that.

			/*	deprecated, worked poorly
			if( $cardType > 0 )
				$sql = "select tr_id, tr_bank from transactions join shopsystem_orders on or_tr_id = tr_id join payment_gateways on tr_bank = pg_id join payment_gateway_options on pg_id = po_pg_id where or_us_id = $us_id and tr_completed = 1 and or_paid IS NOT NULL and or_reshipment IS NULL and po_card_type = $cardType and po_active = true and tr_total > 0 order by tr_id desc";
			else		// default VISA 2
				$sql = "select tr_id, tr_bank from transactions join shopsystem_orders on or_tr_id = tr_id join payment_gateways on tr_bank = pg_id join payment_gateway_options on pg_id = po_pg_id where or_us_id = $us_id and tr_completed = 1 and or_paid IS NOT NULL and or_reshipment IS NULL and po_card_type = 2 and po_active = true and tr_total > 0 order by tr_id desc";

			//$sql = "select tr_id, tr_bank from transactions join shopsystem_orders on or_tr_id = tr_id where or_us_id = $us_id and tr_completed = 1 and or_paid IS NOT NULL and or_reshipment IS NULL order by tr_id desc";
			$last = getRow( $sql );

			if( $last )
			{
				ss_log_message( "Last tr_id {$last['tr_id']} used gateway {$last['tr_bank']}" );

				if( $last['tr_bank'] > 0 )
				{
					$ret['Desc'][] = "user $us_id last payed with gateway {$last['tr_bank']} for Order {$last['tr_id']}";

					$sql = "select * from payment_gateways join payment_gateway_options on pg_id = po_pg_id
								where po_active = true
									and ( pg_limit IS NULL OR (pg_limit IS NOT NULL and pg_limit > pg_accumulation+$amount) ) 
									and ( pg_order_max IS NULL or pg_order_max > $amount )
									and pg_id = {$last['tr_bank']} and po_restrict_to_person = false";

					$pg = getRow( $sql );
					if( $pg )
					{
						$ret['Desc'][] = "accumulation left, choosing this";
						$ret['Gateway'] = $pg;
						ss_log_message( "Returning" );
						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ret );
						return $ret;
					}
				}
			}
*/


			// still here?  gather required info for pool choice

			$previousOrders = getField( "select count(*) from shopsystem_orders
												JOIN transactions ON tr_id = or_tr_id 
											where or_us_id = $us_id
												AND tr_completed = 1
												and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );

			$ret['Desc'][] = "User has $previousOrders previous orders";

			if( $userRow['us_no_chargeback_count'] > $previousOrders )
				$user_nocb = true;

			$firstNameClause = "and ('".escape( $userRow['us_first_name'] )."' REGEXP po_firstname_regex )";
			$lastNameClause = "and ('".escape( $userRow['us_last_name'] )."' REGEXP po_lastname_regex )";
			ss_log_message( "User ".escape( $userRow['us_first_name'] )." ".escape( $userRow['us_last_name'] )." has $previousOrders previous orders");
		}
		else		// unknown person
		{
			$previousOrders = 0;
			if( $billingRecord == NULL )
			{
				$ret['Desc'][] = "No user information at all";
				$ret['Gateway'] = NULL;
				ss_log_message( "Returning" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ret );
				return $ret;
			}
			if( array_key_exists( 'us_first_name', $billingRecord ) && strlen(  $billingRecord['us_first_name'] ) )
				$firstNameClause = "and ('".escape( $billingRecord['us_first_name'] )."' REGEXP po_firstname_regex )";
			if( array_key_exists( 'us_last_name', $billingRecord ) && strlen(  $billingRecord['us_last_name'] ) )
				$lastNameClause = "and ('".escape( $billingRecord['us_last_name'] )."' REGEXP po_lastname_regex )";
		}

		if( array_key_exists( 'us_0_50A4', $billingRecord ) )
			$cn_id = (int) $billingRecord['us_0_50A4'];
		else
		{
			$ret['Desc'][] = "No country in billing record";
			$ret['Gateway'] = NULL;
			ss_log_message( "Returning" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ret );
			return $ret;
		}

		if( $cn_id == 0 )
			$cn_id = $_SESSION['ForceCountry']['cn_id'];

		if( $cn_id == 0 )
		{
			$ret['Desc'][] = "Invalid country in billing record";
			$ret['Gateway'] = NULL;
			ss_log_message( "Returning" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ret );
			return $ret;
		}

		$ret['Desc'][] = "Country ID $cn_id";

		$cn_no_chargeback_count = getField( "select cn_no_chargeback_count from countries where cn_id = $cn_id" );
		if( $cn_no_chargeback_count > 0 )
		{
			ss_log_message( "Country gateway selection in force" );
			if( $cn_no_chargeback_count > $previousOrders )
				$country_nocb = true;
		}
		// pool choice
		$ret['Desc'][] = "choosing from pool";

		if( $chosenGatewayOption['po_card_type'] > 0 )
			$po_option = "and po_card_type = {$chosenGatewayOption['po_card_type']}";
		else
			if( strlen( $chosenGatewayOption['po_id'] ) )
				$po_option = "and po_id = {$chosenGatewayOption['po_id']}";
			else
				$po_option = '';

		if( $user_nocb || $country_nocb )
			$cb_option = 'and pg_can_chargeback = false';
		else
			$cb_option = '';

		$sql = "select * from payment_gateways join payment_gateway_options on pg_id = po_pg_id
					where po_active = true
					 and po_site = ".getSiteID( )."
					 and ( pg_limit IS NULL OR (pg_limit IS NOT NULL and pg_limit > pg_accumulation+$amount) )
					 and ( pg_minimum_orders IS NULL OR pg_minimum_orders <= ".$previousOrders." )
					 and ( po_restrict_to_country IS NULL OR po_restrict_to_country = $cn_id )
					 $firstNameClause
					 $lastNameClause
					 and ( po_restrict_from_country IS NULL OR po_restrict_from_country != $cn_id )
					 and ( po_restrict_from_country2 IS NULL OR po_restrict_from_country2 != $cn_id )
					 and ( po_restrict_from_country3 IS NULL OR po_restrict_from_country3 != $cn_id )
					 and ( po_restrict_from_country4 IS NULL OR po_restrict_from_country4 != $cn_id )
					 and ( pg_order_max IS NULL or pg_order_max > $amount )
					 and po_restrict_to_person = false
					 and po_currency = '{$chosenGatewayOption['po_currency']}'
					 $po_option
					 $cb_option
					 order by po_preference, (pg_limit-pg_accumulation)
					 limit 1";

		$ret['Gateway'] = getRow( $sql );

		// this is the blurb that gets shown to the customer when this returns null.  there is no good place to put this, so it can go here, the least worst place
		if( !$ret['Gateway'] )
		{
			$ret['NoChargeBlurb'] = 'This payment option is unavailable for this order at this time.  Please try another payment option or try placing this order on one of our sister sites.<br />Our main sites are www.acmerockets.com and  www.rubberbands.com, identical stock, contact page and login.</strong><br /><br />';
			$ret['NoChargeBlurb'] .= "<br /><br /><a href = 'javascript:history.back()'>Please navigate back a page and choose another option</a>";
		}
		else
			$ret['NoChargeBlurb'] = '';

		ss_log_message( "$sql Returning" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ret );
		return $ret;

	}

	function doOrderSheetSync( $or_id, $orderProducts = NULL )
	{
		$fixEntry = false;

		$or_id = (int) $or_id;
		ss_log_message( "Order Sheet Sync or_id ".$or_id );
		if( $or_id > 0 )
		{
			// get order status...
			$status = getRow( "select or_paid_not_shipped, or_shipped, or_paid, or_deleted, or_card_denied, or_cancelled, or_standby, or_basket from shopsystem_orders where or_id = ".((int)$or_id) );
			extract( $status );

			ss_log_message( "Status or_paid_not_shipped:$or_paid_not_shipped, or_shipped:$or_shipped, or_paid:$or_paid, or_deleted:$or_deleted, or_card_denied:$or_card_denied, or_cancelled:$or_cancelled, or_standby:$or_standby" );

			if( $orderProducts == NULL )
			{
				$OrderDetails = unserialize($or_basket);
				$orderProducts = $OrderDetails['Basket']['Products'];
			}

//			ss_log_message( "Products..." );
//			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $orderProducts );

			if( strlen( $or_shipped ) )
			{
				// this has been marked as all shipped, make sure that there is nothing to go on the packing list
				query( "delete from shopsystem_order_items where oi_eos_id = NULL and oi_or_id = ".((int)$or_id)."" );
			}

			$orderCodes = array();
			if( (strlen( $or_card_denied ) || ($or_deleted > 0) || strlen( $or_cancelled ) || strlen( $or_standby ) ) )		// want it gone
//				&& (!strlen($or_paid) || !strlen($or_paid_not_shipped) ) )													// on the order list for some reason
			{
				// remove as much as possible from the order system
				ss_log_message( "Removing as much as possible from external order system" );

				foreach($orderProducts as $aProduct)
				{
//					ss_log_message( "Product..." );
//					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $aProduct );
					for( $i = 0; $i < $aProduct['Qty']; $i++ )
					{
						ss_log_message( "Box $i of '".escape($aProduct['Product']['pro_stock_code'])."'" );

						if( array_key_exists('Shipped', $aProduct)
							&& is_array( $aProduct['Shipped'] )
							&& array_key_exists($i, $aProduct['Shipped'] )
							&& strlen( $aProduct['Shipped'][$i] ) )
						{
							ss_log_message( "Shipped" );
						}
						else
						{
							/*
							// never do this, it might have been shipped
							$Q_RemoveExisting = query("DELETE FROM shopsystem_order_sheets_items
															WHERE orsi_or_id = ".((int)$or_id)."
															and orsi_box_number = $i
															and orsi_stock_code = '".escape($aProduct['Product']['pro_stock_code'])."'");
							*/
							// is it on a packing list?

							$productName = getField("SELECT pr_name FROM shopsystem_products, shopsystem_product_extended_options
											WHERE pro_stock_code LIKE '".escape($aProduct['Product']['pro_stock_code'])."'
												AND pr_id = pro_pr_id ");

							if( !strlen( $productName ) )
							{
								// pro_stock_code has changed.  Swap to ID.
								$product = getRow("SELECT * FROM shopsystem_products, shopsystem_product_extended_options
											WHERE pr_id = pro_pr_id and pr_id = ".$aProduct['Product']['pr_id']);
								$aProduct['Product']['pro_stock_code'] = $product['pro_stock_code'];
								$productName = $product['pr_name'];
							}

							$onList = getRow( "select count(*) as rc from shopsystem_order_sheets_items
															WHERE orsi_or_id = ".((int)$or_id)."
															and (orsi_box_number = $i or (orsi_box_number IS NULL and orsi_qty > 0 ) )
															and orsi_stock_code = '".escape($aProduct['Product']['pro_stock_code'])."'"); 

							if( $onList['rc'] > 0 )
							{
								ss_log_message( "On packing list, not removing" );
							}
							else
							{
								ss_log_message( "not on packing list, removing from order list" );
								$Q_RemoveExisting = query("DELETE FROM shopsystem_order_items
															WHERE oi_or_id = ".((int)$or_id)."
															and (oi_box_number = $i or oi_box_number IS NULL)
															and oi_stock_code = '".escape($aProduct['Product']['pro_stock_code'])."'");
							}
						}

						$orderCodes[] = $aProduct['Product']['pro_stock_code'];
					}
				}
				// now nuke everything on the external order list that isn't in $orderCodes
				query( "delete from shopsystem_order_items where oi_or_id = ".((int)$or_id)." and oi_stock_code not in ('".implode( "','", $orderCodes )."')" );
				ss_log_message( "delete from shopsystem_order_items where oi_or_id = ".((int)$or_id)." and oi_stock_code not in ('".implode( "','", $orderCodes )."')" );
			}
			else
				if( strlen($or_paid_not_shipped) )	// should be on a packing list....
				{
					ss_log_message( "want ordering" );
					// sending or have sent these peeps something.
					foreach($orderProducts as $ind=>$aProduct)
					{
//						ss_log_message( "Product..." );
//						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $aProduct );

						$productName = escape(getField("SELECT pr_name FROM shopsystem_products, shopsystem_product_extended_options
										WHERE pro_stock_code LIKE '".escape($aProduct['Product']['pro_stock_code'])."'
											AND pr_id = pro_pr_id "));

						if( !strlen( $productName ) )
						{
							// pro_stock_code has changed.  Swap to ID.
							$product = getRow("SELECT * FROM shopsystem_products, shopsystem_product_extended_options
										WHERE pr_id = pro_pr_id and pr_id = ".$aProduct['Product']['pr_id']);
							$aProduct['Product']['pro_stock_code'] = $product['pro_stock_code'];
							$orderProducts[$ind]['Product']['pro_stock_code'] = $product['pro_stock_code'];
							$productName = escape($product['pr_name']);
							$fixEntry = true;
						}
						else
							if (strlen($aProduct['Product']['Options']))
								$productName .= ' ('.escape($aProduct['Product']['Options']).')';

						// lets have a look at the ExternalOrderSheets stuff and see what is there...

						// how many are shipped, not shipped and already there on the order list?
						$num_shipped = 0;
						$qty = $aProduct['Qty'];

						for( $i = 0; $i < $aProduct['Qty']; $i++ )
							if( array_key_exists('Shipped', $aProduct)
									&& is_array( $aProduct['Shipped'] )
									&& array_key_exists($i, $aProduct['Shipped'] )
									&& strlen( $aProduct['Shipped'][$i] ) )
								$num_shipped++;

						ss_log_message( "Product {$aProduct['Product']['pro_stock_code']} has $num_shipped shipped boxes out of $qty" );

						$Q = getRow( "select count(*) as count from shopsystem_order_items
												JOIN shopsystem_order_sheets_items on orsi_ors_id = oi_eos_id 
														and orsi_or_id = oi_or_id and orsi_stock_code = oi_stock_code
													    and orsi_box_number = oi_box_number
												WHERE oi_or_id = $or_id
												and oi_stock_code = '".escape($aProduct['Product']['pro_stock_code'])."'");

						$there = $Q['count'];
						$orderCodes[] = $aProduct['Product']['pro_stock_code'];

						ss_log_message( "Product {$aProduct['Product']['pro_stock_code']} has $there boxes in order system" );


						if( $there > $qty )		// need to trim off the external Order list
						{
							// how do we tell if it's shipped?
							// it's probably not... but that info has been nuked now.
						}

						// all shipped, leave it as is.
						if( ( $there <= $qty ) && ( $num_shipped == $qty ) )
							continue;

						// same number... ditto
						if( $there == $qty )
							continue;

						// need to add more boxes... but numbers?
						if( $qty > $there )
						{
							// add in more boxes to External Order List
							for( $i = 0; $i < $aProduct['Qty']; $i++ )
							{
								if( array_key_exists('Shipped', $aProduct)
									&& is_array( $aProduct['Shipped'] )
									&& array_key_exists($i, $aProduct['Shipped'] )
									&& strlen( $aProduct['Shipped'][$i] ) )
								{

								}
								else
								{
									// ensure is on external packing list
									$foo = getRow( "select oi_box_number, oi_eos_id from shopsystem_order_items
															WHERE oi_or_id = ".((int)$or_id)."
															and oi_box_number = $i
															and oi_stock_code = '".escape($aProduct['Product']['pro_stock_code'])."'");
	/*
	Order Sheet Sync or_id 85550
	Status or_paid_not_shipped:2011-01-11, or_shipped:, or_paid:2011-01-11 06:55:20, or_deleted:0, or_card_denied:, or_cancelled:, or_standby:
	Product 0113S has 0 shipped boxes out of 4
	Product 0113S has 3 boxes in order system
	Adding Box 0 of '0113S'
	Box 1 of '0113S' already there
	Adding Box 2 of '0113S'
	Box 3 of '0113S' already there


	*/


									if( $foo && $foo['oi_eos_id'] > 0 )
									{
										// it's on an order sheet too...  check it.
										$foo = getRow( "select orsi_box_number, orsi_ors_id from shopsystem_order_sheets_items
															WHERE orsi_or_id = ".((int)$or_id)."
															and orsi_box_number = $i
															and orsi_stock_code = '".escape($aProduct['Product']['pro_stock_code'])."'");

										if( $foo && $foo['orsi_ors_id'] > 0 )
										{
											// OK
										}
										else
										{
											// Not, remove it.
											query( "update shopsystem_order_items set oi_eos_id = NULL
														  where oi_or_id = ".((int)$or_id)."
															and oi_box_number = $i
															and oi_stock_code = '".escape($aProduct['Product']['pro_stock_code'])."'");
										}
										$foo['oi_box_number'] = $i;
									}

									if( $foo && ($foo['oi_box_number'] == $i) )
									{
										ss_log_message( "Box $i of '".escape($aProduct['Product']['pro_stock_code'])."' already there" );
									}
									else
									{
										ss_log_message( "Adding Box $i of '".escape($aProduct['Product']['pro_stock_code'])."'" );

										if( !array_key_exists( 'pr_is_service', $aProduct['Product'] )
										 || ( $aProduct['Product']['pr_is_service'] != 'true' ) )
										{
											if( array_key_exists( 'PrExternal', $aProduct['Product'] ) )
												$Q_InsertStockOrder = query("
													INSERT INTO shopsystem_order_items
														(oi_stock_code, oi_name, oi_or_id, oi_box_number, oi_ve_id)
													VALUES
														('".escape($aProduct['Product']['pro_stock_code'])."',
														 '$productName', $or_id, $i,
														 ".escape($aProduct['Product']['PrExternal']).")
													");
											else
												$Q_InsertStockOrder = query("
													INSERT INTO shopsystem_order_items
														(oi_stock_code, oi_name, oi_or_id, oi_box_number, oi_ve_id)
													VALUES
														('".escape($aProduct['Product']['pro_stock_code'])."',
														 '$productName', $or_id, $i,
														 ".escape($aProduct['Product']['pr_ve_id']).")
													");

										}
										else
											ss_log_message( "Ignoring service" );

									}
								}
							}
						}
						else
						{
							// remove some..
							$remove = $there - $qty;

							$i = getField( "select max(oi_box_number) from shopsystem_order_items
															WHERE oi_or_id = ".((int)$or_id)."
															and oi_stock_code = '".escape($aProduct['Product']['pro_stock_code'])."'");
							// off the end.

							ss_log_message( "Looking to remove $remove boxes, starting at box number $i" );

							for( ; ($remove > 0 ) && ($i >= 0); $i-- )
							{
								if( array_key_exists('Shipped', $aProduct)
									&& is_array( $aProduct['Shipped'] )
									&& array_key_exists( $i, $aProduct['Shipped'] )
									&& strlen( $aProduct['Shipped'][$i] ) )
								{
									// can't remove this one.
									ss_log_message( "Box $i of '".escape($aProduct['Product']['pro_stock_code'])."' seems to be shipped" );
								}
								else
								{
									ss_log_message( "Removing box $i of '".escape($aProduct['Product']['pro_stock_code'])."'" );
									/*
									// never do this, it might have been shipped
									$Q_RemoveExisting = query("DELETE FROM shopsystem_order_sheets_items
															WHERE orsi_or_id = ".((int)$or_id)."
															and orsi_box_number = $i
															and orsi_stock_code = '".escape($aProduct['Product']['pro_stock_code'])."'");
									*/
									// is it on a packing list?

									$onList = getRow( "select count(*) as rc from shopsystem_order_sheets_items
															WHERE orsi_or_id = ".((int)$or_id)."
															and orsi_box_number = $i
															and orsi_stock_code = '".escape($aProduct['Product']['pro_stock_code'])."'"); 

									if( $onList['rc'] == 1 )
									{
										ss_log_message( "nope ".$onList['rc']." on packing list, leaving" );
									}
									else
									{
										$Q_RemoveExisting = query("DELETE FROM shopsystem_order_items
																WHERE oi_or_id = ".((int)$or_id)."
																and (oi_box_number = $i or oi_box_number IS NULL)
																and oi_stock_code = '".escape($aProduct['Product']['pro_stock_code'])."'");
										$remove--;
									}
								}
							}
						}

					}
					if( $fixEntry )
					{
						//serialize fixed or_details...

						$or_basket = getField( "select or_basket from shopsystem_orders where or_id = ".((int)$or_id) );
						$OrderDetails = unserialize($or_basket);
						$OrderDetails['Basket']['Products'] = $orderProducts;
						$or_basket = escape(serialize( $OrderDetails ));
						query( "Update shopsystem_orders set or_basket = '$or_basket' where or_id = ".((int)$or_id) );
					}

					if( count($orderCodes) > 0 )
					{
						query( "delete from shopsystem_order_items where oi_or_id = ".((int)$or_id)." and oi_stock_code not in ('".implode( "','", $orderCodes )."')" );
						ss_log_message( "delete from shopsystem_order_items where oi_or_id = ".((int)$or_id)." and oi_stock_code not in ('".implode( "','", $orderCodes )."')" );
					}
				}
		}

	}

	function ss_getTrasacationRef($trID) {
		$prefix = ss_optionExists('Custom Transaction Referece Code');
		if ($prefix !== null and $prefix !== false and strlen($prefix)) {
			$prefix = substr($prefix, 0, (strlen($prefix) - strlen($trID)));
			return $prefix.$trID;
		}
		return $trID;
	}
	function ss_getShopCategories($assetID, $appearsInMenu = true, $menuParentPath = null) {		
		$returnMenuStruc = array();
		$rootCategories = array();
		$whereSQL = '';
		if ($appearsInMenu) {
			$whereSQL = ' AND ca_appears_in_menu = 1';
		}
						
		$Q_RootCategories = query("
			SELECT * FROM shopsystem_categories
			WHERE ca_parent_ca_id IS NULL
			AND ca_as_id = $assetID
			$whereSQL
			ORDER BY ca_sort_order, ca_name
		");
		
		while ($cat = $Q_RootCategories->fetchRow()) {			
			$niceCategoryName = ss_alphaNumeric($cat['ca_name'],'_');
			$path = "$menuParentPath/Service/Engine/pr_ca_id/{$cat['ca_id']}/Category/{$niceCategoryName}.html";
			$childCategories = ss_getChildCategories($cat['ca_id'], $path,$appearsInMenu, $assetID, $menuParentPath);			
			$hasChild = count($childCategories)?true:false;
			
			array_push($returnMenuStruc,array(
				'as_parent_as_id'	=>	$assetID,
				'as_id'			=>	$assetID.$cat['ca_id'],
				'as_name'			=>	$cat['ca_name'],
				'as_menu_name'		=>	null,
				'AssetDescription'	=>	null,
				'Path'				=>	$path,
				'ParentPath'		=>	$menuParentPath,
				'ParentID'			=>	$assetID,
				'Children'			=>	$childCategories,
				'HasChildren'		=>	$hasChild,
			));			
		}	
		return  $returnMenuStruc;				
	}
	function ss_getChildCategories($parentID, $parentPath, $appearsInMenu, $assetID, $menuParentPath) {			
		$whereSQL = '';
		if ($appearsInMenu) {
			$whereSQL = ' AND ca_appears_in_menu = 1';
		}
		$Q_Subs = query("
			SELECT * FROM shopsystem_categories
			WHERE ca_parent_ca_id = $parentID
				AND ca_as_id = $assetID
				$whereSQL
			ORDER BY ca_sort_order, ca_name
		");	
		if ($Q_Subs->numRows()) {
			$returnChild = array();
			while ($cat = $Q_Subs->fetchRow()) {
				$niceCategoryName = ss_alphaNumeric($cat['ca_name'],'_');				
				$path = "$menuParentPath/Service/Engine/pr_ca_id/{$cat['ca_id']}/Category/{$niceCategoryName}.html";
				$childCategories = ss_getChildCategories($cat['ca_id'], $path,$appearsInMenu, $assetID, $menuParentPath);
				$hasChild = count($childCategories)?true:false;				
				array_push($returnChild,array(
					'as_parent_as_id'	=>	$assetID,
					'as_id'			=>	$assetID.$cat['ca_id'],
					'as_name'			=>	$cat['ca_name'],
					'as_menu_name'		=>	null,
					'AssetDescription'	=>	null,
					'Path'				=>	$path,
					'ParentPath'		=>	$menuParentPath,
					'ParentID'			=>	$assetID,
					'Children'			=>	$childCategories,
					'HasChildren'		=>	$hasChild,
				));	
			}
			return $returnChild;
		} else {
			return array();
		}
		
		
	}
	
	function ss_shopRestrictedCategoriesSQL( $offers = 0 ) {
		global $cfg;
		if( !ss_isAdmin() && !$offers )
			$restrictedCategoriesSQL = " AND ca_id in (select scm_ca_id from site_category_mask where scm_lg_id = {$cfg['currentLanguage']} and scm_ca_active = 1) ";
		else
			$restrictedCategoriesSQL = "";
		if (ss_optionExists('Shop Category Restricted')) {
			if (array_key_exists('CanViewCategory',$_SESSION)) {
				$allowedRestrictedCategories = ArrayToList($_SESSION['CanViewCategory']);
				if (strlen($allowedRestrictedCategories)) {
					$restrictedCategoriesSQL .= " AND (ca_password IS NULL OR ca_id IN ($allowedRestrictedCategories))";
				} else {
					$restrictedCategoriesSQL .= " AND (ca_password IS NULL)";
				}
			} else {
				$restrictedCategoriesSQL .= ' AND (ca_password IS NULL)';
			}
		}
		return $restrictedCategoriesSQL;
	}

	function splitAt( $splitString, $max1, $max2 )
	{
		$splitc = $max1;

		while( ($splitc > 0) && ($splitString[$max1] != ',') && ($splitString[$max1] != ' ') && ($splitString[$max1] != '.') )
			$splitc--;

		if( $splitc <= 0 )		// hit the beginning
			$splitc = $max1;

		$first = substr( $splitString, 0, $splitc );
		if( $splitc < strlen( $splitString ) )
			$second = substr( $splitString, $splitc, $max2 );
		else
			$second = '';

		return [$first, $second];
	}


	interface paymentGateway
	{
		public static function getHiddenFormFields( $orderArray, $txArray, $totalPrice );
		public static function getPOSTURL( );
		public static function getCurrencyHandled( );
		public static function refund( $amount, $key );
		public static function enquire( $order_id );
		public static function refund_tx( $enq_status );
	}

	class acqraPaymentGatewayV1 implements paymentGateway
	{
		// TEST
		//protected static $LOCAL_HOST = 'test.acmerockets.com';
		//protected static $GATEWAY_HOST = 'sandbox.acqra.com';

		// LIVE
		protected static $LOCAL_HOST = 'www.acmerockets.com';
		protected static $GATEWAY_HOST = 'api.acqra.com';

		/*private*/ protected static $MID = 'parent';
		/*private*/ protected static $SECURITY_KEY = 'parent';
		/*private*/ protected static $API_KEY = 'parent';
		/*private*/ protected static $CURRENCY = 'parent';

		/*public */ public static $STATUS_SUCCESS = 10000;
		/*public */ public static $STATUS_REFUND_SUBMITTED = 30001;
		/*public */ public static $STATUS_REFUND_SUCCESS = 30002;

		static function refund_tx( $enq_status )				/* acqraPaymentGatewayV1 */
		{
			if( is_object( $enq_status ) )
			{
				if( property_exists( $enq_status, 'order_ref' ) )
					$unique_id = $enq_status->order_ref;
				if( property_exists( $enq_status, 'status_code' ) )
					if( $enq_status->status_code == static::$STATUS_SUCCESS )
						$show_refund_screen = true;
			}

			if( $show_refund_screen && $unique_id )
				return $unique_id;

		return false;
		}

		static function sign ($params, $fieldNames)				/* acqraPaymentGatewayV1 */
		{
		  return hash('sha256', static::buildDataToSign($params, $fieldNames) );
		}

		static function buildDataToSign($params, $fieldNames)				/* acqraPaymentGatewayV1 */
		{
			foreach ($fieldNames as &$field)
			   $dataToSign[] = $params[$field];

			$dataToSign[] = static::$SECURITY_KEY;

			$ret = implode( ',', $dataToSign );
			ss_log_message( 'hashing ->'.$ret );
			return $ret;
		}

		static function getHiddenFormFields( $Q_Order, $Q_Transaction, $totalPrice )				/* acqraPaymentGatewayV1 */
		{

			$ACK_URL = 'https://'.static::$LOCAL_HOST.'/Shop_System/Service/Completed/tr_id/'.$Q_Transaction['tr_id']
						.'/tr_token/'.$Q_Transaction['tr_token'].'/us_id/'.$Q_Order['or_us_id'];
			$NACK_URL = 'https://'.static::$LOCAL_HOST.'/Members';

			$sdetails = unserialize($Q_Order['or_shipping_details']);
			// Purchaser Details
			$PFirstName = utf8_encode(escape(trim($sdetails['PurchaserDetails']['first_name'])));
			$PLastName = utf8_encode(escape(trim($sdetails['PurchaserDetails']['last_name'])));
			$PAddress = utf8_encode(escape(trim($sdetails['PurchaserDetails']['0_50A1'])));
			$PCity = utf8_encode($sdetails['PurchaserDetails']['0_50A2']);
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

			$b_state = utf8_encode(escape(trim($b_state) ) );

			$PCnTwoCode = getField( "select cn_two_code from countries where cn_name = '$b_country'");

			$PPostal = utf8_encode($sdetails['PurchaserDetails']['0_B4C0']);
			$PPhone = utf8_encode($sdetails['PurchaserDetails']['0_B4C1']);

			$email_address = utf8_encode($sdetails['PurchaserDetails']['Email']);
			$pos = strpos( $email_address, ">" );
			if( $pos )
				$email_address = substr( $email_address, $pos + 1 );
			$pos = strrpos( $email_address, "<" );
			if( $pos )
				$email_address = substr( $email_address, 0, $pos );

			// Shipping Details
			$SFirstName = utf8_encode(escape(trim($sdetails['ShippingDetails']['first_name'])));
			$SLastName = utf8_encode(escape(trim($sdetails['ShippingDetails']['last_name'])));
			$SAddress = utf8_encode(escape(trim($sdetails['ShippingDetails']['0_50A1'])));
			$SCity = utf8_encode($sdetails['ShippingDetails']['0_50A2']);
			$s_state_country = ' '.$sdetails['ShippingDetails']['0_50A4'];
			$pos = strpos( $s_state_country, "<BR>" );
			if( $pos )
			{
				$s_state = substr( $s_state_country, 0, $pos );
				$s_country = substr( $s_state_country, $pos + 4 );
			}
			else
			{
				$s_state = $s_state_country;
				$s_country = $s_state_country;
			}

			$s_state = utf8_encode(escape(trim($s_state) ) );

			$SCnTwoCode = getField( "select cn_two_code from countries where cn_name = '$s_country'");

			$SPostal = utf8_encode($sdetails['ShippingDetails']['0_B4C0']);

			$fields = array( 
				'mid' => static::$MID,
				'currency' => static::$CURRENCY,
				'amount' => number_format($totalPrice, 2, '.', ''),
				'order_ref' =>  $Q_Transaction['tr_id'],
				'success_url' => $ACK_URL,
				'fail_url' => $NACK_URL,
				'enable_3ds' => 'Y',
				'card_holder_first_name' => substr($PFirstName, 0, 60),
				'card_holder_last_name' => substr($PLastName, 0, 60),
				'bill_street_address' => substr($PAddress, 0, 60),
				'bill_city' => substr($PCity, 0, 50),
				'bill_state' => substr($b_state, 0, 50),
				'bill_zip' => substr($PPostal, 0, 9),
				'bill_country' => $PCnTwoCode,
				'email' => substr($email_address, 0, 128),

				'ship_street_address' => substr($SAddress, 0, 60 ),
				'ship_city' => substr($SCity, 0, 50),
				'ship_state' => substr($s_state, 0, 50),
				'ship_zip' => substr($SPostal, 0, 9),
				'ship_country' => $SCnTwoCode,
				'phone' => substr($PPhone, 0, 10),
				);

			$hash = static::sign( $fields, ['mid','currency','order_ref','amount'] );
			$fields['hash'] = $hash;

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );

			return $fields;
		}

		public static function getPOSTURL( )				/* acqraPaymentGatewayV1 */
		{
			$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v1.1/doPayment';
			return $POST_URL;
		}

		public static function getCurrencyHandled( )				/* acqraPaymentGatewayV1 */
		{
			return static::$CURRENCY;
		}

		public static function postScript()				/* acqraPaymentGatewayV1 */
		{
			$emit = '<script src="https://'.static::$GATEWAY_HOST.'/Assets/others/paymentPage.js"></script>';
			return  $emit;
		}

		public static function refund( $amount, $order_id )				/* acqraPaymentGatewayV1 */
		{
			// need to grab ACQRA:settlement_ref:5408564916566853103009  from OrderNotes for this or_id

			$foo = getField( 'select orn_text from shopsystem_order_notes join shopsystem_orders on orn_or_id = or_id where or_tr_id = '.((int)$order_id).' and orn_text like "ACQRA:settlement_ref:%" order by orn_id desc' );
			ss_log_message( $foo );
			if( $pos = strrpos( $foo, ':' ) )
			{
				ss_log_message( "pos = $pos" );
				$settlement_ref = substr( $foo, $pos+1 );
				$fields = array( 
				'mid' => static::$MID,
				'apikey' => static::$API_KEY,
				'currency' => static::$CURRENCY,
				'amount' => number_format($amount, 2, '.', ''),
				'settlement_ref' =>  $settlement_ref,
				);


				$hash = static::sign( $fields, ['mid', 'currency', 'settlement_ref', 'amount'] );
				$fields['hash'] = $hash;

				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );

				$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v1.1/refund_payment';
				$ch = curl_init( $POST_URL );
				curl_setopt( $ch, CURLOPT_POST, 1); 	
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields); 	
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 	
				curl_setopt( $ch, CURLOPT_HEADER, 0); 	
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0); 	
				curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0); 	
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  	
				if( ( $result = curl_exec($ch)) === false )
					ss_log_message( "CURL error :".curl_error($ch) );
				else
				{
					ss_log_message( "Response to POST" );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
				}
			}

			$ret = json_decode( $result );
			$ret->Message = 'Unknown';

			foreach ( $ret as $f=>$v )
			{
				if( $f == 'refund_status' )
					if( $v == static::$STATUS_REFUND_SUCCESS )
						$ret->Message = 'Refund successful';

				if( is_array( $v ) )
					foreach ($v as $e )
					{
						if( is_object( $e ) )
							if( property_exists( $e, 'refund_status' ) )
								if( $e->refund_status == static::$STATUS_REFUND_SUCCESS )
									$ret->Message = 'Refund successful';
					}
				else
				{
					if( is_object( $v ) )
						if( property_exists( $v, 'refund_status' ) )
							if( $v->refund_status == static::$STATUS_REFUND_SUCCESS )
								$ret->Message = 'Refund successful';
				}
			}

			return $ret;
		}

		public static function enquire( $tr_id )				/* acqraPaymentGatewayV1 */
		{
			$fields = array( 
				'mid' => static::$MID,
				'apikey' => static::$API_KEY,
				'order_ref' =>  $tr_id,
				);

			$hash = static::sign( $fields, ['mid', 'order_ref'] );
			$fields['hash'] = $hash;

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );

			$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v1.1/enquiry';
			$ch = curl_init( $POST_URL );
			curl_setopt( $ch, CURLOPT_POST, 1); 	
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields); 	
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 	
			curl_setopt( $ch, CURLOPT_HEADER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0); 	
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  	
			if( ( $result = curl_exec($ch)) === false )
				ss_log_message( "CURL error :".curl_error($ch) );
			else
			{
				ss_log_message( "Response to POST" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
			}

			return json_decode( $result );
		}
	}	// END of acqraPaymentGatewayV1

	class acqraPaymentGatewayV2 implements paymentGateway
	{
		// also outside CM  framework ~/acqra_v2/confirm.php
		// TEST
		/*
		protected static $LOCAL_HOST = 'test.acmerockets.com';
		protected static $GATEWAY_HOST = 'sandbox.acqra.com';
		 */
		// LIVE
		protected static $LOCAL_HOST = 'www.acmerockets.com';
		protected static $GATEWAY_HOST = 'api.acqra.com';

		/*private*/ protected static $MID = 'Invalid';
		/*private*/ protected static $SECURITY_KEY = 'Invalid';
		/*private*/ protected static $API_KEY = 'Invalid';

		/*private*/ protected static $CURRENCY = 'Invalid';

		/*private*/ protected static $VERSION = '2';
		/*public */ public static $STATUS_SUCCESS = 10000;
		/*public */ public static $STATUS_REFUND_SUBMITTED = 30001;
		/*public */ public static $STATUS_REFUND_SUCCESS = 30002;
		/*public */ public static $STATUS_REFUND_FAIL = 30003;
		public static $VOID_AUTHORIZATION_SUCCESS = 10011;

		static function refund_tx( $enq_status )	/*acqraPaymentGatewayV2*/
		{
			$show_refund_screen = false;

			foreach ( $enq_status as $f=>$v )
			{
				if( $f == 'transaction_id' )
					$transaction_no = $v;

				if( $f == 'status_code' )
					$show_refund_screen = true;

				if( is_array( $v ) )
					foreach ($v as $e )
					{
						if( is_object( $e ) )
						{
							if( property_exists( $e, 'transaction_id' ) )
								$transaction_no = $e->transaction_id;
							if( property_exists( $e, 'status_code' ) )
								if( $e->payment_status == static::$STATUS_SUCCESS )
									$show_refund_screen = true;
						}
					}
				else
				{
					if( is_object( $v ) )
					{
						if( property_exists( $v, 'transaction_id' ) )
							$transaction_no = $v->transaction_id;
						if( property_exists( $v, 'status_code' ) )
							if( $v->payment_status == static::$STATUS_SUCCESS )
								$show_refund_screen = true;
					}
				}
			}

			if( $show_refund_screen && $transaction_no )
				return $transaction_no;

		return false;
		}

// cigar0101001testUSD1764008405.08eMbVIvSko4clc5GfzeamoFFiqdCpkRd5

		static function sign ($fields)	/*acqraPaymentGatewayV2*/
		{
		  return hash('sha256', static::buildDataToSign($fields) );
		}

		static function buildDataToSign($fields)	/*acqraPaymentGatewayV2*/
		{
			$ret = implode( ',', $fields );
			ss_log_message( 'hashing ->'.$ret );
			return $ret;
		}

		static function getHiddenFormFields( $Q_Order, $Q_Transaction, $totalPrice )	/*acqraPaymentGatewayV2*/
		{

			$ACK_URL = 'https://'.static::$LOCAL_HOST.'/Shop_System/Service/Completed/tr_id/'.$Q_Transaction['tr_id']
						.'/tr_token/'.$Q_Transaction['tr_token'].'/us_id/'.$Q_Order['or_us_id'];
			$NACK_URL = 'https://'.static::$LOCAL_HOST.'/Members';
			$NOTIFY_URL = 'https://'.static::$LOCAL_HOST.'/acqra_v2/confirm_'.static::$MID.'.php';

			$sdetails = unserialize($Q_Order['or_shipping_details']);
			// Purchaser Details
			$PFirstName = utf8_encode(escape(trim($sdetails['PurchaserDetails']['first_name'])));
			$PLastName = utf8_encode(escape(trim($sdetails['PurchaserDetails']['last_name'])));
			$tmp = splitAt( trim($sdetails['PurchaserDetails']['0_50A1']), 50, 60 );
			$billa1 =  utf8_encode(escape($tmp[0]));
			$billa2 =  utf8_encode(escape($tmp[1]));
			$PCity = utf8_encode($sdetails['PurchaserDetails']['0_50A2']);
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

			$b_state = utf8_encode(escape(trim($b_state) ) );

			$PCnTwoCode = getField( "select cn_two_code from countries where cn_name = '$b_country'");

			$PPostal = utf8_encode($sdetails['PurchaserDetails']['0_B4C0']);
			$PPhone = utf8_encode($sdetails['PurchaserDetails']['0_B4C1']);

			$email_address = utf8_encode($sdetails['PurchaserDetails']['Email']);
			$pos = strpos( $email_address, ">" );
			if( $pos )
				$email_address = substr( $email_address, $pos + 1 );
			$pos = strrpos( $email_address, "<" );
			if( $pos )
				$email_address = substr( $email_address, 0, $pos );

			// Shipping Details
			$SFirstName = utf8_encode(escape(trim($sdetails['ShippingDetails']['first_name'])));
			$SLastName = utf8_encode(escape(trim($sdetails['ShippingDetails']['last_name'])));
			$SAddress = utf8_encode(escape(trim($sdetails['ShippingDetails']['0_50A1'])));
			$SCity = utf8_encode($sdetails['ShippingDetails']['0_50A2']);
			$s_state_country = ' '.$sdetails['ShippingDetails']['0_50A4'];
			$pos = strpos( $s_state_country, "<BR>" );
			if( $pos )
			{
				$s_state = substr( $s_state_country, 0, $pos );
				$s_country = substr( $s_state_country, $pos + 4 );
			}
			else
			{
				$s_state = $s_state_country;
				$s_country = $s_state_country;
			}

			$s_state = utf8_encode(escape(trim($s_state) ) );

			$SCnTwoCode = getField( "select cn_two_code from countries where cn_name = '$s_country'");

			$SPostal = utf8_encode($sdetails['ShippingDetails']['0_B4C0']);

/*
Billing address is missing / invalid
Billing country is missing / invalid -
Billing city is missing / invalid
Billing state is missing / invalid
Billing zip code is missing / invalid
Invalid hash value
*/

			$fields = array( 
				'version' => static::$VERSION,
				'mid' => static::$MID,
				'currency' => static::$CURRENCY,
				'amount' => number_format($totalPrice, 2, '.', ''),
				'order_ref' =>  $Q_Transaction['tr_id'],
				'notify_url' => $NOTIFY_URL,
				'success_url' => $ACK_URL,
				'fail_url' => $NACK_URL,
				'enable_3ds' => 'Y',
				'card_holder_first_name' => substr($PFirstName, 0, 60),
				'card_holder_last_name' => substr($PLastName, 0, 60),
				'bill_street_address' => $billa1,
				'bill_city' => $PCity,
				'bill_state' => $b_state,
				'bill_zip' => $PPostal,
				'bill_country' => $PCnTwoCode,
				'email' => substr($email_address, 0, 128),
				'phone' => substr($PPhone, 0, 10),
				);

			if( strlen( $billa2 ) )
				$fields[ 'bill_street_address2' ] = $billa2;

			$fields['hash'] = static::sign( [ $fields['mid'], static::$CURRENCY, $fields['order_ref'], $fields['amount'], static::$SECURITY_KEY ] );

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );

			return $fields;
		}

		public static function getPOSTURL( )	/*acqraPaymentGatewayV2*/
		{
			$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v2/do_payment';
			return $POST_URL;
		}

		public static function getCurrencyHandled( )	/*acqraPaymentGatewayV2*/
		{
			return static::$CURRENCY;
		}

		public static function postScript()	/*acqraPaymentGatewayV2*/
		{
			$emit = '<script src="https://'.static::$GATEWAY_HOST.'/js/paymentPagev2.js"></script>';
			return  $emit;
		}

		public static function voidtx( $transaction_no )	/*acqraPaymentGatewayV2*/
		{
			$fields = [ 'mid' => static::$MID, 'transaction_id' => $transaction_no, 'apikey' => static::$API_KEY ];

			$hash = static::sign( [ $fields['mid'], $fields['transaction_id'], static::$SECURITY_KEY ] );
			$fields['hash'] = $hash;

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );

			$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v2/void_auth';
			$ch = curl_init( $POST_URL );
			curl_setopt( $ch, CURLOPT_POST, 1); 	
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields); 	
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 	
			curl_setopt( $ch, CURLOPT_HEADER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0); 	
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  	
			if( ( $result = curl_exec($ch)) === false )
				ss_log_message( "CURL error :".curl_error($ch) );
			else
			{
				ss_log_message( "Response to POST" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
			}

			$ret = json_decode( $result );
			$ret->Message = $ret->status_message;

			return $ret;
		}

		public static function capturetx( $transaction_no, $tr_id )	/*acqraPaymentGatewayV2*/
		{
			$fields = [ 'mid' => static::$MID, 'transaction_id' => $transaction_no, 'apikey' => static::$API_KEY ];

			$payment_info = getRow( 'select tr_currency_code, tr_total from transactions where tr_id = '.$tr_id );
			$fields['currency'] = $payment_info['tr_currency_code'];
			$fields['amount'] = number_format( $payment_info['tr_total'], 2, '.', '');

			$hash = static::sign( [ $fields['mid'], $fields['currency'], $fields['transaction_id'], $fields['amount'], static::$SECURITY_KEY ] );
			$fields['hash'] = $hash;

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );

			$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v2/capture_payment';
			$ch = curl_init( $POST_URL );
			curl_setopt( $ch, CURLOPT_POST, 1); 	
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields); 	
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 	
			curl_setopt( $ch, CURLOPT_HEADER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0); 	
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  	
			if( ( $result = curl_exec($ch)) === false )
				ss_log_message( "CURL error :".curl_error($ch) );
			else
			{
				ss_log_message( "Response to POST" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
			}

			$ret = json_decode( $result );
			$ret->Message = $ret->status_message;

			return $ret;
		}
		public static function refund( $amount, $transaction_no )	/*acqraPaymentGatewayV2*/
		{
			$fields = [ 'mid' => static::$MID, 'apikey' => static::$API_KEY, 'currency' => static::$CURRENCY, 'amount' => $amount, 'transaction_id' => $transaction_no ];

			$hash = static::sign( [ $fields['mid'], $fields['currency'], $fields['transaction_id'], $fields['amount'], static::$SECURITY_KEY ] );
			$fields['hash'] = $hash;

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );

			$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v2/refund_payment';
			$ch = curl_init( $POST_URL );
			curl_setopt( $ch, CURLOPT_POST, 1); 	
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields); 	
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 	
			curl_setopt( $ch, CURLOPT_HEADER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0); 	
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  	
			if( ( $result = curl_exec($ch)) === false )
				ss_log_message( "CURL error :".curl_error($ch) );
			else
			{
				ss_log_message( "Response to POST" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
			}

			$ret = json_decode( $result );
			$ret->Message = $ret->status_message;

			return $ret;
		}

		public static function enquire( $tr_id )	/*acqraPaymentGatewayV2*/
		{
			$fields = array( 
				'mid' => static::$MID,
				'apikey' => static::$API_KEY,
				);

			// ACQRA_V2:transaction_id:19112300010532180440008601 ACQRA_V2:status_code:10010 ACQRA_V2:status_message:Payment is open for 3DS ACQRA_V2:currency:USD ACQRA_V2:amount:230 ACQRA_V2:settlement_ref:5744385678176641803024

			$foo = getField( 'select orn_text from shopsystem_order_notes join shopsystem_orders on orn_or_id = or_id where or_tr_id = '.((int)$tr_id).' and orn_text like "ACQRA_V2%"' );
			ss_log_message( $foo );
			$needle = '[transaction_id] =>';
			if( $pos = strrpos( $foo, $needle ) )
			{
				ss_log_message( "pos = $pos" );
				$fields['transaction_id'] = substr( $foo, $pos+strlen($needle) );
				if( $space = strpos( $fields['transaction_id'], ' ' ) )
					$fields['transaction_id'] = substr( $fields['transaction_id'], 0, $space );

				$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v2/enquiry';

				$hash = static::sign( [ $fields['mid'], $fields['transaction_id'], static::$SECURITY_KEY ] );
				$fields['hash'] = $hash;
			}
			else
			{
				// 6 hours difference between zurich and HK
				$foo = getField( 'select DATE_ADD(or_recorded, INTERVAL 7 hour) from shopsystem_orders where or_tr_id = '.((int)$tr_id) );
				ss_log_message( $foo );
				$fields['transaction_date'] = $foo[2].$foo[3].$foo[5].$foo[6].$foo[8].$foo[9];
				$fields['order_ref'] =  $tr_id;

				$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v2/enquiry_orderref';

				$hash = static::sign( [ $fields['mid'], $fields['order_ref'], $fields['transaction_date'], static::$SECURITY_KEY ] );
				$fields['hash'] = $hash;
			}

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );

			$ch = curl_init( $POST_URL );
			curl_setopt( $ch, CURLOPT_POST, 1); 	
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields); 	
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 	
			curl_setopt( $ch, CURLOPT_HEADER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0); 	
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  	
			if( ( $result = curl_exec($ch)) === false )
				ss_log_message( "CURL error :".curl_error($ch) );
			else
			{
				ss_log_message( "Response to POST" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
			}

			$foo = json_decode( $result );
			//$foo->TxDate = $fields['transaction_date'];
			return $foo;
		}
	}	// END of acqraPaymentGatewayV2

	// the old payment gateway, kept for refunds and posterity
	class acqraPaymentGateway extends acqraPaymentGatewayV1
	{
		// TEST
		// deprecated

		// LIVE
		protected static $MID = 'fd78sy87ah';
		protected static $SECURITY_KEY = 'q1fd78sy87afd78sy87afd78sy87ahr5';
		protected static $API_KEY = 'SVfd78sy87afd78sy87afd78sy87ah09';
		protected static $CURRENCY = 'EUR';	
	}

	//class wingLungPaymentGateway extends acqraPaymentGatewayV1
	class wingLungPaymentGateway extends acqraPaymentGatewayV2
	{
		// also outside CM  framework ~/acqra/confirm.php

		// TEST
		/*
		 * protected static $MID = 'fd78sy87ah02test';
		protected static $SECURITY_KEY = 'nmqfd78sy87ahfd78sy87ahBlLQi3hbe';
		protected static $API_KEY = 'akY4cjN0dW40WFfd78sy87afd78sy87ah2V0ay9EdW1HVFZUN0c5bGJWZz0=';
		protected static $CURRENCY = 'EUR';
		 */

		// LIVE
		protected static $MID = 'pfd78sy87ah2';
		protected static $SECURITY_KEY = 'zgY3fd78sy87ah4NRz5QpjDAvgOxBZuj';
		protected static $API_KEY = 'NHUyWVJOdnhBVWfd78sy87ahYktCUT09';
		protected static $CURRENCY = 'EUR';
	}

	class wingHangPaymentGatewayUSD extends acqraPaymentGatewayV2
	{
		// TEST
		/*
		protected static $MID = 'cfd78sy87ah2test';
		protected static $SECURITY_KEY = '7xz7GTjpHEIgfd78sy87ahPn55e3eqv5';
		protected static $API_KEY = 'dTZhaVV5NnZlNEUxMfd78sy87ahBbGtMUzlMZGJoUzdncmVmRjR6ZEErdz0=';
		protected static $CURRENCY = 'USD';
		*/

		// LIVE
		protected static $MID = 'cfd78sy87ah2';
		protected static $SECURITY_KEY = 'qVlBAwfd78sy87ahkYsgncwb3rCrzkIM';
		protected static $API_KEY = 'RmhzeUp1a2fd78sy87ahQmFDV3ZSQT09';
		protected static $CURRENCY = 'USD';
	}

	class wingHangPaymentGatewayHKD extends acqraPaymentGatewayV2
	{
		protected static $MID = 'cfd78sy87ah2';
		protected static $SECURITY_KEY = 'qVlBAwfd78sy87ahkYsgncwb3rCrzkIM';
		protected static $API_KEY = 'RmhzeUp1a2Yfd78sy87ahmFDV3ZSQT09';
		protected static $CURRENCY = 'HKD';
	}

	class wingHangPaymentGatewayNZD extends acqraPaymentGatewayV2
	{
		protected static $MID = 'cfd78sy87ah2';
		protected static $SECURITY_KEY = 'qVlBfd78sy87ahBZfd78sy87ahCrzkIM';
		protected static $API_KEY = 'RmhzeUp1a2fd78sy87ahQmFDV3ZSQT09';
		protected static $CURRENCY = 'NZD';
	}

	class wingHangPaymentGatewaySGD extends acqraPaymentGatewayV2
	{
		protected static $MID = 'cfd78sy87ah2';
		protected static $SECURITY_KEY = 'qVlBAwDoasdfsadfdsafdsazkIM';
		protected static $API_KEY = 'RmhzeUp1a2YzUkw2MmI1QmFDV3ZSQT09';
		protected static $CURRENCY = 'SGD';
	}

	class wingHangPaymentGatewayAUD extends acqraPaymentGatewayV2
	{
		protected static $MID = 'cfd78sy87ah2';
		protected static $SECURITY_KEY = 'qVlBAwDoasdfsadfdsafdsazkIM';
		protected static $API_KEY = 'RmhzeUp1a2YzUkw2MmI1QmFDV3ZSQT09';
		protected static $CURRENCY = 'AUD';
	}

	class wingHangPaymentGatewayEUR extends acqraPaymentGatewayV2
	{
		protected static $MID = 'cfd78sy87ah2';
		protected static $SECURITY_KEY = 'qVlBAwDoasdfsadfdsafdsazkIM';
		protected static $API_KEY = 'RmhzeUp1a2YzUkw2MmI1QmFDV3ZSQT09';
		protected static $CURRENCY = 'EUR';
	}

	class wingHangPaymentGatewayJPY extends acqraPaymentGatewayV2
	{
		protected static $MID = 'cfd78sy87ah2';
		protected static $SECURITY_KEY = 'qVlBAwDoasdfsadfdsafdsazkIM';
		protected static $API_KEY = 'RmhzeUp1a2YzUkw2MmI1QmFDV3ZSQT09';
		protected static $CURRENCY = 'JPY';
	}

	class wingHangPaymentGatewayKRW extends acqraPaymentGatewayV2
	{
		protected static $MID = 'cfd78sy87ah2';
		protected static $SECURITY_KEY = 'qVlBAwDoasdfsadfdsafdsazkIM';
		protected static $API_KEY = 'RmhzeUp1a2YzUkw2MmI1QmFDV3ZSQT09';
		protected static $CURRENCY = 'KRW';
	}

	class wingHangPaymentGatewayGBP extends acqraPaymentGatewayV2
	{
		protected static $MID = 'cfd78sy87ah2';
		protected static $SECURITY_KEY = 'qVlBAwDoasdfsadfdsafdsazkIM';
		protected static $API_KEY = 'RmhzeUp1a2YzUkw2MmI1QmFDV3ZSQT09';
		protected static $CURRENCY = 'GBP';
	}

	class acqraUnionpayPaymentGateway implements paymentGateway
	{
		// also outside CM  framework ~/acqra/confirm.php
		//*private*/ protected static $MID = 'f9ds87yfds';
		//*private*/ protected static $SECURITY_KEY = 'f6zf9ds87yfdsonz0tzn9qs1kkd5wljm';
		//*private*/ protected static $LOCAL_HOST = 'test.acmerockets.com';
		//*private*/ protected static $GATEWAY_HOST = 'sandbox.acqra.com';

		/*
			MID : f9ds87yfds01
			Security key : VaB3pHuf9ds87yfdsoSw71ora6EcruX0
			Transaction Currency: 344 (HKD)
			Settlement Currency: 344 (HKD)
		*/

		/*private*/ protected static $MID = 'f9ds87yfds01';
		/*private*/ protected static $SECURITY_KEY = 'VaB3pHuZf9ds87yfdsSw71ora6EcruX0';
		/*private*/ protected static $LOCAL_HOST = 'www.acmerockets.com';
		/*private*/ protected static $GATEWAY_HOST = 'api.acqra.com';

		/*private*/ protected static $CURRENCY = 'HKD';
		/*private*/ protected static $CURRENCY_ISO_ID = '344';
		/*private*/ protected static $VERSION = '1.1';
		/*private*/ protected static $CHANNEL_ID = 'upop';
		/*public */ public static $STATUS_SUCCESS = 1000;
		/*public */ public static $STATUS_REFUND_SUBMITTED = 8002;
		/*public */ public static $STATUS_REFUND_SUCCESS = 8003;

		/*
		MID: f9ds87yfds
		Security Key: f6z6ywv8cegeqonz0tzn9qs1kkd5wljm
		Payment Channel: upop

		Testing Card (Provided by UPOP)
		Credit card：6250947000000014
		mobile：+852 11112222
		cvn2:  123
		exp date: month 12 year 33
		SMS Code on PC: 111111
		SMS Code on Mobile: 123456

		Debit card：6223164991230014
		mobile：13012345678
		PIN: 111111
		cvn2:  123
		exp date: month 12 year 33
		SMS Code on PC：111111
		SMS Code on Mobile：123456
		*/

		static function refund_tx( $enq_status )						/*acqraUnionpayPaymentGateway*/
		{
			foreach ( $enq_status as $f=>$v )
			{
				if( is_array( $v ) )
					foreach ($v as $e )
					{
						if( is_object( $e ) )
						{
							if( property_exists( $e, 'transaction_no' ) )
								$transaction_no = $e->transaction_no;
							if( property_exists( $e, 'payment_status' ) )
								if( $e->payment_status == acqraUnionpayPaymentGateway::$STATUS_SUCCESS )
									$show_refund_screen = true;
						}
					}
				else
				{
					if( is_object( $v ) )
					{
						if( property_exists( $v, 'transaction_no' ) )
							$transaction_no = $v->transaction_no;
						if( property_exists( $v, 'payment_status' ) )
							if( $v->payment_status == acqraUnionpayPaymentGateway::$STATUS_SUCCESS )
								$show_refund_screen = true;
					}
				}
			}

			if( $show_refund_screen && $transaction_no )
				return $transaction_no;

		return false;
		}

		static function sign ($fields)						/*acqraUnionpayPaymentGateway*/
		{
		  return hash('md5', acqraUnionpayPaymentGateway::buildDataToSign($fields) );
		}

		static function buildDataToSign($fields)						/*acqraUnionpayPaymentGateway*/
		{
			$ret = implode( '', $fields );
			ss_log_message( 'hashing ->'.$ret );
			return $ret;
		}

		static function getHiddenFormFields( $Q_Order, $Q_Transaction, $totalPrice )						/*acqraUnionpayPaymentGateway*/
		{

			$ACK_URL = 'https://'.static::$LOCAL_HOST.'/Shop_System/Service/Completed/tr_id/'.$Q_Transaction['tr_id']
						.'/tr_token/'.$Q_Transaction['tr_token'].'/us_id/'.$Q_Order['or_us_id'];
			$NACK_URL = 'https://'.static::$LOCAL_HOST.'/Members';
			$NOTIFY_URL = 'https://'.static::$LOCAL_HOST.'/confirm.php';

			$sdetails = unserialize($Q_Order['or_shipping_details']);
			// Purchaser Details
			$PFirstName = utf8_encode(trim($sdetails['PurchaserDetails']['first_name']));
			$PLastName = utf8_encode(trim($sdetails['PurchaserDetails']['last_name']));
			$PAddress = utf8_encode(trim($sdetails['PurchaserDetails']['0_50A1']));
			$PCity = utf8_encode($sdetails['PurchaserDetails']['0_50A2']);
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

			$b_state = utf8_encode(escape(trim($b_state) ) );

			$PCnTwoCode = getField( "select cn_two_code from countries where cn_name = '$b_country'");

			$PPostal = utf8_encode($sdetails['PurchaserDetails']['0_B4C0']);
			$PPhone = utf8_encode($sdetails['PurchaserDetails']['0_B4C1']);

			$email_address = utf8_encode($sdetails['PurchaserDetails']['Email']);
			$pos = strpos( $email_address, ">" );
			if( $pos )
				$email_address = substr( $email_address, $pos + 1 );
			$pos = strrpos( $email_address, "<" );
			if( $pos )
				$email_address = substr( $email_address, 0, $pos );

			// Shipping Details
			$SFirstName = utf8_encode(escape(trim($sdetails['ShippingDetails']['first_name'])));
			$SLastName = utf8_encode(escape(trim($sdetails['ShippingDetails']['last_name'])));
			$SAddress = utf8_encode(escape(trim($sdetails['ShippingDetails']['0_50A1'])));
			$SCity = utf8_encode($sdetails['ShippingDetails']['0_50A2']);
			$s_state_country = ' '.$sdetails['ShippingDetails']['0_50A4'];
			$pos = strpos( $s_state_country, "<BR>" );
			if( $pos )
			{
				$s_state = substr( $s_state_country, 0, $pos );
				$s_country = substr( $s_state_country, $pos + 4 );
			}
			else
			{
				$s_state = $s_state_country;
				$s_country = $s_state_country;
			}

			$s_state = utf8_encode(escape(trim($s_state) ) );

			$SCnTwoCode = getField( "select cn_two_code from countries where cn_name = '$s_country'");

			$SPostal = utf8_encode($sdetails['ShippingDetails']['0_B4C0']);

			$fields = array( 
				'version' => static::$VERSION,
				'mid' => static::$MID,
				'currency' => static::$CURRENCY_ISO_ID,
				'amount' => number_format($totalPrice, 2, '.', ''),
				'channel_id' => static::$CHANNEL_ID,
				'order_id' =>  $Q_Transaction['tr_id'],
				'notify_url' => $NOTIFY_URL,
				'return_url' => $ACK_URL,
//				'fail_url' => $NACK_URL,
				'first_name' => substr($PFirstName, 0, 60),
				'last_name' => substr($PLastName, 0, 60),
				'email' => substr($email_address, 0, 128),
				'phone' => substr($PPhone, 0, 10),
				);

			$fields['hash'] = acqraUnionpayPaymentGateway::sign( [ $fields['mid'], static::$SECURITY_KEY, $fields['order_id'], $fields['amount'] ] );

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );

			return $fields;
		}

		public static function getPOSTURL( )						/*acqraUnionpayPaymentGateway*/
		{
			$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v1.1c/submit';
			return $POST_URL;
		}

		public static function getCurrencyHandled( )						/*acqraUnionpayPaymentGateway*/
		{
			return static::$CURRENCY;
		}

		public static function postScript()						/*acqraUnionpayPaymentGateway*/
		{
			$emit = '<script src="https://'.static::$GATEWAY_HOST.'/Assets/others/paymentPage.js"></script>';
			return  $emit;
		}

		public static function refund( $amount, $transaction_no )						/*acqraUnionpayPaymentGateway*/
		{
			$fields = [ 'version' => static::$VERSION, 'mid' => static::$MID, 'transaction_no' => $transaction_no, 'refund_amount' => $amount ];

			$hash = acqraUnionpayPaymentGateway::sign( [ $fields['mid'], static::$SECURITY_KEY, $fields['refund_amount'], $fields['transaction_no'] ] );
			$fields['hash'] = $hash;

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );

			$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v1.1c/refund';
			$ch = curl_init( $POST_URL );
			curl_setopt( $ch, CURLOPT_POST, 1); 	
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields); 	
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 	
			curl_setopt( $ch, CURLOPT_HEADER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0); 	
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  	
			if( ( $result = curl_exec($ch)) === false )
				ss_log_message( "CURL error :".curl_error($ch) );
			else
			{
				ss_log_message( "Response to POST" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
			}

			$ret = json_decode( $result );
			$ret->Message = 'Unknown';

			foreach ( $ret as $f=>$v )
			{
				if( $f == 'refund_status' )
					if( $v == static::$STATUS_REFUND_SUCCESS )
						$ret->Message = 'Refund successful';

				if( is_array( $v ) )
					foreach ($v as $e )
					{
						if( is_object( $e ) )
							if( property_exists( $e, 'refund_status' ) )
								if( $e->refund_status == static::$STATUS_REFUND_SUCCESS )
									$ret->Message = 'Refund successful';
					}
				else
				{
					if( is_object( $v ) )
						if( property_exists( $v, 'refund_status' ) )
							if( $v->refund_status == static::$STATUS_REFUND_SUCCESS )
								$ret->Message = 'Refund successful';
				}
			}

			return $ret;
		}

		public static function enquire( $tr_id )						/*acqraUnionpayPaymentGateway*/
		{
			$fields = array( 
				'version' => static::$VERSION,
				'mid' => static::$MID,
				'order_id' =>  $tr_id,
				);

			$foo = getField( 'select orn_text from shopsystem_order_notes join shopsystem_orders on orn_or_id = or_id where or_tr_id = '.((int)$tr_id).' and orn_text like "Payment Acqra Unionpay Gateway data%"' );
			ss_log_message( $foo );
			$needle = '[transaction_time] =>';
			if( $pos = strrpos( $foo, $needle ) )
			{
				ss_log_message( "pos = $pos" );
				$txdatetime = substr( $foo, $pos+strlen($needle) );
				$fields['transaction_date'] = /*$txdatetime[1].$txdatetime[2].*/$txdatetime[3].$txdatetime[4].$txdatetime[6].$txdatetime[7].$txdatetime[9].$txdatetime[10];
			}
			else
			{
				// 6 hours difference between zurich and HK
				$foo = getField( 'select DATE_ADD(or_recorded, INTERVAL 6 hour) from shopsystem_orders where or_tr_id = '.((int)$tr_id) );
				ss_log_message( $foo );
				$fields['transaction_date'] = $foo[2].$foo[3].$foo[5].$foo[6].$foo[8].$foo[9];
			}

			$hash = acqraUnionpayPaymentGateway::sign( [ $fields['mid'], static::$SECURITY_KEY, $fields['order_id'], $fields['transaction_date'] ] );
			$fields['hash'] = $hash;

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );

			$POST_URL = 'https://'.static::$GATEWAY_HOST.'/v1.1c/enquiry_orderid';
			$ch = curl_init( $POST_URL );
			curl_setopt( $ch, CURLOPT_POST, 1); 	
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields); 	
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 	
			curl_setopt( $ch, CURLOPT_HEADER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0); 	
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0); 	
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  	
			if( ( $result = curl_exec($ch)) === false )
				ss_log_message( "CURL error :".curl_error($ch) );
			else
			{
				ss_log_message( "Response to POST" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
			}

			return json_decode( $result );
		}


	}	/* END of class acqraUnionpayPaymentGateway*/

?>
