<?php

	// 784 is USD....
	$Q_Orders = query("
		SELECT * FROM shopsystem_orders, transactions, OldExchangeRates
		WHERE or_archive_year IS NULL
			AND or_charge_list = 1
			AND or_tr_id = tr_id
			AND tr_payment_details_szln IS NOT NULL
			AND tr_currency_link = 784
			AND OERID = tr_exchange_rate_index
		ORDER BY tr_id
	");

	$Q_CreditCardTypes = query("
		SELECT * FROM credit_card_types
	");

	$ccTypes = array();
	while ($row = $Q_CreditCardTypes->fetchRow()) {
		$ccTypes[$row['cct_id']] = $row['cct_name'];
	}
	

	while( $row = $Q_Orders->fetchRow() )
	{

		$cc = "";
		$ct = "";

		$pay_details = unserialize($row['tr_payment_details_szln']);
		if( array_key_exists('TrCreditCardNumber', $pay_details) )
			$cc = preg_replace( '/[^0-9]/', '', $pay_details['TrCreditCardNumber']);

		if( array_key_exists('TrCreditCardType', $pay_details) )
			if( array_key_exists($pay_details['TrCreditCardType'], $ccTypes ) )
				$ct = $ccTypes[$pay_details['TrCreditCardType']];

		$exp_month = $pay_details['TrCreditCardExpiry'][0].$pay_details['TrCreditCardExpiry'][1];
		$exp_year = substr( $pay_details['TrCreditCardExpiry'], 3 );
		$cvv = $pay_details['TrCreditCardCVV2'];

		$euro = 0;
		if( array_key_exists('tr_charge_total', $row ) )
		{
			$currency = substr( $row['tr_charge_total'], strlen( $row['tr_charge_total'] ) - 3 );

			if( $currency != 'USD' )
			{

				$usd = 0;
				$euro =  $row['tr_total'];
				if( array_key_exists('OERValues', $row )
				 && strlen( $row['OERValues'] ) )
				{
					$rates = unserialize( $row['OERValues'] );
					if( array_key_exists( 'EUR_USD', $rates ) )
						$usd = number_format( $euro * $rates['EUR_USD'], 2 );
				}
				else
				{
					$usd = number_format( $euro * ss_getExchangeRate( "EUR", "USD" ), 2 );
				}
			}
			else
				$usd = number_format( $row['tr_total'], 2 );

			$details = unserialize($row['or_shipping_details']);

			if( array_key_exists('PurchaserDetails', $details ) )
			{
				$purch = $details['PurchaserDetails'];

				$country = '';
				if( $row['or_country'] > 0 )
					$country = GetField( "select cn_two_code from countries where cn_id = ".$row['or_country'] );

				$state_country = $purch['0_50A4'];
				$pos = strpos( $state_country, "<BR>" );
				if( $pos )
				{
					$state = substr( $state_country, 0, $pos );
					if( !strlen( $country ) )
					{
						$countryName = substr( $state_country, $pos + 4 );
						if( strlen( $countryName ) )
							$country = GetField( "select cn_two_code from countries where cn_name = '$countryName'" );
					}
				}
				else
				{
					$state = $state_country;
/*					$country = $state_country;	*/
				}

				$addr1 = urlencode(strip_tags($purch['0_50A1']));
				$addr2 = urlencode(strip_tags($purch['0_B4BF']));
				$city = urlencode(strip_tags($purch['0_50A2']));
				$zip = urlencode(strip_tags($purch['0_B4C0']));
				$phone = urlencode(strip_tags(preg_replace( '/ /', '-', $purch['0_B4C1'])));
			}
			else
			{
				echo "No Purchaser details<br/>";
			}

			// post fields...

			if( IsSet( $state ) && $usd > 0 && IsSet( $country ) && strlen( $country ) )
			{
				$postinfo = "AcctID=73356&Password=63bvdbGDF2ddWQ&TrackID={$row['or_id']}&TrxType=SALE&NameOnCC=".urlencode($row['tr_client_name'])
								."&CardNo=$cc&EXPMonth=$exp_month&EXPYear=$exp_year&CVV=$cvv&Amount=$usd&IPAddress={$row['tr_ip_address']}"
								."&FName={$row['or_purchaser_firstname']}&LName={$row['or_purchaser_lastname']}"
								."&Address1=$addr1&Address2=$addr2&City=$city&State=$state&Postal=$zip&Country=$country&Email={$row['or_purchaser_email']}&Phone=$phone&sub=sub";


				// create a new cURL resource
				$ch = curl_init();
	//			curl_setopt($ch, CURLOPT_URL, "https://www.e-globalpayment1.net/cgi-bin/ccprocess.exe");
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postinfo);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0");
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				$content = curl_exec( $ch );
				$res = curl_getinfo($ch);
				curl_close($ch);

				//ss_log_message( $postinfo );		// TODO delete this, security....
/*
				print_r( $postinfo );
				echo "<br/>";
				echo "<br/>";
				echo "<br/>";
				print_r( $content );
				die;
*/
				// res
				// Array ( [url] => https://www.e-globalpayment1.net/cgi-bin/ccprocess.exe [content_type] => text/html [http_code] => 200 [header_size] => 150 [request_size] => 508 [filetime] => -1 [ssl_verify_result] => 0 [redirect_count] => 0 [total_time] => 6.169731 [namelookup_time] => 1.042711 [connect_time] => 1.407368 [pretransfer_time] => 5.33319 [size_upload] => 328 [size_download] => 132 [speed_download] => 21 [speed_upload] => 53 [download_content_length] => 0 [upload_content_length] => 0 [starttransfer_time] => 5.914523 [redirect_time] => 0 ) 

				// content
				// ACCTID=73356&TRANSID=20110823020058221059&TRACKID=209552&RESULT=NOK&ERRORNUMBER=110&AUTHCODE=0&REFCODE=0&AVSCODE=&REDIRECTPAGE= 
				ss_log_message( "PP returned: $content" );
				$ChargedOK = false;
				$ack = '';
				$Errnumber = 0;
				$Authcode = '';
				$vals = explode( '&', $content );
				foreach( $vals as $val )
				{
					if( !strncmp( $val, "RESULT=", 7 ) )
					{
						$ack = substr( $val, 7 );
						if( !strncmp( $ack, "ACK", 3 ) )
							$ChargedOK = true;
					}

					if( !strncmp( $val, "ERRORNUMBER=", 12 ) )
						$Errnumber = substr( $val, 12 );

					if( !strncmp( $val, "AUTHCODE=", 9 ) )
						$Authcode = substr( $val, 9 );
				}

				$Q_Notes = query("INSERT INTO shopsystem_order_notes
								(orn_text, orn_timestamp, orn_or_id)
							VALUES ('PP returned Ack:$ack Err:$Errnumber Auth:$Authcode', NOW(), ".$row['or_id'].") ");

				if( $ChargedOK )
				{
					echo "Card Charged OK....<br/>";
					ss_audit( 'update', 'Orders', $row['or_id'], 'Authorisation => '.$Authcode );
					query( "Update shopsystem_orders set or_authorisation_number = '$Authcode', or_charge_list = NULL where or_id = {$row['or_id']}" );
					$r = new Request( "ShopSystem.MarkPaidNotShipped", array( 'or_id' => $row['or_id'], 'SendEmail' => true ) );
				}
				else
				{
					if( $Errnumber <= 101 )
					{
						echo "Unable to process at this time, error number $Errnumber";
						die;
					}
					else
					{
						echo "Card Failed. $Errnumber...<br/>";
						if( $Errnumber >= 302 )
						{
							// card denied?
							$r = new Request( "ShopSystem.MarkProperty", array( 'Property' => 'CardDenied', 'or_id' => $row['or_id'] ) );

							ss_audit( 'update', 'Orders', $row['or_id'], 'Off Chargelist' );
							query( "Update shopsystem_orders set or_charge_list = NULL where or_id = {$row['or_id']}" );
						}
					}
				}
			}
			else
			{
				echo "No state or USD == 0 (euro = ".$euro;
			}
		}
		else
		{
			echo "No tr_charge_total<br/>";
			print_r( $row );
		}

	}

?>
