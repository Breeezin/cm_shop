<?php

global $bank, $link, $tr_id, $order_details, $gateway;
$bank = -1;
$tr_id = -1;

function ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $message_r )
{
	if( is_array(  $message_r ) or is_object( $message_r ) )
		$message = print_r($message_r, true );
	else
		$message = "primitive passed to ss_log_message_r()";

	return ss_log_message( $message );
}

function ss_log_message( $message )
{
	global $bank, $tr_id;

	$sid = session_id();
	if( strlen( $sid ) > 4 )
		$s = substr( $sid, strlen( $sid ) - 4 );
	else
		$s = "NONE";
	$t = strftime( '%F %T' );
	$file = fopen( "/tmp/test_messages", "a+" );
	fwrite( $file, "$s:$t:" );
	fwrite( $file, "PAYMENT bank:$bank transaction:$tr_id " );
	fwrite( $file, $message );
	fwrite( $file, "\n" );
	fclose( $file );
}

// error handler function
function imErrorHandler ($errno, $errstr, $errfile, $errline, $vars)
{
	ss_log_message( "ERROR #:$errno str:$errstr where:$errfile:$errline" );
	return true;
}

function escape( $input )
{
	global $bank, $link, $tr_id;

	$input = trim( $input );
	//$input = mysqli_real_escape_string( $link, substr( $input, 0, strspn( $input,  "AÁBCDEÉFGHÍIJKLMNÑOÓPQRSTUÚVWXYZaábcdeéfghíijklmnñoópqrstuúvwxyz0123456789@#-.,_\\' " ) ) );
	$input = mysqli_real_escape_string( $link, substr( $input, 0, strspn( $input,  "ABCDEFGHIJKLMNOPQRSTUVWXYZaábcdeéfghíijklmnñoópqrstuúvwxyz0123456789@#-.,_\\' " ) ) );

	return $input;
}

function safe( $input )
{
	$input = trim( $input );
	//$input = substr( $input, 0, strspn( $input,  "AÁBCDEÉFGHÍIJKLMNÑOÓPQRSTUÚVWXYZaábcdeéfghíijklmnñoópqrstuúvwxyz0123456789@.,\\' " ) );
	$input = substr( $input, 0, strspn( $input,  "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789" ) );

	return $input;
}

function ss_absolutePathToURL($path,$full = false) {
	$remove = getcwd()."/";
	$path = stri_replace($remove,'',$path);
	return 'http://www.acmerockets.com/'.$path;
}

function ss_withoutTrailingSlash($dir) {
	if (substr($dir,-1) == '/') return substr($dir,1,-1);
	return $dir;
}

function init( $which_bank, $transaction )
{
	global $bank, $link, $tr_id, $order_details, $gateway;

	$bank = (int)$which_bank;
	$tr_id = (int)$transaction;

	ss_log_message( "init()" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );

	set_error_handler("imErrorHandler");
	session_start();

    require_once('Custom/GlobalSettings.php');

    $link = mysqli_connect($dbCfg['dbServer'],$dbCfg['dbUsername'], $dbCfg['dbPassword']);
	if( $link )
	{
		if( mysqli_select_db( $link, $dbCfg['dbName'] ) )
		{
			mysqli_set_charset($link, 'latin1' );
			if( $tr_id )
			{
				$sql = "START TRANSACTION";
				ss_log_message( "SQL:".$sql );
				if( !mysqli_query( $link, $sql ) )
				{
					mysqli_close($link);
					return false;
				}

				$sql = "select * from transactions join shopsystem_orders on or_tr_id = tr_id where tr_id = $tr_id";
				ss_log_message( "SQL:".$sql );
				if( $q = mysqli_query( $link, $sql ) )
					$order_details = mysqli_fetch_assoc( $q );
				else
				{
					ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
					mysqli_close($link);
					return false;
				}

				if( $order_details && $order_details['tr_bank'] && ($order_details['tr_bank'] != $bank ) )
				{
					$bank = $order_details['tr_bank'];
					ss_log_message( "Bank is now $bank" );
				}

				$sql = "select * from payment_gateways where pg_id = $bank";
				ss_log_message( "SQL:".$sql );
				if( $q = mysqli_query( $link, $sql ) )
					$gateway = mysqli_fetch_assoc( $q );
				else
				{
					ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
					mysqli_close($link);
					return false;
				}

				if( $order_details && $gateway )
					return true;
			}
			else
				return true;
		}
		else
		{
			ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
        	ss_log_message( "Error selecting database '".$dbCfg['dbName']."'" );
			mysqli_close($link);
		}
	}
	else
	{
		ss_log_message("Could not connect to '".$dbCfg['dbServer']."' as '".$dbCfg['dbUsername']."'");
	}

	return false;
}

// From PHP Manual User contributed notes: dave at [nospam]netready dot biz
function stri_replace($find, $replace, $string )
{
	$parts = explode( strtolower($find), strtolower($string) );
	$pos = 0;
	foreach( $parts as $key=>$part ){
		$parts[ $key ] = substr($string, $pos, strlen($part));
		$pos += strlen($part) + strlen($find);
	}
	return( join( $replace, $parts ) );
}

function timerStart( $text )
{
	ss_log_message( $text );
}

function timerFinish( $text )
{
	ss_log_message( $text );
}

function ipcheck( $ranges, $bank, $tr_id = 0 )
{
	global $bank, $link, $tr_id, $order_details, $gateway;

	$found = false;

	if( is_array( $ranges ) )
	{
		foreach ( $ranges as $range )
			if( !strncmp( $_SERVER['REMOTE_ADDR'], $range, strlen( $range ) ) )
				$found = true;
	}
	else
		if( !strncmp( $_SERVER['REMOTE_ADDR'], $ranges, strlen( $range ) ) )
			$found = true;

	if( !$found )
	{
		ss_log_message( "Security voilation, {$_SERVER['REMOTE_ADDR']} not in acceptable range" );

		if( $bank > 0 )
		{
			ss_log_message( "Disabling bank ".(int) $bank );

			$to = array( "rex@admin.com", "vicky@admin.com", "rolfbjork@gmail.com", "macbjorck@me.com" );
			//$to = array( "rex@admin.com");
			// disable gateway
			$sql = "update payment_gateway_options set po_active = 0 where po_pg_id = ".(int) $bank;
			ss_log_message( $sql );
			if( !mysqli_query( $link, $sql ) )
			{
				ss_log_message( "unable to execute > $sql" );
				ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
			}

			include_once( "System/Libraries/Rmail/Rmail.php" );
			$mailer = new Rmail();
			$mailer->setFrom('admin@acmerockets.com');		// irrelevant, overwritten
			$mailer->setSubject("Critical: AcmeRockets Payment Gateway $bank Disabled");
			$mailer->setText("AcmeRockets Payment Gateway $bank Disabled on Transaction $tr_id");
			$mailer->setSMTPParams("localhost", 587);		// out via submit to gmail
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $mailer );
			$result = $mailer->send( $to, 'smtp');
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
		}
		die;
	}
}

function done_ack( )
{
	global $bank, $link, $tr_id, $order_details, $gateway;

	if( !mysqli_query( $link, "commit" ) )
		ss_log_message( "commit() failed" );

	// send confirm email

	$subject = "Order Receipt";

	include_once( "System/Libraries/Rmail/Rmail.php" );

	$sql = "select * from assets where as_id = 514";
	ss_log_message( "SQL:".$sql );
	if( $q = mysqli_query( $link, $sql ) )
		$shop_asset = mysqli_fetch_assoc( $q );
	else
	{
		ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
		ss_log_message( "Failed" );
		die;
	}

	$sql = "SELECT * FROM assets WHERE as_type LIKE 'users'";
	ss_log_message( "SQL:".$sql );
	if( $q = mysqli_query( $link, $sql ) )
		$user_asset = mysqli_fetch_assoc( $q );
	else
	{
		ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
		ss_log_message( "Failed" );
		die;
	}

//	mysqli_close($link);

	require_once( 'System/Core/ListFunctions.php' );

	$shop_assetCereal = unserialize( $shop_asset['as_serialized'] );
	$user_assetCereal = unserialize( $user_asset['as_serialized'] );

//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $shop_assetCereal );
//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $user_assetCereal );

	$fieldNamesArray = array();	 			

	if( is_array( $user_assetCereal ) && array_key_exists( 'AST_USER_FIELDS', $user_assetCereal ) )
	{
		$fieldsArray = unserialize($user_assetCereal['AST_USER_FIELDS']);
		foreach($fieldsArray as $fieldDef)
		{	
			if( array_key_exists( 'uuid', $fieldDef ) && array_key_exists( 'name', $fieldDef ) )
				$fieldNamesArray[$fieldDef['uuid']] = $fieldDef['name'];			
		}
	}

//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fieldNamesArray );

	$emailText = $shop_assetCereal['AST_SHOPSYSTEM_CLIENT_CREDITCARDEMAIL'];

	$allTags = array();	
	// get details from purchaser and shipping
	// put into the tag table with value
	$shippingDetails = unserialize($order_details['or_shipping_details']);
	if (!array_key_exists('first_name',$shippingDetails['ShippingDetails']))
	{
		if (array_key_exists('Name',$shippingDetails['ShippingDetails']))
		{
			$aValue = $shippingDetails['ShippingDetails']['Name'];
			$shippingDetails['ShippingDetails']['first_name'] = ListFirst($aValue,' ');
			$shippingDetails['ShippingDetails']['last_name'] = ListLast($aValue,' ');
		}
	}

	foreach($shippingDetails['ShippingDetails'] as $key => $aValue)
	{
		if (array_key_exists($key, $fieldNamesArray)) 
			$allTags["S.".$fieldNamesArray[$key]] = $aValue; 				
		else 
			$allTags["S.".$key] = $aValue; 				
	}		

	if (!array_key_exists('first_name',$shippingDetails['PurchaserDetails'])) {
		$aValue = $shippingDetails['PurchaserDetails']['Name'];
		$shippingDetails['PurchaserDetails']['first_name'] = ListFirst($aValue,' ');
		$shippingDetails['PurchaserDetails']['last_name'] = ListLast($aValue,' ');
	}			

	foreach($shippingDetails['PurchaserDetails'] as $key => $aValue)
	{
		if (array_key_exists($key, $fieldNamesArray)) 
			$allTags["P.".$fieldNamesArray[$key]] = $aValue; 				
		else 
			$allTags["P.".$key] = $aValue; 					
	}

	$ordetails = unserialize($order_details['or_details']);
	$allTags['OrderDetails'] = $ordetails['BasketHTML'];
	$allTags['TotalCharge'] = $order_details['tr_charge_total'];
	$allTags['Total'] = $order_details['tr_total'];
	$allTags['ChargingName'] = $gateway['pg_charging_name'];
	$allTags['OrderNumber'] = $order_details['tr_id'];

//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $allTags );
	foreach($allTags as $tag => $value)
		$emailText = stri_replace("[{$tag}]",$value,$emailText);

//	ss_log_message( $emailText );

	// html manipulation from Email.Send
	$styleSheet = 'System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css';
	$ExtraStyleSheets = '<link rel="stylesheet" href="'.$styleSheet.'" type="text/css">';
	$data = array(
		'ExtraStyleSheets'	=>	$ExtraStyleSheets,
		'Content'	=>	$emailText,
	);

//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $data );

	global $cfg;
	$cfg['cacheTemplates'] = true;
	$cfg['plaintext_server'] = 'http://www.acmerockets.com/';
	$cfg['currentSiteFolder'] = 'acmerockets';

	require_once( 'System/Core/Functions/Template.php' );

	//$htmlMessage = processTemplate("System/Classes/Tools/Email/Templates/Email.html",$data);
	$htmlMessage = processTemplate("Custom/ContentStore/Templates/acmerockets/Email/Email.html",$data);

//	ss_log_message( $htmlMessage );

	$mailer = new Rmail();
	$mailer->setFrom('admin@acmerockets.com');		// irrelevant, overwritten
	$mailer->setSubject($subject);
	$mailer->setHTML($htmlMessage);				
	$mailer->setSMTPParams("localhost", 587);		// out via submit to gmail
//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $mailer );
	$result = $mailer->send(array($order_details['or_purchaser_email']), 'smtp');

	ss_log_message( 'email send result = '.$result );
}

function done_nak( )
{
	global $bank, $link, $tr_id;

	if( !mysqli_query( $link, "commit" ) )
		ss_log_message( "commit() failed" );

	ss_log_message( "Failing" );
	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, debug_backtrace() );

//	if( !mysqli_query( $link, "rollback" ) )
//		ss_log_message( "rollback() failed" );

	mysqli_close($link);
}

function mark_paid( )
{
	global $bank, $link, $tr_id, $order_details, $gateway, $decodedPaymentData;

	ss_log_message( "update transactions set tr_completed = 1 where tr_id = $tr_id" );

	if( !mysqli_query( $link, "update transactions set tr_completed = 1 where tr_id = $tr_id" ) )
	{
		ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
		ss_log_message( "update transactions failed" );
		return false;
	}

	ss_log_message( "update shopsystem_orders
			set or_paid = now(),
				or_paid_not_shipped = now(),
				or_charge_list = NULL,
				or_card_denied = NULL, 
				or_cancelled = NULL,
				or_shipped = NULL
			where or_tr_id = $tr_id" );

	if( !mysqli_query( $link, "update shopsystem_orders
			set or_paid = now(),
				or_paid_not_shipped = now(),
				or_charge_list = NULL,
				or_card_denied = NULL, 
				or_cancelled = NULL,
				or_shipped = NULL
			where or_tr_id = $tr_id" ) )
	{
		ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
		ss_log_message( "update shopsystem_orders failed" );
		return false;
	}

	if( $order_details )
	{
		if( IsSet( $decodedPaymentData ) && Is_Array( $decodedPaymentData ) )
			$note = mysqli_real_escape_string($link, "Payment ".$gateway['pg_name']." data, ".print_r( $decodedPaymentData, true ));
		else
			$note = mysqli_real_escape_string($link, "Payment ".$gateway['pg_name']." data, ".print_r( $_POST, true )." ".print_r( $_GET, true ));
		$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$note', NOW(), {$order_details['or_id']} )";
		ss_log_message( "SQL:".$sql );
		if( !mysqli_query( $link, $sql ) )
		{
			ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
			return false;
		}

		$basket = unserialize($order_details['or_details']);

		$ExternalProducts = array();
		foreach($basket['OrderProducts'] as $aProduct)
		{
			if (array_key_exists( 'pr_combo', $aProduct['Product'] ) && $aProduct['Product']['pr_combo'])
			{
				ss_log_message( "Combo product splitup" );
				$sqlcombo = "SELECT * FROM shopsystem_combo_products, shopsystem_products, shopsystem_product_extended_options
							WHERE cpr_element_pr_id = {$aProduct['Product']['pr_id']}
							AND cpr_pr_id = pr_id
							AND pro_pr_id = pr_id";

				ss_log_message( "SQL:".$sqlcombo );
				if( $qcombo = mysqli_query( $link, $sqlcombo ) )
				{
					while( $comborow = mysqli_fetch_assoc( $qcombo ) )
					{
						$name = escape($comborow['pr_name']);
						$qty = $aProduct['Qty'] * $comborow['cpr_qty'];
						$price = $qty * $comborow['pro_price'];

						ss_log_message( "Ordering pr_id:".$comborow['pr_id'] );

						$sql = "INSERT INTO shopsystem_order_products 
									(orpr_or_id, orpr_pr_id, orpr_pr_name, orpr_price,
									orpr_qty, orpr_timestamp, orpr_site_folder) 
								VALUES
									({$order_details['or_id']}, {$comborow['pr_id']}, '$name', '$price',
									$qty, Now(), '{$order_details['or_site_folder']}')";

						ss_log_message( "SQL:".$sql );
						if( !mysqli_query( $link,$sql) )
						{
							ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
							return false;
						}

						ss_log_message( "reserving stock of pr_id:".$comborow['pr_id'] );

						$sql = "UPDATE shopsystem_product_extended_options
									SET pro_stock_available = pro_stock_available-$qty
								WHERE pro_pr_id = {$comborow['pr_id']}
									and pro_stock_available IS NOT NULL
									and pro_stock_available > 0";

						ss_log_message( "SQL:".$sql );
						if( !mysqli_query( $link,$sql) )
						{
							ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
							return false;
						}

						$sql = "insert into audit (au_userid, au_operation, au_table, au_key, au_notes) values ({$order_details['or_us_id']}, 'update', 'Products', {$comborow['pr_id']}, 'order $tr_id paid, reserving $qty')";

						ss_log_message( "SQL:".$sql );
						if( !mysqli_query( $link,$sql) )
							ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );

						if( $comborow['pr_is_service'] != 'true' )
						{
							for( $i = 0; $i < $qty; $i++ )
							{
								ss_log_message( "Accumulating another Box of pr_id:".$comborow['pr_id']." Code:'".escape($comborow['pro_stock_code'])."'" );
								if( array_key_exists(escape($comborow['pro_stock_code']), $ExternalProducts ) )
									$ExternalProducts[escape($comborow['pro_stock_code'])]['Num']++;
								else
									$ExternalProducts[escape($comborow['pro_stock_code'])] = array( 'Name' => $name, 'External' => escape($comborow['pr_ve_id']), 'Num' => 1 );
							}
						}
					}
				}
			}
			else
			{
				$name = escape("{$aProduct['Product']['pr_name']} ({$aProduct['Product']['Options']})");
				$price = $aProduct['Qty'] * $aProduct['Product']['Price'];

				ss_log_message( "Ordering pr_id:".$aProduct['Product']['pr_id'] );

				$sql = "INSERT INTO shopsystem_order_products 
							(orpr_or_id, orpr_pr_id, orpr_pr_name, orpr_price,
							orpr_qty, orpr_timestamp, orpr_site_folder) 
						VALUES
							({$order_details['or_id']}, {$aProduct['Product']['pr_id']}, '$name', '$price',
							{$aProduct['Qty']}, Now(), '{$order_details['or_site_folder']}')";

				ss_log_message( "SQL:".$sql );
				if( !mysqli_query( $link,$sql) )
				{
					ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
					return false;
				}

				$subStock = true;
				if( $order_details['or_country'] > 0 )
				{
					$sql = "select cn_bypass_stock_control from countries where cn_id = ".$order_details['or_country'];
					ss_log_message( "SQL:".$sql );
					if( $q = mysqli_query( $link, $sql ) )
					{
						$bypass = mysqli_fetch_assoc( $q );
						if( array_key_exists( 'cn_bypass_stock_control', $bypass ) && ( $bypass['cn_bypass_stock_control'] == 'true' ) )
							$subStock = false;
					}
				}

				if( $subStock )
				{
					ss_log_message( "reserving stock of pr_id:".$aProduct['Product']['pr_id'] );

					$sql = "UPDATE shopsystem_product_extended_options
								SET pro_stock_available = pro_stock_available-{$aProduct['Qty']}
							WHERE pro_pr_id = {$aProduct['Product']['pr_id']}
								and pro_stock_available IS NOT NULL
								and pro_stock_available > 0";


					ss_log_message( "SQL:".$sql );
					if( !mysqli_query( $link,$sql) )
					{
						ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
						return false;
					}

					$sql = "insert into audit (au_userid, au_operation, au_table, au_key, au_notes) values ({$order_details['or_us_id']}, 'update', 'Products', {$aProduct['Product']['pr_id']}, 'order $tr_id paid, reserving {$aProduct['Qty']}')";

					ss_log_message( "SQL:".$sql );
					if( !mysqli_query( $link,$sql) )
						ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
				}
				else
					ss_log_message( "Order to Country:{$order_details['or_country']} bypasses stock control" );

				if( !array_key_exists( 'pr_is_service', $aProduct['Product'] )
				 || ( $aProduct['Product']['pr_is_service'] != 'true' ) )
				{
					for( $i = 0; $i < $aProduct['Qty']; $i++ )
					{
						ss_log_message( "Accumulating another Box of pr_id:".$aProduct['Product']['pr_id']." Code:'".escape($aProduct['Product']['pro_stock_code'])."'" );
						ss_log_message( "Accumulating another Box of '".escape($aProduct['Product']['pro_stock_code'])."'" );
						if( array_key_exists($aProduct['Product']['pro_stock_code'], $ExternalProducts ) )
							$ExternalProducts[$aProduct['Product']['pro_stock_code']]['Num']++;
						else
							$ExternalProducts[$aProduct['Product']['pro_stock_code']] = array( 'Name' => $name, 'External' => escape($aProduct['Product']['pr_ve_id']), 'Num' => 1 );
					}
				}
			}
		}

		ss_log_message( "ExternalProducts" );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ExternalProducts );

		$sql = "DELETE FROM shopsystem_order_items where oi_or_id = {$order_details['or_id']}";

		ss_log_message( "SQL:".$sql );
		if( !mysqli_query( $link,$sql) )
		{
			ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
			return false;
		}

		foreach( $ExternalProducts as $stockcode => $details )
		{
			ss_log_message( "Adding {$details['Num']} Boxes of '$stockcode'" );
			for( $i = 0; $i < $details['Num']; $i++ )
			{
				$sql = "INSERT INTO shopsystem_order_items
								(oi_stock_code, oi_name, oi_or_id, oi_box_number, oi_ve_id)
							VALUES
								('$stockcode',
								 '{$details['Name']}', {$order_details['or_id']}, $i,
								 {$details['External']})";

				ss_log_message( "SQL:".$sql );
				if( !mysqli_query( $link,$sql) )
				{
					ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
					return false;
				}
			}
		}

		$sql = "update payment_gateways set pg_accumulation = pg_accumulation + {$order_details['tr_total']} where pg_id = $bank";
		ss_log_message( "SQL:".$sql );
		if( !mysqli_query( $link, $sql ) )
		{
			ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
			return false;
		}
		return true;
	}
}

