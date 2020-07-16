<?php
	$results = array();
	$Title = "Accounting Accumulation";

	$slink = mysqli_connect('127.0.0.1','_Shared','bfgv98kjm6')
			or die ("Could not connect");
	mysqli_select_db( $slink, 'common' )
        or die ( "Error selecting database" );
	$exchangeRateR = mysqli_query( $slink, "select * from ExchangeRates where Source = 'USD' and Dest = 'EUR'" );
	$rr = mysqli_fetch_assoc( $exchangeRateR );
	$USD2EUR = $rr['Rate'];
	mysqli_close($slink);

    require_once('session.php');

	echo "ExchangeRate is $USD2EUR<br/>\n";
	$allyears = false;
	if( array_key_exists( 'allyears', $_GET ) )
		$allyears = true;
		
	//ini_set("user_agent", "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; GTB6; .NET CLR 1.1.4322; .NET CLR 2.0.50727)" );
	ini_set("user_agent", "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)" );
//	@apache_setenv('no-gzip', 1);
//	@ini_set('zlib.output_compression', 0);
//	@ini_set('implicit_flush', 1);
//	for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
//	ob_implicit_flush(1);

	function me_query( $sql )
		{
		global $link;
		echo $sql."<br/>\n";
		return mysqli_query( $link, $sql );
		}

	function output_hours( $seconds )
		{
		$remain = $seconds;
		$h = (int) ($remain / 60 / 60 );
		$remain -= $h * 60 * 60;
		$m = (int) ($remain / 60);
		$remain -= $m * 60;
		$s = (int) $remain;

		return sprintf( "%02d:%02d", $h, $m );
		}

	$first_year = 2004;
	$this_year = date( 'Y' );
	$this_month = date( 'm' );
	$this_day = date( 'd' );
	$now = time(NULL);
	echo "Date/Time is ".strftime( "%d / %m / %Y %H:%M", $now )."<br/>\n";

	// housekeeping

	set_time_limit(3600);
	$seg = sem_get( 7896345, 1, 0666, 1) ;
	sem_acquire($seg); 

//	mysql_query( "update shopsystem_orders set or_country = convert( substr( or_shipping_values, locate( 'ShDe0_50A4', or_shipping_values ) + 18, 3), UNSIGNED) where or_archive_year IS NULL and or_country IS NULL" );
	me_query( "update shopsystem_orders set or_archive_year = extract( year from or_recorded ) where or_archive_year IS NULL and or_recorded < CURDATE() - INTERVAL 2 MONTH" );

	$unprocessed_sales = 0;
	$unprocessed_cm = 0;
	$unprocessed_profit = 0;
	$unprocessed_num = 0;
	$earliest_unprocessed = time(NULL);
	$earliest_tx_number = -1;
	$earliest_order_number = -1;
	$last_unprocessed = 0;

	$processed_sales = 0;
	$processed_num = 0;
	$processed_orders = 0;
	$processed_shipping = 0;
	$processed_supplier_cost = 0;
	$processed_other_variable_cost = 0;
	$processed_reship_value = 0;
	$processed_reship_boxes = 0;
	$processed_refund_value = 0;
	$processed_cm = 0;
	$processed_profit = 0;
	$last_usd_rate = 1;
	$pcountry = -1;
	$pcc = "IS NULL";
	$pci = "NULL";
	$psite = "";
	$pgateway = -1;
	$pyear = -1;
	$pmonth = -1;
	$pday = -1;
	$porid = 0;
	$pcurrency = '';

	$order_elements = array();

	// grab all the entries where as_dirty = true and make all corresponding or_summarised = false, then delete the dirty records and carry on.
	// TODO debug this.....
	echo "Looking for dirty records<br />\n";
	if( $refund_result = me_query( "select * from account_summary where as_dirty = true" ) )
		while( $dirty_row = mysql_fetch_assoc( $refund_result ) )
			me_query( "update shopsystem_orders, transactions set or_summarised = false where or_tr_id = tr_id
							and {$dirty_row['as_country']} = or_country
							and '{$dirty_row['as_site']}' = or_site_folder
							and {$dirty_row['as_gateway']} = tr_bank
							and '{$dirty_row['as_currency']}' = tr_currency_code
							and {$dirty_row['as_year']} = YEAR( or_recorded )
							and {$dirty_row['as_month']} = MONTH( or_recorded )
							and {$dirty_row['as_day']} = DAY( or_recorded )" );
	me_query( "delete from account_summary where as_dirty = true" );


//	mysql_query( "begin" );

	if( $refund_result = me_query( "select *, YEAR( rfd_timestamp ) as OrYear, MONTH( rfd_timestamp) as OrMonth, DAY( rfd_timestamp) as OrDay, UNIX_TIMESTAMP(rfd_timestamp) as OrTimestamp from shopsystem_orders join transactions on tr_id = or_tr_id join shopsystem_refunds on or_id = rfd_or_id where rfd_summarized = false order by tr_currency_code, or_country, or_site_folder, rfd_timestamp" ) )
		{
		while( $rrw = mysql_fetch_assoc($refund_result) )
			{
			extract( $rrw );
			if( ($psite != $or_site_folder )		// accumulated records yet?
			 || ($pgateway != $tr_bank )
			 || ($pcurrency != $tr_currency_code )
			 || ($pcountry !== $or_country )
			 || ($pyear != $OrYear )
			 || ($pmonth != $OrMonth )
			 || ($pday != $OrDay ) )
				{
				// update

				if( $pyear > 0 )
					{
					echo "Current Row:increment day $pyear-$pmonth-$pday currency:$pcurrency country:$pcountry or_id:$porid by refund $processed_refund_value <br/>\nFrom Orders ... ";
					print_r( $order_elements );
					$order_elements = array();
					echo " <br/>\n";
					$q = "update account_summary set 
							as_refund_value = as_refund_value + $processed_refund_value
						where
							as_country $pcc
							and as_currency = '$pcurrency'
							and as_site = '$psite'
							and as_gateway = $pgateway
							and as_year = $pyear
							and as_month = $pmonth
							and as_day = $pday";

					if( !me_query( $q ) )
						{
						echo mysql_error()."<br/>\n";
						}

					if( mysql_affected_rows() == 0 )
						{
						//echo $q."\n";
						// insert
						echo "update failed, inserting <br/>\n";

						if( $s = me_query( "select min( op_usd_rate ) as usd_rate from ordered_products where op_or_id = $porid" ) )
							if( $r = mysql_fetch_assoc( $s ) )
								$last_usd_rate = $r['usd_rate'];

						if( !me_query( "insert into account_summary (
										as_country,
										as_site,
										as_gateway,
										as_year,
										as_month,
										as_day,
										as_refund_value,
										as_currency,
										as_usd_rate)
									values (
										$pci,
										'$psite',
										$pgateway,
										$pyear,
										$pmonth,
										$pday,
										$processed_refund_value,
										'$pcurrency',
										$last_usd_rate)" ) )
							{
							echo mysql_error()."<br/>\n";
							}
						}
					}

				$processed_refund_value = 0;
				$psite = $or_site_folder;
				$pgateway = $tr_bank;
				$pcountry = $or_country;
				$pcc = "IS NULL";
				$pci = "NULL";
				if( $pcountry )
				{
					$pcc = "= $pcountry";
					$pci = $pcountry;
				}
				$pyear = $OrYear;
				$pmonth = $OrMonth;
				$pday = $OrDay;
				$porid = $or_id;
				$pcurrency = $tr_currency_code;
				}

			if( $rfd_amount > 0 )
				{
//				if( $TrSourceCurrency == 784 )
//					$rfd_amount *= $USD2EUR;

				$processed_refund_value += $rfd_amount;
				echo "Refund on order $or_tr_id of $rfd_amount<br/>\n";
				$order_elements[] = $or_tr_id;
				}

			if( !me_query( "update shopsystem_refunds set rfd_summarized = true where rfd_or_id = $rfd_or_id and rfd_timestamp = '$rfd_timestamp'" ) )
				{
				echo mysql_error()."<br/>\n";
				}
			}
		}

	if( $pyear > 0 )
		{
		echo "Last Row:increment day $pyear-$pmonth-$pday currency:$pcurrency country:$pcountry or_id:$porid by refund $processed_refund_value <br/>\nFrom Orders ... ";
		print_r( $order_elements );
		$order_elements = array();
		echo " <br/>\n";

		$q = "update account_summary set 
				as_refund_value = as_refund_value + $processed_refund_value
			where
				as_country $pcc
				and as_site = '$psite'
				and as_gateway = $pgateway
				and as_currency = '$pcurrency'
				and as_year = $pyear
				and as_month = $pmonth
				and as_day = $pday";

		if( !me_query( $q ) )
			{
			echo mysql_error()."<br/>\n";
			}

		if( mysql_affected_rows() == 0 )
			{
			echo "Update failed, inserting <br/>\n";

			if( $s = me_query( "select min( op_usd_rate ) as usd_rate from ordered_products where op_or_id = $porid" ) )
				if( $r = mysql_fetch_assoc( $s ) )
					$last_usd_rate = $r['usd_rate'];

			if( !me_query( "insert into account_summary (
							as_country,
							as_site,
							as_gateway,
							as_year,
							as_month,
							as_day,
							as_refund_value,
							as_currency,
							as_usd_rate )
						values (
							$pci,
							'$psite',
							$pgateway,
							$pyear,
							$pmonth,
							$pday,
							$processed_refund_value,
							'$pcurrency',
							$last_usd_rate )" ) )
				{
				echo mysql_error()."<br/>\n";
				}
			}

		// reset totals


		}

	echo "Unsummarized orders for this year<br/>\n";
	$order_elements = array();

	if( true )
		$sql = "select *, YEAR( or_recorded ) as OrYear, MONTH( or_recorded) as OrMonth, DAY( or_recorded) as OrDay, UNIX_TIMESTAMP(or_recorded) as OrTimestamp from shopsystem_orders join transactions on tr_id = or_tr_id where or_summarised = false AND tr_completed = 1 AND tr_status_link < 3 order by tr_currency_code, or_country, or_site_folder, or_recorded";
	else
		$sql = "select *, YEAR( or_recorded ) as OrYear, MONTH( or_recorded) as OrMonth, DAY( or_recorded) as OrDay, UNIX_TIMESTAMP(or_recorded) as OrTimestamp from shopsystem_orders join transactions on tr_id = or_tr_id where (or_archive_year IS NULL or or_archive_year = YEAR(NOW())) and or_summarised = false AND tr_completed = 1 AND tr_status_link < 3 order by tr_currency_code, or_country, or_site_folder, or_recorded";

	if( $order_result = me_query( $sql ) )
		{
		echo "Fetching rows\n" ;
		$i = 0;
		while( $orw = mysql_fetch_assoc($order_result) )
			{
			echo "looking at row ".(++$i)."\n";

			// tr_charge_total IS NOT NULL
			// AND tr_completed = 1
			// AND tr_status_link < 3
			// and (or_paid IS NOT NULL or or_paid_not_shipped IS NOT NULL )
			// and or_reshipment IS NULL
			// AND or_card_denied IS NULL AND or_cancelled IS NULL

			extract( $orw );

			if( !strlen( $or_track_link ) )			// can we check this address?
			{
				echo "checking address ?\n";

				$sql2 = "select * from address_checker where ac_cn_id = $or_country";
				if( $checker_result = me_query( $sql2 ) )
					if( $checker_row = mysql_fetch_assoc($checker_result) )
					{
						// prepare address vars
						$sdetails = unserialize($or_shipping_details);
						$address = urlencode(rtrim(ltrim($sdetails['ShippingDetails']['0_50A1'])));
						$city = urlencode(rtrim(ltrim($sdetails['ShippingDetails']['0_50A2'])));
						$zip = urlencode(rtrim(ltrim($sdetails['ShippingDetails']['0_B4C0'])));
						$s_state_country = $sdetails['ShippingDetails']['0_50A4'];
						$pos = strpos( $s_state_country, "<BR>" );
						if( $pos !== false )
						{
							$state = urlencode(substr( $s_state_country, 0, $pos ));
							$country = urlencode(substr( $s_state_country, $pos + 4 ));
						}
						else
						{
							$state = urlencode($s_state_country);
							$country = urlencode($s_state_country);
						}

						$subs = array( '$address' => $address,
							'$city' => $city,
							'$zip' => $zip,
							'$state' => $state );

						$url = $checker_row['ac_check_url'];
						$url = strtr($url, $subs);
						echo "grabbing address check results from '{$checker_row['ac_check_url']}' -> '$url'<br />\n";
						$ch = curl_init( );
						$headers = array( "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
							"Accept-Encoding:gzip, deflate",
							"Accept-Language:en-US,en;q=0.5",
							"Connection:keep-alive",
							"DNT:1",
							"Host:tools.usps.com",
							"User-Agent:Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0" );
						curl_setopt($ch, CURLOPT_URL, str_replace( ' ', '%20', $url) );
						curl_setopt($ch, CURLOPT_PROXY, 'http://127.0.0.1');
						curl_setopt($ch, CURLOPT_PROXYPORT, 8123);
						curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
//						curl_setopt($ch, CURLOPT_REFERER, 'https://tools.usps.com/go/ZipLookupAction!input.action?mode=0&refresh=true');
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_AUTOREFERER, true);
						curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
						curl_setopt($ch, CURLOPT_ENCODING, '');

						curl_setopt($ch, CURLOPT_COOKIESESSION, true);
						curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookieMonster');
						curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookieMonster');
						curl_setopt($ch, CURLINFO_HEADER_OUT, true );
						curl_setopt($ch, CURLOPT_VERBOSE, 1);
						curl_setopt($ch, CURLOPT_HEADER, 1);

						$response = curl_exec($ch);

						$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
						$header = substr($response, 0, $header_size);
						$page = substr($response, $header_size);

//						echo "retrieved >>>>>\n".$page."\n<<<<<<<<br/>";
						$info = curl_getinfo( $ch, CURLINFO_HEADER_OUT );
						curl_close( $ch );

						echo "headers out >>>>>>>>>>>>>>>>\n";
						print_r( $info );
						echo "<<<<<<<<<<<<<<\n";

						echo "headers in >>>>>>>>>>>>>>>>\n";
						print_r( $header );
						echo "<<<<<<<<<<<<<<\n";

//						if( preg_match( $checker_row['ac_invalid_pattern'], $page ) )
//							$or_track_link = 'Address unknown at post office';
						if( $pos = strpos( $page, $checker_row['ac_extract_pattern_start'] ) )
						{
							$page = substr( $page, $pos );
							if( $pos = strpos( $page, $checker_row['ac_extract_pattern_end'] ))
								$page = substr( $page, 0, $pos+strlen($checker_row['ac_extract_pattern_end']) );
						}
						$or_track_link = trim(rtrim(strip_tags($page)));

						if( $or_track_link === NULL )
							$or_track_link = 'Failed<br />\n';

						me_query( "update shopsystem_orders set or_track_link = '".mysql_real_escape_string($or_track_link)."' where or_id = $or_id" );
					}
			}

			//if( ( $tr_profit == 0 ) && !strlen( $or_cancelled ) && !strlen( $or_card_denied ) && !strlen( $or_reshipment ) )
			if( ( $tr_profit === NULL ) && !strlen( $or_cancelled ) && !strlen( $or_card_denied ) && !strlen( $or_reshipment ) )
			{
				echo "Missing profit on Order $tr_id, external calc, skipping for now";
				exec( "wget 'http://www.acmerockets.com/index.php?act=ShopSystem.AcmeCalculateOrderProfit&or_id=$or_id' -O- > /dev/null" );
				continue;
			}

/*
			if( $TrSourceCurrency == 784 )
			{
				$tr_total *= $USD2EUR;
				$tr_incl_shipping  *= $USD2EUR;
				$tr_excl_shipping *= $USD2EUR;
				$tr_profit *= $USD2EUR;
				$or_profit *= $USD2EUR;
			}
*/

			if( ($psite != $or_site_folder )		// accumulated records yet?
			 || ($pgateway != $tr_bank )
			 || ($pcurrency != $tr_currency_code )
			 || ($pcountry != $or_country )
			 || ($pyear != $OrYear )
			 || ($pmonth != $OrMonth )
			 || ($pday != $OrDay ) )
				{
				// update
				if( $pyear > 0 && $processed_num > 0 )
					{
					print( "Next Set<br/>\nUpdate set (($pcountry, $psite, $pgateway, $pyear, $pday, $pcurrency) $processed_sales, $processed_cm, $processed_profit)<br/>\nFrom Orders ... " );
					print_r( $order_elements );
					$order_elements = array();

					$q = "update account_summary set 
							as_num_orders = as_num_orders + $processed_orders,
							as_sales = as_sales + $processed_sales,
							as_shipping_value = as_shipping_value + $processed_shipping,
							as_supplier_cost = as_supplier_cost + $processed_supplier_cost,
							as_other_variable_cost = as_other_variable_cost + $processed_other_variable_cost,
							as_reship_value = as_reship_value + $processed_reship_value,
							as_reship_boxes = as_reship_boxes + $processed_reship_boxes,
							as_cm_value = as_cm_value + $processed_cm,
							as_profit = as_profit + $processed_profit
						where
							as_country $pcc
							and as_site = '$psite'
							and as_gateway = $pgateway
							and as_currency = '$pcurrency'
							and as_year = $pyear
							and as_month = $pmonth
							and as_day = $pday";

					if( !me_query( $q ) )
						{
						echo mysql_error()."<br/>\n";
						}

					if( mysql_affected_rows() == 0 )
						{
						//echo $q."\n";
						// insert
						echo "Update failed, new row<br/>\n";

						if( !me_query( "insert into account_summary (
										as_country,
										as_site,
										as_gateway,
										as_year,
										as_month,
										as_day,
										as_num_orders,
										as_sales,
										as_shipping_value,
										as_supplier_cost,
										as_other_variable_cost,
										as_reship_value,
										as_reship_boxes,
										as_cm_value,
										as_profit,
										as_currency,
										as_usd_rate)
									values (
										$pci,
										'$psite',
										$pgateway,
										$pyear,
										$pmonth,
										$pday,
										$processed_orders,
										$processed_sales,
										$processed_shipping,
										$processed_supplier_cost,
										$processed_other_variable_cost,
										$processed_reship_value,
										$processed_reship_boxes,
										$processed_cm,
										$processed_profit,
										'$pcurrency',
										$last_usd_rate)" ) )
							{
							echo mysql_error()."<br/>\n";
							}
						}
					}

				$processed_num = 0;
				$processed_orders = 0;
				$processed_sales = 0;
				$processed_shipping = 0;
				$processed_supplier_cost = 0;
				$processed_other_variable_cost = 0;
				$processed_reship_value = 0;
				$processed_reship_boxes = 0;
				$processed_cm = 0;
				$processed_profit = 0;
				$last_usd_rate = 1;
				$psite = $or_site_folder;
				$pgateway = $tr_bank;
				$pcurrency = $tr_currency_code;
				$pcountry = $or_country;
				$pcc = "IS NULL";
				$pci = "NULL";
				if( $pcountry )
					{
					$pcc = "= $pcountry";
					$pci = $pcountry;
					}
				$pyear = $OrYear;
				$pmonth = $OrMonth;
				$pday = $OrDay;
				}

			if( $or_cancelled || $or_card_denied )
				{
				echo "Summarising $or_tr_id as Cancelled or Denied<br/>\n";
				if( !me_query( "update shopsystem_orders set or_summarised = true where or_id = $or_id" ) )
					{
					echo mysql_error()."<br/>\n";
					}
				}
			else
				if( ( $or_paid || $or_paid_not_shipped || $or_reshipment ) && !$or_standby )		// processed in some way and not on standby
					{
					$processed_num++;
					$processed_shipping += $tr_incl_shipping + $tr_excl_shipping;

					if( $s = me_query( "select sum( op_supplier_price ) as cost, min( op_usd_rate ) as usd_rate from ordered_products where op_or_id = $or_id" ) )
						if( $r = mysql_fetch_assoc( $s ) )
							{
							$processed_supplier_cost += $r['cost'];
							$last_usd_rate = $r['usd_rate'];
							if( !$processed_supplier_cost )
								$processed_supplier_cost = 0;
							if( !$last_usd_rate )
								$last_usd_rate = 1;
//								{
//								if( $or_reshipment )		// this info isn't in the ordered_products tables as this wasn't ordered.  damn.
//									{
//									if( $s = me_query( "select sum( op_supplier_price ) as cost, min( op_usd_rate ) as usd_rate from ordered_products where op_or_id = $or_id" ) )
//										if( $r = mysql_fetch_assoc( $s ) )
//											{
//											$processed_supplier_cost += $r['cost'];
//											$last_usd_rate = $r['usd_rate'];
//											}
//									}
//								else
//									{
//									$last_usd_rate = 1;
//									}
//								}
							}

					$order_elements[] = $or_tr_id;

					if( $or_reshipment )		// NOT a sale, NOT part of the profit column
						{
						echo "Order $tr_id reshipment cost ".-$tr_profit."<br/>\n";
						$processed_reship_value -= $tr_profit;

						if( $s = me_query( "select count(*) as qty from shopsystem_order_items where oi_or_id = $or_id" ) )
							{
							if( $r = mysql_fetch_assoc( $s ) )
								$processed_reship_boxes += $r['qty'];
							mysql_free_result($s);
							}
						else
							{
							echo mysql_error()."<br/>\n";
							}
						}
					else
						{
						$processed_cm += $or_profit;
						$processed_profit += $tr_profit;
						$processed_sales += $tr_total + $tr_used_credit;
						$processed_orders++;

						if( $s = me_query( "select * from payment_gateways where pg_id = $tr_bank" ) )
							if( $r = mysql_fetch_assoc( $s ) )
								$processed_other_variable_cost += ( $tr_order_total * $r['pg_skim'] / 100.0 + $r['pg_skim_fixed'] );
						}

					echo "Summarising $or_tr_id as Paid<br/>\nupdate shopsystem_orders set or_summarised = true where or_id = $or_id<br/>\n";

					if( !me_query( "update shopsystem_orders set or_summarised = true where or_id = $or_id" ) )
						{
						echo mysql_error()."<br/>\n";
						}
					}
				else
					{
					if( $or_shipped )
						{
						// shipped but not paid eh?  lets not revisit this.
						echo "Shipped but not paid eh? $or_tr_id<br/>\nupdate shopsystem_orders set or_summarised = true where or_id = $or_id<br/>\n";

						if( !me_query( "update shopsystem_orders set or_summarised = true where or_id = $or_id" ) )
							echo mysql_error()."<br/>\n";
						}
					else
						if( !$or_standby )
							{
							print( "Ignoring Order $or_tr_id as unprocessed<br/>\n" );

							if( $OrTimestamp  > $last_unprocessed )
								$last_unprocessed = $OrTimestamp;
							if( $OrTimestamp < $earliest_unprocessed )
								{
								$earliest_unprocessed = $OrTimestamp;
								$earliest_tx_number = $tr_id;
								$earliest_order_number = $or_id;
								}

							$unprocessed_sales += $tr_total + $tr_used_credit;
							$unprocessed_cm += $or_profit;
							$unprocessed_profit += $tr_profit;
							$unprocessed_num++;
//							if( !me_query( "update transactions set tr_completed = 0 where tr_id = $tr_id" ) )
//								echo mysql_error()."<br/>\n";
							}
//						else		// this didn't work properly, cancelled live orders.
//							{
//							print( "Cancelling Ignoring Order $or_tr_id on Standby<br/>\n" );
//							if( !me_query( "update shopsystem_orders set or_cancelled = now() where or_id = $or_id" ) )
//								echo mysql_error()."<br/>\n";
//							}
					}
			}

		// save last accumulation
		if( $pyear > 0 && $processed_num > 0 )
			{
			print( "Last Set<br/>\nUpdate set (($pcountry, $psite, $pgateway, $pyear, $pday, $pcurrency) $processed_sales, $processed_cm, $processed_profit)<br/>\nFrom Orders ... " );
			print_r( $order_elements );
			$order_elements = array();

			if( !me_query( "update account_summary set 
					as_num_orders = as_num_orders + $processed_orders,
					as_sales = as_sales + $processed_sales,
					as_shipping_value = as_shipping_value + $processed_shipping,
					as_supplier_cost = as_supplier_cost + $processed_supplier_cost,
					as_other_variable_cost = as_other_variable_cost + $processed_other_variable_cost,
					as_reship_value = as_reship_value + $processed_reship_value,
					as_reship_boxes = as_reship_boxes + $processed_reship_boxes,
					as_cm_value = as_cm_value + $processed_cm,
					as_profit = as_profit + $processed_profit
				where
					as_country $pcc
					and as_site = '$psite'
					and as_gateway = $pgateway
					and as_currency = '$pcurrency'
					and as_year = $pyear
					and as_month = $pmonth
					and as_day = $pday" ) )
				{
				echo mysql_error()."<br/>\n";
				}

			if( mysql_affected_rows() == 0 )
				{
				// insert
				echo "update failed, inserting <br/>\n";
				if( !me_query( "insert into account_summary (
								as_country,
								as_site,
								as_gateway,
								as_year,
								as_month,
								as_day,
								as_num_orders,
								as_sales,
								as_shipping_value,
								as_supplier_cost,
								as_other_variable_cost,
								as_reship_value,
								as_reship_boxes,
								as_cm_value,
								as_profit,
								as_currency,
								as_usd_rate)
							values (
								$pci,
								'$psite',
								$pgateway,
								$pyear,
								$pmonth,
								$pday,
								$processed_orders,
								$processed_sales,
								$processed_shipping,
								$processed_supplier_cost,
								$processed_other_variable_cost,
								$processed_reship_value,
								$processed_reship_boxes,
								$processed_cm,
								$processed_profit,
								'$pcurrency',
								$last_usd_rate)" ) )
					{
					echo mysql_error()."<br/>\n";
					}
				}
			}

		mysql_free_result( $order_result );
//		me_query( "commit" );
		}
	else
		{
		echo mysql_error()."<br/>\n";
		me_query( "rollback" );
		}

	sem_release($seg);

	if( $unprocessed_sales > 0 )
		{
		echo "<h2>Unprocessed Orders</h2><br/>\n";
		echo "<br/>\n";
		echo "Total unprocessed sales &euro;".number_format($unprocessed_sales, 2, '.', ',')
			." ($unprocessed_num orders), potential Profit &euro;".number_format($unprocessed_profit, 2, '.', ',')
			." (%".number_format( $unprocessed_profit * 100 /$unprocessed_sales, 1).")<br/>\n";
		echo "Earliest unprocessed order ".output_hours( $now - $earliest_unprocessed )." hours ago (";
		echo "<a href='/index.php?act=ShopSystem.ViewOrder&or_id=$earliest_order_number&tr_id=$earliest_tx_number&as_id=514&BreadCrumbs=Administration%20:%20Orders%20:' target='_blank'>$earliest_tx_number</a>";
		echo "), last "
			.output_hours( $now - $last_unprocessed )." hours ago (&euro; "
			.number_format( $unprocessed_sales * 60 * 60 / ($now - $earliest_unprocessed))."/hr)<br/>\n";
		}

?>
