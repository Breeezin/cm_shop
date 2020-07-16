<?php 

	$this->param('bl_id');

	$bl_id = (int) $this->ATTRIBUTES['bl_id'];

	ss_log_message( "update blacklist ident $bl_id" );

	if( $bl_row = getRow( "select * from blacklist where bl_id = $bl_id" )  )
	{
		if( !$bl_row['bl_us_id'] )
		{
			ss_log_message( "searching for user {$bl_row['bl_email_address']}" );

			if( $us_id = getField( "select us_id from users where us_email = '{$bl_row['bl_email_address']}'" ) )
			{
				query( "update blacklist set bl_us_id = $us_id where bl_id = $bl_id" );
				$bl_row['bl_us_id'] = $us_id;
				ss_log_message( "updating blank user id to $us_id" );
			}
		}

		if( !$bl_row['bl_last_tr_id'] && $bl_row['bl_us_id'] )
		{
			ss_log_message( "searching for last order from user {$bl_row['bl_us_id']}" );
			
			$max = getField( "select max(or_tr_id) from shopsystem_orders where or_us_id = {$bl_row['bl_us_id']}" );
			if( !$max )
				$max = 0;
			query( "update blacklist set bl_last_tr_id = $max where bl_id = $bl_id" );
			$bl_row['bl_last_tr_id'] = $max;
		}

		if($bl_row['bl_us_id'] > 0)
		{
			$first_bl_id = getField( "select min(bl_id) from blacklist where bl_us_id = {$bl_row['bl_us_id']}" );
			if( $first_bl_id < $bl_id )		// swap
			{
				ss_log_message( "swapping bl_id from $bl_id to $first_bl_id" );

				$bl_row = getRow( "select * from blacklist where bl_id = $first_bl_id" );
				$bl_id = $first_bl_id;
			}

			ss_log_message( "clearing out other junk" );

			query( "delete from blacklist where bl_us_id = {$bl_row['bl_us_id']} and bl_id > $first_bl_id" );

			if( !query( "update users set us_bl_id = $bl_id where us_id = {$bl_row['bl_us_id']}" ) )
			{
				ss_log_message( "ERROR: unable to update User:us_bl_id" );
				return NULL;
			}

//			query( "delete from blacklist_ip_addresses where blip_bl_id = $bl_id" );
//			query( "delete from blacklist_cc_details where blcc_bl_id = $bl_id" );

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $bl_row );

			if( strlen( $bl_row['bl_last_tr_id'] ) )
			{
				$Order = getRow("SELECT or_id, or_us_id, or_shipping_details, us_email FROM shopsystem_orders join users on us_id = or_us_id
									WHERE or_tr_id = {$bl_row['bl_last_tr_id']}");

				if (strlen($Order['or_shipping_details']))
				{
					$details = unserialize($Order['or_shipping_details']);

					$billing_name = escape(trim($details['PurchaserDetails']['Name']));
					$billing_email = escape(trim(strip_tags($details['PurchaserDetails']['Email'])));
					$billing_company = escape(trim($details['PurchaserDetails']['0_B4BF']));
					$billing_address1 = escape(trim($details['PurchaserDetails']['0_50A1']));
					$billing_city = escape(trim($details['PurchaserDetails']['0_50A2']));
					$billing_zip = escape(trim($details['PurchaserDetails']['0_B4C0']));
					$billing_phone = escape(trim($details['PurchaserDetails']['0_B4C1']));
					$billing_country_state = escape(trim($details['PurchaserDetails']['0_50A4']));

					if( $pos = strrpos( $billing_country_state, '>' ) )
					{
						$cname = substr( $billing_country_state, ++$pos );
						$billing_country = getField( "select cn_id from countries where cn_name = '$cname'" );
					}

					if( $pos = strrpos( $billing_country_state, '<' ) )
					{
						$ccode = substr( $billing_country_state, 0, $pos );
						$billing_state = getField( "select StName from country_states where StCode = '$ccode'" );
						if( !strlen( $billing_state ) )
							$billing_state = $ccode;
					}

					ss_log_message( "billing_country_state:$billing_country_state billing_country:$billing_country billing_state:$billing_state" );

					$shipping_name = escape(trim($details['ShippingDetails']['Name']));
					$shipping_email = escape(trim(strip_tags($details['ShippingDetails']['Email'])));
					$shipping_company = escape(trim($details['ShippingDetails']['0_B4BF']));
					$shipping_address1 = escape(trim($details['ShippingDetails']['0_50A1']));
					$shipping_city = escape(trim($details['ShippingDetails']['0_50A2']));
					$shipping_zip = escape(trim($details['ShippingDetails']['0_B4C0']));
					$shipping_phone = escape(trim($details['ShippingDetails']['0_B4C1']));
					$shipping_country_state = escape(trim($details['ShippingDetails']['0_50A4']));

					if( $pos = strrpos( $shipping_country_state, '>' ) )
					{
						$cname = substr( $shipping_country_state, ++$pos );
						$shipping_country = getField( "select cn_id from countries where cn_name = '$cname'" );
					}

					if( $pos = strrpos( $shipping_country_state, '<' ) )
					{
						$ccode = substr( $shipping_country_state, 0, $pos );
						$shipping_state = getField( "select StName from country_states where StCode = '$ccode'" );
						if( !strlen( $shipping_state ) )
							$shipping_state = $ccode;
					}

					if( !query(" Update blacklist set
									bl_billing_name = '$billing_name',
									bl_billing_email_address = '$billing_email',
									bl_billing_company = '$billing_company',
									bl_billing_address1 = '$billing_address1',
									bl_billing_address_city = '$billing_city',
									bl_billing_address_state = '$billing_state',
									bl_billing_address_country = '$billing_country',
									bl_billing_address_phone = '$billing_phone',
									bl_billing_address_zip = '$billing_zip',
									bl_shipping_name = '$shipping_name',
									bl_shipping_email_address = '$shipping_email',
									bl_shipping_company = '$shipping_company',
									bl_shipping_address1 = '$shipping_address1',
									bl_shipping_address_city = '$shipping_city',
									bl_shipping_address_state = '$shipping_state',
									bl_shipping_address_country = '$shipping_country',
									bl_shipping_address_phone = '$shipping_phone',
									bl_shipping_address_zip = '$shipping_zip'
								where bl_id =  $bl_id" ) )
					{
						ss_log_message( "ERROR: unable to update into blacklist" );
						return NULL;
					}
				}
			}


			// grab all the other orders and fill in blacklist_ip_addresses and perhaps blacklist and CC if the addresses are different
			$Q_Orders = query( "select * from shopsystem_orders join transactions on tr_id = or_tr_id where or_us_id = {$bl_row['bl_us_id']}" );
			while( $order_row = $Q_Orders->fetchRow() )
			{
				ss_log_message( "examining order {$order_row['tr_id']}" );
				if( strlen($order_row['or_shipping_details']))
				{
					$ouserEmail = $order_row['or_purchaser_email'];
					$odetails = unserialize($order_row['or_shipping_details']);

					$obilling_name = escape(trim($odetails['PurchaserDetails']['Name']));
					$obilling_email = escape(trim(strip_tags($odetails['PurchaserDetails']['Email'])));
					$obilling_company = escape(trim($odetails['PurchaserDetails']['0_B4BF']));
					$obilling_address1 = escape(trim($odetails['PurchaserDetails']['0_50A1']));
					$obilling_city = escape(trim($odetails['PurchaserDetails']['0_50A2']));
					$obilling_zip = escape(trim($odetails['PurchaserDetails']['0_B4C0']));
					$obilling_phone = escape(trim($odetails['PurchaserDetails']['0_B4C1']));
					$obilling_country_state = escape(trim($odetails['PurchaserDetails']['0_50A4']));
					$obilling_country = (int)$obilling_country_state;
					$obilling_state = '';

					if( $pos = strpos( $obilling_country_state, '>' ) )
					{
						$cname = substr( $obilling_country_state, ++$pos );
						$obilling_country = getField( "select cn_id from countries where cn_name = '$cname'" );
					}

					if( $pos = strpos( $obilling_country_state, '<' ) )
					{
						$ccode = substr( $obilling_country_state, 0, $pos );
						$obilling_state = getField( "select StName from country_states where StCode = '$ccode'" );
						if( !strlen( $obilling_state ) )
							$obilling_state = $ccode;
					}

					$oshipping_name = escape(trim($odetails['ShippingDetails']['Name']));
					$oshipping_email = escape(trim(strip_tags($odetails['ShippingDetails']['Email'])));
					$oshipping_company = escape(trim($odetails['ShippingDetails']['0_B4BF']));
					$oshipping_address1 = escape(trim($odetails['ShippingDetails']['0_50A1']));
					$oshipping_city = escape(trim($odetails['ShippingDetails']['0_50A2']));
					$oshipping_zip = escape(trim($odetails['ShippingDetails']['0_B4C0']));
					$oshipping_phone = escape(trim($odetails['ShippingDetails']['0_B4C1']));
					$oshipping_country_state = escape(trim($odetails['ShippingDetails']['0_50A4']));
					$oshipping_country = (int)$oshipping_country_state;
					$oshipping_state = '';

					if( $pos = strpos( $oshipping_country_state, '>' ) )
					{
						$cname = substr( $oshipping_country_state, ++$pos );
						$oshipping_country = getField( "select cn_id from countries where cn_name = '$cname'" );
					}

					if( $pos = strpos( $oshipping_country_state, '<' ) )
					{
						$ccode = substr( $oshipping_country_state, 0, $pos );
						$oshipping_state = getField( "select StName from country_states where StCode = '$ccode'" );
						if( !strlen( $oshipping_state ) )
							$oshipping_state = $ccode;
					}

					$sql = "select count(*) from blacklist where 
								bl_billing_name = '$obilling_name'
								AND bl_billing_email_address = '$obilling_email'
								AND bl_billing_company = '$obilling_company'
								AND bl_billing_address1 = '$obilling_address1'
								AND bl_billing_address_city = '$obilling_city'
								AND bl_billing_address_state = '$obilling_state'
								AND bl_billing_address_country = '$obilling_country'
								AND bl_billing_address_phone = '$obilling_phone'
								AND bl_billing_address_zip = '$obilling_zip'
								AND bl_shipping_name = '$oshipping_name'
								AND bl_shipping_email_address = '$oshipping_email'
								AND bl_shipping_company = '$oshipping_company'
								AND bl_shipping_address1 = '$oshipping_address1'
								AND bl_shipping_address_city = '$oshipping_city'
								AND bl_shipping_address_state = '$oshipping_state'
								AND bl_shipping_address_country = '$oshipping_country'
								AND bl_shipping_address_phone = '$oshipping_phone'
								AND bl_shipping_address_zip = '$oshipping_zip'
								AND bl_email_address = '$ouserEmail'";

//					ss_log_message( $sql );

					// is this any different to the main record?  yes?  insert another record
					if( getField( $sql ) == 0 )
					{
						if( $order_row['tr_id'] != $bl_row['bl_last_tr_id'] )
						{
							ss_log_message( "different, this tr_id:{$order_row['tr_id']} != bl_last_tr_id:{$bl_row['bl_last_tr_id']}" );
							$key = newPrimaryKeyWithMin($this->tableName,$this->tablePrimaryKey, $this->tablePrimaryMinValue);

							if( !query( "insert into blacklist (bl_id) values ($key)" ) )
							{
								ss_log_message( "ERROR: unable to insert into blacklist" );
								return NULL;
							}
						}
						else
						{
							ss_log_message( "updating primary/first entry" );
							$key = $bl_id;
						}

						ss_log_message( "updating blacklist entry $key" );

						if( !query(" Update blacklist set
								bl_us_id = {$bl_row['bl_us_id']},
								bl_billing_name = '$obilling_name',
								bl_billing_email_address = '$obilling_email',
								bl_billing_company = '$obilling_company',
								bl_billing_address1 = '$obilling_address1',
								bl_billing_address_city = '$obilling_city',
								bl_billing_address_state = '$obilling_state',
								bl_billing_address_country = '$obilling_country',
								bl_billing_address_phone = '$obilling_phone',
								bl_billing_address_zip = '$obilling_zip',
								bl_shipping_name = '$oshipping_name',
								bl_shipping_email_address = '$oshipping_email',
								bl_shipping_company = '$oshipping_company',
								bl_shipping_address1 = '$oshipping_address1',
								bl_shipping_address_city = '$oshipping_city',
								bl_shipping_address_state = '$oshipping_state',
								bl_shipping_address_country = '$oshipping_country',
								bl_shipping_address_phone = '$oshipping_phone',
								bl_shipping_address_zip = '$oshipping_zip',
								bl_email_address = '$ouserEmail',
								bl_reason = '{$bl_row['bl_reason']}',
								bl_last_tr_id = {$bl_row['bl_last_tr_id']},
								bl_notes = '".addslashes($bl_row['bl_notes'])."'
							where bl_id =  $key" ) )
						{
							ss_log_message( "ERROR: unable to update into blacklist" );
							return NULL;
						}
					}
					else
						ss_log_message( "same, already there" );

					$blcc_bl_id = $bl_id;
					$blcc_raw = '';
					$blcc_issuer_num = '';
					$blcc_issuer_name = '';
					$blcc_last4 = '';
					$blcc_expiry_date = '';
					$blcc_tr_id = '';
					$blcc_card_type = '';
					$blcc_auth_response = '';
					$blcc_auth_cv_result = '';
					$blcc_auth_currency = '';
					$blcc_auth_amount = '';
					$blcc_reason_code = '';
					$blcc_auth_avs_code = '';

					$blcc_insert = false;

					// blacklist_cc_details
					// Card:414734****8632 Exp:11/20 Issuer:FIA CARD SERVICES, N.A./US   OR
					// Card:481588****2245 Exp:06/21 Issuer:BANK OF HAWAII/US
					// Card:xxxxxxxxxxxx8013 Exp:05-2019 Message:Request was processed successfully.
					// 0123456789012345678901234567890123456789
					// 0         1         2         3

					if( $blcc_raw = getField( "select orn_text from shopsystem_order_notes where orn_or_id = {$order_row['or_id']} and orn_text like 'Card:%'" ) )
					{
						ss_log_message( "found card spec line in notes" );

						// glob the card number bits [5..]
						if( $blcc_raw[5] == 'x' )		// unknown issuer
						{
							$blcc_last4 = substr($blcc_raw, 17, 4);
							$blcc_issuer_name = '';
							$blcc_issuer_num  = 0;
							$blcc_expiry_date = substr($blcc_raw, 29, 4)."-".substr($blcc_raw, 26, 2)."-01";
							$blcc_insert = true;
						}
						else
						{
							$blcc_issuer_num  = (int)substr( $blcc_raw, 5 );		// unknown length so cheat
							if( $blcc_issuer_num > 0 )
							{
								$blcc_insert = true;
								$blcc_last4 = substr($blcc_raw, 15, 4);
								$blcc_issuer_name = substr( $blcc_raw, 37 );
								$blcc_expiry_date = "20".substr($blcc_raw, 27, 2)."-".substr($blcc_raw, 24, 2)."-01";
							}
						}

					}


					if( $ccnotes = getField( "select orn_text from shopsystem_order_notes where orn_or_id = {$order_row['or_id']} and orn_text like 'Payme%'" ) )
					{
						ss_log_message( "found payment spec line in notes" );

						$cmp = 'Cybersource';
						if( substr( $ccnotes, 8, strlen( $cmp ) ) == $cmp )
						{
							if( $pos = strpos( $ccnotes, ',' ) )
							{
								$pos += 2;
								$oa = ShopSystem_BlackListAdministration::print_r_reverse( substr( $ccnotes, $pos ) );
								ss_log_message( "decoded cybersource payment ..." );
								ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $oa );
								if( is_array( $oa ) )
								{
									if( array_key_exists( 'req_card_type', $oa ) )
										$blcc_card_type = $oa['req_card_type'];
									if( array_key_exists( 'auth_response', $oa ) )
										$blcc_auth_response = $oa['auth_response'];
									if( array_key_exists( 'auth_cv_result', $oa ) )
										$blcc_auth_cv_result = $oa['auth_cv_result'];
									if( array_key_exists( 'req_currency', $oa ) )
										$blcc_auth_currency = $oa['req_currency'];
									if( array_key_exists( 'auth_amount', $oa ) )
										$blcc_auth_amount = $oa['auth_amount'];
									if( array_key_exists( 'auth_avs_code', $oa ) )
										$blcc_auth_avs_code = $oa['auth_avs_code'];
									if( array_key_exists( 'reason_code', $oa ) )
										$blcc_reason_code = $oa['reason_code'];
									if( array_key_exists( 'req_card_number', $oa ) )
										// xxxxxxxxxxxx7821
										$blcc_last4 = substr( $oa['req_card_number'], 12 );
									if( array_key_exists( 'req_card_expiry_date', $oa ) )
										// 09-2021
										$blcc_expiry_date = substr($oa['req_card_expiry_date'], 3)."-".substr($oa['req_card_expiry_date'], 0, 2)."-01";

									$blcc_insert = true;
								}
								else
									ss_log_message( "decode failed" );
							}
						}

						$cmp = 'EPG';
						if( substr( $ccnotes, 8, strlen( $cmp ) ) == $cmp )
						{
							if( $pos = strpos( $ccnotes, ',' ) )
							{
								$pos += 2;
								$oa = ShopSystem_BlackListAdministration::print_r_reverse( substr( $ccnotes, $pos ) );
								if( array_key_exists( 'amp;AcsUrl', $oa ) )
								{
									ss_log_message( "decoded EPG payment ..." );
									ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $oa['amp;AcsUrl'] );
									if( $rawCC = ShopSystem_BlackListAdministration::xml_token_extract( 'cardNumber', $oa['amp;AcsUrl'] ) )
										$blcc_last4 = substr( $rawCC, 10 );
									$blcc_card_type = ShopSystem_BlackListAdministration::xml_token_extract( 'cardType', $oa['amp;AcsUrl'] );
									$blcc_auth_response = 2000;
									$blcc_auth_cv_result = '';
									$blcc_auth_currency = ShopSystem_BlackListAdministration::xml_token_extract( 'currency', $oa['amp;AcsUrl'] );
									$blcc_auth_amount = ShopSystem_BlackListAdministration::xml_token_extract( 'amount', $oa['amp;AcsUrl'] );
									$blcc_reason_code = '';
									$blcc_auth_avs_code = '';
									if( $exp = ShopSystem_BlackListAdministration::xml_token_extract( 'expDate', $oa['amp;AcsUrl'] ) )
										$blcc_expiry_date = '20'.substr($exp, 2, 2)."-".substr($exp, 0, 2)."-01";

									$blcc_insert = true;
								}
							}
						}

/*
					Payment DeutscheBankAmex data, Array ( [Ds_Date] => 22/06/2017 [Ds_Hour] => 16:21 [Ds_SecurePayment] => 1 [Ds_Card_Country] => 826 [Ds_Amount] => 8737 [Ds_Currency] => 978 [Ds_Order] => 01621739zNTK [Ds_MerchantCode] => 092466317 [Ds_Terminal] => 001 [Ds_Response] => 0000 [Ds_MerchantData] => [Ds_TransactionType] => 0 [Ds_ConsumerLanguage] => 2 [Ds_AuthorisationCode] => 025148 [Ds_Card_Brand] => 1 ) 
*/

						$cmp = 'DeutscheBankAmex';
						if( substr( $ccnotes, 8, strlen( $cmp ) ) == $cmp )
						{
							if( $pos = strpos( $ccnotes, ',' ) )
							{
								$pos += 2;
								$oa = ShopSystem_BlackListAdministration::print_r_reverse( substr( $ccnotes, $pos ) );
								ss_log_message( "decoded DeutscheBankAmex payment ..." );
								ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $oa );
								if( is_array( $oa ) )
								{
									if( array_key_exists( 'Ds_Card_Brand', $oa ) )
										$blcc_card_type = $oa['Ds_Card_Brand'];
									if( array_key_exists( 'Ds_Response', $oa ) )
										$blcc_auth_response = $oa['Ds_Response'];
									if( array_key_exists( 'Ds_AuthorisationCode', $oa ) )
										$blcc_auth_cv_result = $oa['Ds_AuthorisationCode'];
									if( array_key_exists( 'Ds_Currency', $oa ) )
										$blcc_auth_currency = $oa['Ds_Currency'];
									if( array_key_exists( 'Ds_Amount', $oa ) )
										$blcc_auth_amount = $oa['Ds_Amount']/100.0;
									$blcc_reason_code = '';
									$blcc_auth_avs_code = '';
									$blcc_last4 = '';
									$blcc_expiry_date = '';

									$blcc_insert = true;
								}
								else
									ss_log_message( "decode failed" );
							}
						}
					}
/*
					if( $blcc_insert )
					{
						$sql =  "insert into blacklist_cc_details (blcc_bl_id, blcc_raw, blcc_issuer_num, blcc_issuer_name, blcc_last4, blcc_expiry_date, blcc_card_type, blcc_auth_response, blcc_auth_cv_result, blcc_auth_currency, blcc_auth_amount, blcc_reason_code, blcc_auth_avs_code )
								values ($bl_id, '$blcc_raw', '$blcc_issuer_num', '$blcc_issuer_name', '$blcc_last4', '$blcc_expiry_date', '$blcc_card_type', '$blcc_auth_response', '$blcc_auth_cv_result', '$blcc_auth_currency', '$blcc_auth_amount', '$blcc_reason_code', '$blcc_auth_avs_code')";
						ss_log_message( $sql );
						if( !query( $sql ) )
						{
							ss_log_message( "ERROR: unable to insert into blacklist_cc_details" );
							return NULL;
						}
					}
					

					// blacklist ip addresses
					if( strlen( $order_row['tr_ip_address'] ) )
					{
						ss_log_message( "processing IP address {$order_row['tr_ip_address']}" );

						if( getField( "select count(*) from blacklist_ip_addresses where blip_ip_address = '{$order_row['tr_ip_address']}'" ) == 0 )
						{
							// do whois lookup
							exec( "whois {$order_row['tr_ip_address']}", $whois );
							$hostname = gethostbyaddr( $order_row['tr_ip_address'] );
							$ipa = explode( '.', $order_row['tr_ip_address'] );

							$cidr = "";
							$first = 'NULL';
							$last = 'NULL';
							foreach( $whois as $line )
							{
								$new = preg_replace( "/.*({$ipa[0]}+.\d+.\d+.\d+\/\d+)/", "$1", $line );
								if( $new != $line )		// matched
									$cidr = $new;
							}

							if( strlen( $cidr ) )
							{
								$ca = explode( "/", $cidr );
								$first = ip2long( $ca[0] );
								$last = $first + pow( 2, 32-$ca[1] ) - 1;
							}
							else
							{
								foreach( $whois as $line )
								{
									$new = preg_replace( "/.*\s(\d+.\d+.\d+.\d+)\s-\s(\d+.\d+.\d+.\d+)/", "$1-$2", $line );
									if( $new != $line )		// matched
									{
										$cidr = $new;
										$newa = explode( '-', $new );
										$first = ip2long( $newa[0] );
										$last = ip2long( $newa[1] );
									}
								}
							}
							if( !$first || !strlen( $first ) )
								$first = 'NULL';
							if( !$last || !strlen( $last ) )
								$last = 'NULL';
							// decompose fingerprint
							$bi = escape( trim( $order_row['tr_browser_ident'] ) );

							if( !query( "insert into blacklist_ip_addresses (blip_bl_id, blip_tr_id, blip_ip_address, blip_reverse_dns, blip_netblock_start,
											blip_netblock_end, blip_browser_ident, blip_raw_fingerprint, blip_ip_address_country )
										values ($bl_id, {$order_row['or_tr_id']}, '{$order_row['tr_ip_address']}', '$hostname', $first, $last, '$bi', '{$order_row['tr_fingerprint']}', '".ss_getCountry($order_row['tr_ip_address'])."' )" ) )
							{
								ss_log_message( "ERROR: unable to insert into blacklist_ip_addresses" );
								return NULL;
							}

							if( !query( "update blacklist_ip_addresses set blip_window_size = SUBSTRING_INDEX( blip_raw_fingerprint, ':', 1 ),
												 blip_inital_ttl = SUBSTRING_INDEX(SUBSTRING_INDEX( blip_raw_fingerprint, ':', 2 ), ':', -1),
												 blip_dont_fragment = CONVERT(SUBSTRING_INDEX(SUBSTRING_INDEX( blip_raw_fingerprint, ':', 3 ), ':', -1), UNSIGNED),
												 blip_syn_packet_size = CONVERT(SUBSTRING_INDEX(SUBSTRING_INDEX( blip_raw_fingerprint, ':', 4 ), ':', -1), UNSIGNED),
												 blip_option_mss = CONVERT(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX( blip_raw_fingerprint, ':', 5 ), ':', -1), 'M', -1), UNSIGNED),
												 blip_option_selective_ack = LOCATE('S', SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX( blip_raw_fingerprint, ':', 5 ), ':', -1), 'M', -1) ),
												 blip_option_ts = LOCATE('T', SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX( blip_raw_fingerprint, ':', 5 ), ':', -1), 'M', -1) )
											where blip_tr_id = {$order_row['or_tr_id']}" ) )
							{
								ss_log_message( "ERROR: unable to update into blacklist_ip_addresses" );
								return NULL;
							}
						}
					}
					*/
				}
			}
		}
		else
			ss_log_message( "still no user ident in blacklist entry" );
			
	}
	else
		ss_log_message( "No blacklist entry" );

?>
