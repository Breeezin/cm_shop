<?php

	$avs_codes = array(
		'1' => 'USA: Not supported AVS is not supported for this processor or card type.',
		'2' => 'USA: Unrecognized The processor returned an unrecognized value for the AVS response.',
		'3' => 'USA: Match Address is confirmed. Returned only for PayPal Express Checkout.',
		'4' => 'USA: No match Address is not confirmed. Returned only for PayPal Express',
		'A' => 'USA:Partial match Street address matches, but 5-digit and 9-digit postal codes do not match.',
		'B' => 'INTL: Partial match Street address matches, but postal code is not verified.',
		'C' => 'INTL: No match Street address and postal code do not match.',
		'D' => 'INTL: Street address and postal code match.',
		'E' => 'USA: Invalid AVS data is invalid or AVS is not allowed for this card type.',
		'F' => 'USA: Partial match Card member’s name does not match, but billing postal code matches. Returned only for the American Express card type.',
		'G' => 'Non-US. Issuer does not participate',
		'H' => 'USA: Partial match Card member’s name does not match, but street address and postal code match. Returned only for the American Express card type.',
		'I' => 'INTL: No match Address not verified.',
		'I' => 'USA: No match Address not verified.',
		'J' => 'USA: Match Card member’s name, billing address, and postal code match.  Shipping information verified and chargeback protection guaranteed through the Fraud Protection Program. Returned only if you are signed up to use AAV+ with the American Express Phoenix processor.',
		'K' => 'USA: Partial match Card member’s name matches, but billing address and billing postal code do not match. Returned only for the American Express card type.',
		'L' => 'USA: Partial match Card member’s name and billing postal code match, but billing address does not match. Returned only for the American Express card type.',
		'M' => 'INTL: Street address and postal code match.',
		'N' => 'USA: No match One of the following: Street address and postal code do not match.  Card member’s name, street address, and postal code do not match. Returned only for the American Express card type.',
		'O' => 'USA: Partial match Card member’s name and billing address match, but billing postal code does not match. Returned only for the American Express card type.',
		'P' => 'INTL: Partial match Postal code matches, but street address not verified.',
		'Q' => 'USA: Match Card member’s name, billing address, and postal code match.  Shipping information verified but chargeback protection not guaranteed (Standard program). Returned only if you are signed to use AAV+ with the American Express Phoenix processor.',
		'R' => 'USA: System unavailable System unavailable.',
		'S' => 'USA: Not supported U.S.-issuing bank does not support AVS.',
		'T' => 'USA: Partial match Card member’s name does not match, but street address matches. Returned only for the American Express card type.',
		'U' => 'USA: System unavailable Address information unavailable for one of these reasons:  The U.S. bank does not support non-U.S. AVS.   The AVS in a U.S. bank is not functioning properly.',
		'V' => 'USA: Match Card member’s name, billing address, and billing postal code match. Returned only for the American Express card type.',
		'W' => 'USA: Partial match Street address does not match, but 9-digit postal code matches.',
		'X' => 'USA: Match Street address and 9-digit postal code match.',
		'Y' => 'USA: Match Street address and 5-digit postal code match.',
		'Z' => 'USA: Partial match Street address does not match, but 5-digit postal code matches.',
		);

	require( "../bank_interface/functions.php" );
	global $bank, $tr_id, $link;

	define ('HMAC_SHA256', 'sha256');
	define ('PROFILE_ID', 'F0D122E5-45E1-0B04-9761-2E3692756547' );
	define ('ACCESS_KEY', '6e280b23edc53c1b822a17be42bd9261' );
	define ('SECRET_KEY', 'c9be70855d8a4defa695cbef2c272771d8abd882b3484d94992c4a0f409b642f7e82834cdf804be8ae3bff62393d89629d80ee2966ae41d1ba198b3911132a9130d9a76630374a0fb084fad32c8a7651ed51f6cd0b5f445c80b0bfd6823b3eb0b881e0b5e9024011b43fe5e66483bceef979b81548b94b7ebe6126cb03882b16');


	function sign ($params) {
	  return signData(buildDataToSign($params), SECRET_KEY);
	}

	function signData($data, $secretKey) {
		return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
	}

	function buildDataToSign($params) {
			$signedFieldNames = explode(",",$params["signed_field_names"]);
			foreach ($signedFieldNames as &$field) {
			   $dataToSign[] = $field . "=" . $params[$field];
			}
			return commaSeparate($dataToSign);
	}

	function commaSeparate ($dataToSign) {
		return implode(",",$dataToSign);
	}

	$bank = 38;	// pg_id:payment_gateways
/*

	if( strcmp( $_SERVER['HTTP_USER_AGENT'], 'Klik & Pay' ) )
	{
		ss_log_message( "Wrong user agent.  Dying" );
	}
*/

    ss_log_message( "CYBERSOURCE _SERVER" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );
    ss_log_message( "CYBERSOURCE _GET" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_GET );
    ss_log_message( "CYBERSOURCE _POST" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_POST );

/*

    [auth_cv_result] => M
    [req_card_number] => xxxxxxxxxxxx3516
    [req_locale] => en
    [signature] => tw0s4PR5kxm1Kluyj1DrwKA0L+K8Y5Bgs3ctaQdJQKw=
    [auth_trans_ref_no] => 4992164502966514504695
    [req_bill_to_surname] => Hazen
    [req_bill_to_address_city] => Grand Ledge, MI 48837
    [req_card_expiry_date] => 10-2017
    [req_bill_to_address_postal_code] => 48837
    [req_bill_to_phone] => 5172825111
    [reason_code] => 100
    [auth_amount] => 375.00
    [auth_response] => 00
    [bill_trans_ref_no] => 4992164502966514504695
    [req_bill_to_forename] => Blake
    [req_payment_method] => card
    [request_token] => Ahj//wSTDqAzHdOxmFP3cizRy5ZMWzRqwZOWzZqxaNWDRs5aqKIME8ejAVFEGCePR6QQknxwRhk0ky3SA5M6sMKkmHUBmO6djMKfuAAAyRFQ
    [auth_time] => 2017-07-05T010050Z
    [req_amount] => 375.00
    [req_bill_to_email] => blake@hazentrane.com
    [auth_avs_code_raw] => D
    [transaction_id] => 4992164502966514504695
    [req_currency] => USD
    [req_card_type] => 001
    [decision] => ACCEPT
    [message] => Request was processed successfully.
    [signed_field_names] => transaction_id,decision,req_access_key,req_profile_id,req_transaction_uuid,req_transaction_type,req_reference_number,req_amount,req_currency,req_locale,req_payment_method,req_bill_t
o_forename,req_bill_to_surname,req_bill_to_email,req_bill_to_phone,req_bill_to_address_line1,req_bill_to_address_city,req_bill_to_address_state,req_bill_to_address_country,req_bill_to_address_postal_code,req_c
ard_number,req_card_type,req_card_expiry_date,message,reason_code,auth_avs_code,auth_avs_code_raw,auth_response,auth_amount,auth_code,auth_cv_result,auth_cv_result_raw,auth_trans_ref_no,auth_time,request_token
,bill_trans_ref_no,signed_field_names,signed_date_time
    [req_transaction_uuid] => 595c39eb81665
    [auth_avs_code] => D
    [auth_code] => 024000
    [req_bill_to_address_country] => US
    [req_transaction_type] => sale
    [req_access_key] => 79888745bc79306a9ebf505a1f94d256
    [auth_cv_result_raw] => M
    [req_profile_id] => F0D122E5-0B04-45E1-9761-2E3692756547
    [req_reference_number] => 1639168
    [req_bill_to_address_state] => MI
    [signed_date_time] => 2017-07-05T01:00:50Z
    [req_bill_to_address_line1] => PO BOX 69  421 S. Clinton

*/
	$ip = array();
	foreach( $_POST as $key => $val )
		$ip[$key] = rtrim(ltrim( $val ));
		//$ip[$key] = escape(rtrim(ltrim( $val )));

	if( sign($_POST) != $_POST['signature'] )
	{
		ss_log_message( "CYBERSOURCE sig mismatch:". $_POST['signature']."!=".sign($_POST) );
		sleep( 10000 );
		die;
	}

	$card_details = array();

	$card_details['used_tr_id'] = $tr_id = (int)safe( $_POST['req_reference_number'] );
	$authid = safe( $_POST['auth_code'] );
	$successcode = safe( $_POST['decision'] );
	$avs_code = safe( $_POST['auth_avs_code'] );
	$message = addslashes( 'Card:'.$_POST['req_card_number'].' Exp:'.$_POST['req_card_expiry_date'].' Message:'.$_POST['message'] );
	$card_details['used_auth_amount'] = $amt = (float)( $_POST['auth_amount'] );

	if( array_key_exists( 'message', $_POST ) )
		$card_details['used_auth_response'] = addslashes( $_POST['message'] );

	if( array_key_exists( 'req_currency', $_POST ) )
		$card_details['used_auth_currency'] = addslashes( $_POST['req_currency'] );

	if( array_key_exists( 'req_card_number', $_POST ) )
	{
		$card_details['used_raw'] = addslashes( $_POST['req_card_number'] );
		$card_details['used_last4'] = addslashes( substr($_POST['req_card_number'], strlen($_POST['req_card_number'])-4) );
	}

	if( array_key_exists( 'req_card_expiry_date', $_POST ) )
		$card_details['used_expiry_date'] = substr( $_POST['req_card_expiry_date'], 3 ).'/'.substr( $_POST['req_card_expiry_date'], 0, 2 ).'/01';

	if( array_key_exists( 'req_card_type', $_POST ) )
		switch( $_POST['req_card_type'] )
		{
			case '001':
				$card_details['used_card_type'] = 'VISA';
		}
	
	$card_details['used_charged_ok'] = ( ( $successcode == 'ACCEPT' ) && strlen( $authid ) );

	if( array_key_exists( 'auth_cv_result',$_POST ) )
		$card_details['used_auth_cv_result'] = $_POST['auth_cv_result'];

	if( array_key_exists( 'reason_code',$_POST ) )
		$card_details['used_reason_code'] = $_POST['reason_code'];

	if( array_key_exists( 'auth_avs_code',$_POST ) )
		$card_details['used_auth_avs_code'] = $_POST['auth_avs_code'];

	if( array_key_exists( 'req_bill_to_forename',$_POST ) )
		$card_details['used_holder_name'] = $_POST['req_bill_to_forename'];
	else
		$card_details['used_holder_name'] = '';

	if( array_key_exists( 'req_bill_to_surname',$_POST ) )
		$card_details['used_holder_name'] .= ' '.$_POST['req_bill_to_surname'];

	if( array_key_exists( 'req_bill_to_address_country',$_POST ) )
		$card_details['used_issuer_country'] = $_POST['req_bill_to_address_country'];

	if( !$tr_id )
	{
		ss_log_message( "CYBERSOURCE no tr_id" );
		die;
	}

	if( init( $bank, $tr_id ) )
	{
		if( count( $card_details ) )
		{
			$sqla = "insert into used_cc_details (";
			$sqlb = " values (";
			foreach( $card_details as $col => $val )
			{
				$sqla .= $col.', ';
				$sqlb .= "'".addslashes($val)."', ";
			}
			// remove last ,
			$sqla = substr( $sqla, 0, strlen( $sqla )-2 ).')';
			$sqlb = substr( $sqlb, 0, strlen( $sqlb )-2 ).')';

			ss_log_message( $sqla.$sqlb );
			@mysqli_query( $link, $sqla.$sqlb );
		}


//		ipcheck( array( '58.64.198.72', '203.105.16' ), $bank, $tr_id );
		$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$message', NOW(), {$order_details['or_id']} )";
		ss_log_message( "SQL:".$sql );
		mysqli_query( $link, $sql );

		if( strstr( $message, "fraud" ) )
		{
			// cancel order
			$sql =  "update shopsystem_orders set or_cancelled = NOW() where or_tr_id = $tr_id";
			ss_log_message( "SQL:".$sql );
			mysqli_query( $link, $sql );
		}

		// only for newish customers.....

		$sql = 'select count(*) as cnt from transactions join shopsystem_orders on tr_id = or_tr_id where tr_completed >= 1 and or_shipped IS NOT NULL and or_us_id = '.$order_details['or_us_id'];
		ss_log_message( "SQL:".$sql );
		if( $q = mysqli_query( $link, $sql ) )
			$c = mysqli_fetch_assoc( $q );
		else
		{
			ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
			ss_log_message( "Failed" );
			die;
		}

		if( $c['cnt'] <= 4 )
		{
			$hold = false;

			if( array_key_exists( $avs_code, $avs_codes ) )
				if( ($avs_code != 'M')
				 && ($avs_code != 'D')
				 )
					$hold = true;
				else
					$hold = false;
			else
				$hold = true;

			if( $hold )
			{
				$note = $avs_codes[$avs_code];

				$note .= ' Auto hold.';
				mysqli_query( $link, "update shopsystem_orders set or_standby = NOW() where or_tr_id = $tr_id" );

				$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$note', NOW(), {$order_details['or_id']} )";
				ss_log_message( "SQL:".$sql );
				if( !mysqli_query( $link, $sql ) )
				{
					ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
					die;
				}
			}
		}

		if( strstr( $message, "fraud" ) )
		{
			$sdetails = unserialize($order_details['or_shipping_details']);
			$BillFirstName = escape(rtrim(ltrim($sdetails['PurchaserDetails']['first_name'])));
			$BillLastName = escape(rtrim(ltrim($sdetails['PurchaserDetails']['last_name'])));
			$BillCompany = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_B4BF'])));
			$BillAddress = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A1'])));
			$BillCity = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A2'])));
			$BillZip = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_B4C0'])));
			$BillPhone = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_B4C1'])));
			$BillStateCountry = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A4'])));
			$ShipFirstName = escape(rtrim(ltrim($sdetails['ShippingDetails']['first_name'])));
			$ShipLastName = escape(rtrim(ltrim($sdetails['ShippingDetails']['last_name'])));
			$ShipCompany = escape(rtrim(ltrim($sdetails['ShippingDetails']['0_B4BF'])));
			$ShipAddress = escape(rtrim(ltrim($sdetails['ShippingDetails']['0_50A1'])));
			$ShipCity = escape(rtrim(ltrim($sdetails['ShippingDetails']['0_50A2'])));
			$ShipZip = escape(rtrim(ltrim($sdetails['ShippingDetails']['0_B4C0'])));
			$ShipPhone = escape(rtrim(ltrim($sdetails['ShippingDetails']['0_B4C1'])));
			$ShipStateCountry = escape(rtrim(ltrim($sdetails['ShippingDetails']['0_50A4'])));
			$email_address = $sdetails['PurchaserDetails']['Email'];


			if( $pos = strrpos( $BillStateCountry, '>' ) )
			{
				$cname = substr( $BillStateCountry, ++$pos );
				if( $gq = mysqli_query( $link,"select cn_id from countries where cn_name = '$cname'") )
					if( $gr = mysqli_fetch_assoc( $gq ) )
						$BillingCountry = $gr['cn_id'];
					else
						$BillingCountry = 0;
				else
					$BillingCountry = 0;
			}
			else
				$BillingCountry = 0;

			if( $pos = strrpos( $BillStateCountry, '<' ) )
			{
				$ccode = substr( $BillStateCountry, 0, $pos );
				$billing_state = getField( "select StName from country_states where StCode = '$ccode'" );
				if( $gq = mysqli_query( $link,"select StName from country_states where StCode = '$ccode'" ) )
					if( $gr = mysqli_fetch_assoc( $gq ) )
						$BillingState = $gr['StName'];
					else
						$BillingState = '';
				else
					$BillingState = '';
			}
			else
				$BillingState = '';

			if( $pos = strrpos( $ShipStateCountry, '>' ) )
			{
				$cname = substr( $ShipStateCountry, ++$pos );
				if( $gq = mysqli_query( $link,"select cn_id from countries where cn_name = '$cname'") )
					if( $gr = mysqli_fetch_assoc( $gq ) )
						$ShippingCountry = $gr['cn_id'];
					else
						$ShippingCountry = 0;
				else
					$ShippingCountry = 0;
			}
			else
				$ShippingCountry = 0;

			if( $pos = strrpos( $ShipStateCountry, '<' ) )
			{
				$ccode = substr( $ShipStateCountry, 0, $pos );
				$billing_state = getField( "select StName from country_states where StCode = '$ccode'" );
				if( $gq = mysqli_query( $link,"select StName from country_states where StCode = '$ccode'" ) )
					if( $gr = mysqli_fetch_assoc( $gq ) )
						$ShippingState = $gr['StName'];
					else
						$ShippingState = '';
				else
					$ShippingState = '';
			}
			else
				$ShippingState = '';

			// blacklist client
//			$sql = "insert into shopsystem_blacklist (BlLiBillingName, BlLiBillingAddress, BlLiShippingName, BlLiShippingAddress, BlLiEmail, BlLiNotes, BlLiIPAddress, BlLiBrowserIdent, BlLiFingerprint ) values ('$BillFirstName $BillLastName', '$BillAddress', '$ShipFirstName $ShipLastName', '$ShipAddress', '$email_address', 'Attempted fraud detected by Cybersource', '{$order_details['tr_ip_address']}', '{$order_details['tr_browser_ident']}', '{$order_details['tr_fingerprint']}')";
//			ss_log_message( "SQL:".$sql );
			$sql = "insert into blacklist (bl_us_id, "
				."bl_billing_name, bl_billing_company, bl_billing_address1, bl_billing_address_city, bl_billing_address_state, bl_billing_address_country, bl_billing_address_phone, bl_billing_address_zip"
				."bl_shipping_name, bl_shipping_company, bl_shipping_address1, bl_shipping_address_city, bl_shipping_address_state, bl_shipping_address_country, bl_shipping_address_phone, bl_shipping_address_zip"
				."bl_email_address, bl_reason, bl_last_tr_id, bl_notes"
				.") values ("
				.$order_details['or_us_id'].", "
				."'$BillFirstName $BillLastName', '$BillCompany', '$BillAddress', '$BillCity', $BillingState, $BillingCountry, '$BillPhone', '$BillZip'"
				."'$ShipFirstName $ShipLastName', '$ShipCompany', $ShipAddress, '$ShipCity', '$ShippingState', '$ShippingCountry', '$ShipPhone', '$ShipZip'"
				."'$email_address', STOLEN_CREDITCARD, {$order_details['tr_id']}, 'Automatically added by Cybersource' )";
			ss_log_message( "SQL:".$sql );
			mysqli_query( $link, $sql );
			$bl_id = mysqli_insert_id( $link );
			$bi = escape( trim( $order_details['tr_browser_ident'] ) );
			mysqli_query( $link, 'update users set us_bl_id = $bl_id where us_id = '.$order_details['or_us_id'] );
			// should really look this ip up and fill in all the rest of the guff
			$sql =  "insert into blacklist_ip_addresses (blip_bl_id, blip_tr_id, blip_ip_address, blip_browser_ident, blip_raw_fingerprint )
										values ($bl_id, {$order_details['tr_id']}, '{$order_details['tr_ip_address']}', '$bi', '{$order_details['tr_fingerprint']}' )";
			if( !query( $sql ) )
				ss_log_message( "Unable to insert into blacklist_ip_addresses" );

		}

		if( ( $successcode == 'ACCEPT' ) && strlen( $authid ) && ($amt >= $order_details['tr_total'] ) )
			if( mark_paid( ) )
			{
				done_ack( );

				$sql = 'select us_do_not_address_check from users where us_id = '.$order_details['or_us_id'];
				ss_log_message( "SQL:".$sql );
				if( $q = mysqli_query( $link, $sql ) )
					$cc_array = mysqli_fetch_assoc( $q );
				else
				{
					ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
					ss_log_message( "Failed" );
					die;
				}
				if( $cc_array['us_do_not_address_check'] != 'true' )
				{
					ss_log_message( 'checking address details for user ID '.$order_details['or_us_id'] );
					$sdetails = unserialize($order_details['or_shipping_details']);
					$BillFirstName = escape(rtrim(ltrim($sdetails['PurchaserDetails']['first_name'])));
					$BillLastName = escape(rtrim(ltrim($sdetails['PurchaserDetails']['last_name'])));
					$BillAddress = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A1'])));
					$ShipFirstName = escape(rtrim(ltrim($sdetails['ShippingDetails']['first_name'])));
					$ShipLastName = escape(rtrim(ltrim($sdetails['ShippingDetails']['last_name'])));
					$ShipAddress = escape(rtrim(ltrim($sdetails['ShippingDetails']['0_50A1'])));
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

					trim( $b_state );
					while( $b_state[0] == ' ' )
						$b_state = substr( $b_state, 1 );

					$sql = "select cn_two_code from countries where cn_name = '$b_country'";
					ss_log_message( "SQL:".$sql );
					if( $q = mysqli_query( $link, $sql ) )
						$cc_array = mysqli_fetch_assoc( $q );
					else
					{
						ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
						ss_log_message( "Failed" );
						die;
					}
					$cn_two_code = $cc_array['cn_two_code'];

					$Postal = $sdetails['PurchaserDetails']['0_B4C0'];
					$Phone = $sdetails['PurchaserDetails']['0_B4C1'];
					$email_address = $sdetails['PurchaserDetails']['Email'];
					$pos = strpos( $email_address, ">" );
					if( $pos )
						$email_address = substr( $email_address, $pos + 1 );
					$pos = strrpos( $email_address, "<" );
					if( $pos )
						$email_address = substr( $email_address, 0, $pos );

					$same = true;
					$note = '';
					if( $BillFirstName != $ip['req_bill_to_forename'] )
					{
						$same = false;
						$note .= "First Name Different ($BillFirstName != {$ip['req_bill_to_forename']}). ";
					}

					if( $BillLastName != $ip['req_bill_to_surname'] )
					{
						$same = false;
						$note .= "Last Name Different ($BillLastName != {$ip['req_bill_to_surname']}). ";
					}

					if( $BillAddress != $ip['req_bill_to_address_line1'] )
					{
						$same = false;
						$note .= "Billing Address different ($BillAddress != {$ip['req_bill_to_address_line1']}. ";
					}

					if( $Postal != $ip['req_bill_to_address_postal_code'] )
					{
						$same = false;
						$note .= "Post Code Different ($Postal != {$ip['req_bill_to_address_postal_code']}). ";
					}

					if( $email_address != $ip['req_bill_to_email'] )
					{
						$same = false;
						$note .= "Email Name Different ($email_address != {$ip['req_bill_to_email']}). ";
					}
					if( $cn_two_code != $ip['req_bill_to_address_country'] )
					{
						$same = false;
						$note .= "Country Different ($cn_two_code != {$ip['req_bill_to_address_country']}). ";
					}

					if( !$same )
					{
						mysqli_query( $link, "update shopsystem_orders set or_standby = NOW() where or_tr_id = $tr_id" );
						$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$note Auto hold.', NOW(), {$order_details['or_id']} )";
						ss_log_message( "SQL:".$sql );
						if( !mysqli_query( $link, $sql ) )
						{
							ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
							die;
						}
						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $ip );
					}
				}
				else
					ss_log_message( 'NOT checking address details for user ID '.$order_details['or_us_id'] );
			}
			else
				done_nak();
		else
		{
			ss_log_message( "CYBERSOURCE not processing, successcode:$successcode authid:$authid amt:$amt tr_total:{$order_details['tr_total']}" );
			done_nak();
		}
	}
	else
		done_nak();

?>
