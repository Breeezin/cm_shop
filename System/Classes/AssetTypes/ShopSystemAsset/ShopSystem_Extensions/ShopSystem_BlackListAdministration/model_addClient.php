<?php 

	if( count( $_POST ) )
	{
		$this->param('us_id');
		$this->param('BackURL');
		$this->param('Reason', '' );
		$this->param('Notes', '' );

		$us_id = (int) $this->ATTRIBUTES['us_id'];
		$reason = $this->ATTRIBUTES['Reason'];

		ss_log_message( "Adding user id $us_id to blacklist as '$reason'");

		if( $user_row = getRow( "select * from users where us_id = $us_id" ) )
		{
			// out of the user record
			$billing_address1 = addslashes($user_row['us_0_50A1']);
			$billing_company = addslashes($user_row['us_0_B4BF']);
			$billing_city = addslashes($user_row['us_0_50A2']);
			$billing_zip = addslashes($user_row['us_0_B4C0']);
			$billing_phone = addslashes($user_row['us_0_B4C1']);
			$billing_country_state = addslashes($user_row['us_0_50A4']);
			$billing_country = (int)$user_row['us_0_50A4'];
			$billing_state = '';
			$billing_name = addslashes( $user_row['us_first_name'].' '.$user_row['us_last_name'] );
			$billing_email = escape(trim($user_row['us_email']));

			if( $pos = strpos( $billing_country_state, 'text&|&' ) )
				$billing_state = substr( $billing_country_state, $pos + 7 );
			else
				if( $pos = strpos( $billing_country_state, 'select&|&' ) )
				{
					$snum = (int)substr( $billing_country_state, $pos + 9 );
					$billing_state = getField( "select StName from country_states where sts_id = $snum" );
				}

			ss_log_message( "billing_country_state:$billing_country_state billing_country:$billing_country billing_state:$billing_state" );

			// default to same
			$shipping_name = $billing_name;
			$shipping_company = $billing_company;
			$shipping_address1 = $billing_address1;
			$shipping_city = $billing_city;
			$shipping_zip = $billing_zip;
			$shipping_phone = $billing_phone;
			$shipping_country = $billing_country;
			$shipping_state = $billing_state;
			$shipping_email = $billing_email;

			$userEmail = escape(trim($user_row['us_email']));
			
			$notes = escape( trim( $this->ATTRIBUTES['Notes'] ) );

			$lastTrID = 0;

			$newPrimaryKey = newPrimaryKeyWithMin($this->tableName,$this->tablePrimaryKey, $this->tablePrimaryMinValue);
			if( !query( "update users set us_bl_id = $newPrimaryKey where us_id = $us_id" ) )
			{
				ss_log_message( "ERROR: unable to update User:us_bl_id" );
				return NULL;
			}

			if( !query( "insert into blacklist (bl_id) values ($newPrimaryKey)" ) )
			{
				ss_log_message( "ERROR: unable to insert into blacklist" );
				return NULL;
			}

			// grab last order and fill in details from that, `cos it's the special one
			if( $lastTrID = (int)getField( "select max( or_tr_id ) from shopsystem_orders where or_us_id = $us_id" ) )
			{
				$Order = getRow("SELECT or_id, or_us_id, or_shipping_details, us_email FROM shopsystem_orders join users on us_id = or_us_id
									WHERE or_tr_id = $lastTrID");

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
						if( !strlen( $billing_state ) )
							$billing_state = $ccode;
					}

					if( $pos = strrpos( $billing_country_state, '<' ) )
					{
						$ccode = substr( $billing_country_state, 0, $pos );
						$billing_state = getField( "select StName from country_states where StCode = '$ccode'" );
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

					// blacklist_cc_details
					// Card:414734****8632 Exp:11/20 Issuer:FIA CARD SERVICES, N.A./US   OR
					// Card:xxxxxxxxxxxx8013 Exp:05-2019 Message:Request was processed successfully.
					// 0123456789012345678901234567890123456789
					// 0         1         2         3

					if( $ccnotes = getField( "select orn_text from shopsystem_order_notes where orn_or_id = {$Order['or_id']} and orn_text like 'Card:%'" ) )
					{
						// glob the card number bits [5..]
						if( $ccnotes[5] == 'x' )		// unknown issuer
						{
							$last4 = substr($ccnotes, 17, 4);
							$issuer_name = '';
							$issuer_num  = 0;
							$expiry = substr($ccnotes, 29, 4)."-".substr($ccnotes, 26, 2)."-01";
						}
						else
						{
							$last4 = substr($ccnotes, 15, 4);
							$issuer_name = substr( $ccnotes, 47 );
							$issuer_num  = (int)substr( $ccnotes, 5 );		// unknown length so cheat
							$expiry = "20".substr($ccnotes, 27, 2)."-".substr($ccnotes, 24, 2)."-01";
						}

						if( (int) $last4 > 0 )
							if( !query( "insert into blacklist_cc_details (blcc_bl_id, blcc_raw, blcc_issuer_num, blcc_issuer_name, blcc_last4, blcc_expiry_date )
									values ($newPrimaryKey, '$ccnotes', '$issuer_num', '$issuer_name', '$last4', '$expiry' )" ) )
							{
								ss_log_message( "ERROR: unable to insert into blacklist_cc_details" );
								return NULL;
							}

					}

					// blacklist ip addresses
					if( $tr = getRow( "select tr_ip_address, tr_browser_ident, tr_fingerprint from transactions where tr_id = $lastTrID" ) )
					{
						if( strlen( $tr['tr_ip_address'] ) )
						{
							// do whois lookup
							exec( "whois {$tr['tr_ip_address']}", $whois );
							$hostname = gethostbyaddr( $tr['tr_ip_address'] );
							$ipa = explode( '.', $tr['tr_ip_address'] );

							$cidr = "";
							$first = ip2long( $tr['tr_ip_address'] );
							$last = ip2long( $tr['tr_ip_address'] );
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

							if( !strlen( $first ) )
								$first = ip2long( $tr['tr_ip_address'] );

							if( !strlen( $last ) )
								$last = ip2long( $tr['tr_ip_address'] );

							// decompose fingerprint
							$bi = escape( trim( $tr['tr_browser_ident'] ) );

							if( !query( "insert into blacklist_ip_addresses (blip_bl_id, blip_tr_id, blip_ip_address, blip_reverse_dns, blip_netblock_start,
											blip_netblock_end, blip_browser_ident, blip_raw_fingerprint, blip_ip_address_country )
										values ($newPrimaryKey, $lastTrID, '{$tr['tr_ip_address']}', '$hostname', $first, $last, '$bi', '{$tr['tr_fingerprint']}', '".ss_getCountry($order_row['tr_ip_address'])."' )" ) )
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
											where blip_tr_id = $lastTrID" ) )
							{
								ss_log_message( "ERROR: unable to update into blacklist_ip_addresses" );
								return NULL;
							}

						}
					}
				}
			}
			else
			{
				// person has never ordered
			}

			if( !query(" Update blacklist set
					bl_us_id = $us_id,
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
					bl_shipping_address_zip = '$shipping_zip',
					bl_email_address = '$userEmail',
					bl_reason = $reason,
					bl_last_tr_id = $lastTrID,
					bl_notes = '$notes'
				where bl_id =  $newPrimaryKey" ) )
			{
				ss_log_message( "ERROR: unable to update into blacklist" );
				return NULL;
			}

			if( $lastTrID )
			{
				// grab all the other orders and fill in blacklist_ip_addresses and perhaps blacklist and CC if the addresses are different
				$Q_Orders = query( "select * from shopsystem_orders join transactions on tr_id = or_tr_id where or_us_id = $us_id" );
				while( $order_row = $Q_Orders->fetchRow() )
				{
					if( ($order_row['or_tr_id'] != $lastTrID ) && strlen($order_row['or_shipping_details']))
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
						}

						$oshipping_name = escape(trim($odetails['ShippingDetails']['Name']));
						$oshipping_email = escape(trim(strip_tags($odetails['ShippingDetails']['Email'])));
						$oshipping_company = escape(trim($odetails['ShippingDetails']['0_B4BF']));
						$oshipping_address1 = escape(trim($odetails['ShippingDetails']['0_50A1']));
						$oshipping_city = escape(trim($odetails['ShippingDetails']['0_50A2']));
						$oshipping_zip = escape(trim($odetails['ShippingDetails']['0_B4C0']));
						$oshipping_phone = escape(trim($odetails['ShippingDetails']['0_B4C1']));
						$oshipping_country_state = escape(trim($odetails['ShippingDetails']['0_50A4']));
						$oshipping_country = (int)$osohipping_country_state;
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
						}

						// is this any different to the main record?  yes?  insert another record
						if( getField( "select count(*) from blacklist where 
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
							AND bl_email_address = '$ouserEmail'" ) == 0 )
						{
							$newerPrimaryKey = newPrimaryKeyWithMin($this->tableName,$this->tablePrimaryKey, $this->tablePrimaryMinValue);

							if( !query( "insert into blacklist (bl_id) values ($newerPrimaryKey)" ) )
							{
								ss_log_message( "ERROR: unable to insert into blacklist" );
								return NULL;
							}

							if( !query(" Update blacklist set
									bl_us_id = $us_id,
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
									bl_reason = $reason,
									bl_last_tr_id = $lastTrID,
									bl_notes = '$notes'
								where bl_id =  $newerPrimaryKey" ) )
							{
								ss_log_message( "ERROR: unable to update into blacklist" );
								return NULL;
							}
						}

						// blacklist_cc_details
						// Card:414734****8632 Exp:11/20 Issuer:FIA CARD SERVICES, N.A./US   OR
						// Card:xxxxxxxxxxxx8013 Exp:05-2019 Message:Request was processed successfully.
						// 0123456789012345678901234567890123456789
						// 0         1         2         3

						if( $ccnotes = getField( "select orn_text from shopsystem_order_notes where orn_or_id = {$order_row['or_id']} and orn_text like 'Card:%'" ) )
						{
							// glob the card number bits [5..]
							if( $ccnotes[5] == 'x' )		// unknown issuer
							{
								$last4 = substr($ccnotes, 17, 4);
								$issuer_name = '';
								$issuer_num  = 0;
								$expiry = substr($ccnotes, 29, 4)."-".substr($ccnotes, 26, 2)."-01";
							}
							else
							{
								$last4 = substr($ccnotes, 15, 4);
								$issuer_name = substr( $ccnotes, 47 );
								$issuer_num  = (int)substr( $ccnotes, 5 );		// unknown length so cheat
								$expiry = "20".substr($ccnotes, 27, 2)."-".substr($ccnotes, 24, 2)."-01";
							}

							if( (int) $last4 > 0 )
								if( !query( "insert into blacklist_cc_details (blcc_bl_id, blcc_raw, blcc_issuer_num, blcc_issuer_name, blcc_last4, blcc_expiry_date )
										values ($newPrimaryKey, '$ccnotes', '$issuer_num', '$issuer_name', '$last4', '$expiry' )" ) )
								{
									ss_log_message( "ERROR: unable to insert into blacklist_cc_details" );
									return NULL;
								}

							// blacklist ip addresses
							if( $tr = getRow( "select tr_ip_address, tr_browser_ident, tr_fingerprint from transactions where tr_id = {$order_row['tr_id']}" ) )
							{
								if( strlen( $tr['tr_ip_address'] ) )
								{
									// do whois lookup
									exec( "whois {$tr['tr_ip_address']}", $whois );
									$hostname = gethostbyaddr( $tr['tr_ip_address'] );
									$ipa = explode( '.', $tr['tr_ip_address'] );

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
									// decompose fingerprint
									$bi = escape( trim( $tr['tr_browser_ident'] ) );

									if( !query( "insert into blacklist_ip_addresses (blip_bl_id, blip_tr_id, blip_ip_address, blip_reverse_dns, blip_netblock_start,
													blip_netblock_end, blip_browser_ident, blip_raw_fingerprint )
												values ($newPrimaryKey, {$order_row['or_tr_id']}, '{$tr['tr_ip_address']}', '$hostname', $first, $last, '$bi', '{$tr['tr_fingerprint']}' )" ) )
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

						}
					}
				}
			}
		}

		locationRelative($this->ATTRIBUTES['BackURL']);
	}
?>
