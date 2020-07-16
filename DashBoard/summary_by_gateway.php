<?php
	$results = array();
	$Title = "Accounting Summary";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";

	echo "</head>";

	function output_hours( $seconds )
		{
		$remain = $seconds;
		$h = (int) ($remain / 60 / 60 );
		$remain -= $h * 60 * 60;
		$m = (int) ($remain / 60);
		$remain -= $m * 60;
		$s = (int) $remain;

		return sprintf( "%02d:%02d", $h, $d );
		}

	function output_header( )
		{
		global $gateway, $gateways;

		echo "<th>orders</th>";
		echo "<th>{$gateways[$gateway]['pg_name']} sales</th>";
		echo "<th>{$gateways[$gateway]['pg_name']} shipping</th>";
		echo "<th>reship boxes</th>";
		echo "<th>{$gateways[$gateway]['pg_name']} reship value</th>";
		echo "<th>% reship</th>";
		echo "<th>{$gateways[$gateway]['pg_name']} refund value</th>";
		echo "<th>% refund</th>";
	//	echo "<th>&euro; cm</th>";
	//	echo "<th>% cm</th>";
		echo "<th>{$gateways[$gateway]['pg_name']} CM</th>";
		echo "<th>{$gateways[$gateway]['pg_name']} box cost</th>";
		echo "<th>% Margin</th>";
		}

	function output_row( $row, $from_year = 0, $from_month = 0, $from_day = 0, $to_year = 0, $to_month = 0, $to_day = 0 )
		{
		global $gateway, $gateways;

		if( $from_year > 0 )
			$from_date = sprintf( "%04d", $from_year)."-".sprintf( "%02d", $from_month )."-".sprintf( "%02d", $from_day );

		if( $to_year > 0 )
			{
			$to = mktime( 0, 0, 0, $to_month, $to_day, $to_year );
			if( $to > 0 )
				$to_date = strftime( "%F", $to );
			}

		echo "<td>".$row['orders']."</td>";
		if( strlen( $from_date ) && strlen( $to_date ) )
			echo "<td align='right'><a href='sales.php?from=$from_date&to=$to_date&gateway={$gateways[$gateway]['pg_id']}'>".number_format($row['sales'], $gateways[$gateway]['po_currency_precision'], '.', ',')."</a></td>";
		else
			echo "<td align='right'>".number_format($row['sales'], $gateways[$gateway]['po_currency_precision'], '.', ',')."</td>";
		echo "<td align='right'>".number_format($row['shipping'], $gateways[$gateway]['po_currency_precision'], '.', ',')."</td>";
		echo "<td align='right'>".$row['reship_boxes']."</td>";
		if( strlen( $from_date ) && strlen( $to_date ) )
			echo "<td align='right'><a href='reships_made.php?from=$from_date&to=$to_date&gateway={$gateways[$gateway]['pg_id']}'>".number_format($row['reship_value'], $gateways[$gateway]['po_currency_precision'], '.', ',')."</a></td>";
		else
			echo "<td align='right'>".number_format($row['reship_value'], $gateways[$gateway]['po_currency_precision'], '.', ',')."</td>";
		if( $row['sales'] > 0 )
			echo "<td align='right'>".number_format($row['reship_value']*100/$row['sales'], 1)."</td>";
		else
			echo "<td align='right'></td>";
		if( strlen( $from_date ) && strlen( $to_date ) )
			echo "<td align='right'><a href='refunds.php?from=$from_date&to=$to_date&gateway={$gateways[$gateway]['pg_id']}'>".number_format($row['refund_value'], $gateways[$gateway]['po_currency_precision'], '.', ',')."</a></td>";
		else
			echo "<td align='right'>".number_format($row['refund_value'], $gateways[$gateway]['po_currency_precision'], '.', ',')."</td>";
		if( $row['sales'] > 0 )
			echo "<td align='right'>".number_format($row['refund_value']*100/$row['sales'], 1)."</td>";
		else
			echo "<td align='right'></td>";
	//	echo "<td align='right'>".number_format($row['cm'], $gateways[$gateway]['po_currency_precision'], '.', ',')."</td>";
	//	echo "<td align='right'>".number_format($row['cm_percent'], 1)."</td>";
		echo "<td align='right'>".number_format($row['profit'], 2, '.', ',')."</td>";
		echo "<td align='right'>".number_format($row['sales']-$row['profit']-$row['refund_value'], $gateways[$gateway]['po_currency_precision'], '.', ',')."</td>";
		echo "<td align='right'>".number_format($row['margin_percent'], 1)."</td>";
		}

	$first_year = 2004;
	$this_year = date( 'Y' );
	$this_month = date( 'm' );
	$this_day = date( 'd' );
	$this_script = 'summary_by_gateway.php';
	$now = time(NULL);
	echo "Date/Time is ".strftime( "%d / %m / %Y %H:%M", $now )."<br/>";

	$gateways = array();
	if( $result = mysql_query( "select DISTINCT pg_id, pg_name, po_currency_name, po_currency_precision from payment_gateways join payment_gateway_options on pg_id = po_pg_id order by pg_id desc" ) )
		{
		while( $row = mysql_fetch_assoc($result) )
			$gateways[$row['pg_id']] = $row;
		mysql_free_result($result);
		}

	$gateway = NULL;
	if( array_key_exists( 'gateway', $_GET ) )
		if( (int)( $_GET['gateway'] ) > 0 )
			$gateway = (int)( $_GET['gateway'] );

	$gateway_clause = " and as_gateway = $gateway";
	if(  $gateway == NULL )
		{
		$gateway_clause = '';
		}
	else
		{
		echo "<h2>Gateway {$gateways[$gateway]['pg_name']}, {$gateways[$gateway]['po_currency_name']}</h2>";
		}

	if( array_key_exists( 'year', $_GET ) )
		$want_year = (int) $_GET['year'];

	if( array_key_exists( 'month', $_GET ) )
		$want_month = (int) $_GET['month'];


	if( IsSet( $want_year ) and !IsSet( $want_month ) )		// show all months for want_year
		echo "<h2>All months in year $want_year</h2><br/>";
	else
		if( IsSet( $want_year ) and IsSet( $want_month ) )		// show all days for this month and year
			echo "<h2>All days in $want_month / $want_year</h2><br/>";
		else
			{
			$result = mysql_query("select as_month, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit-as_reship_value) as profit, sum(as_profit-as_reship_value)*100/sum(as_sales) as margin_percent from account_summary where as_year = $want_year $gateway_clause group by as_month order by as_month asc" );
			}


	if( IsSet( $want_year ) and !IsSet( $want_month ) )		// show all months for want_year
		{
		$sql = "select as_month, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent , sum(as_profit-as_reship_value) as profit, sum(as_profit-as_reship_value)*100/sum(as_sales) as margin_percent from account_summary where as_year = $want_year $gateway_clause group by as_month order by as_month asc";
		$result = mysql_query($sql);
		if ($result)
			{
			echo "<table border='1'>";
			echo "<tr>";
			echo "<th>period</th>";
			output_header( );
			echo "</tr>";
			while( $row = mysql_fetch_assoc($result) )
				{
				echo "<tr>";
				echo "<td><a href='$this_script?year=$want_year&month={$row['as_month']}&gateway=$gateway'>{$row['as_month']} / $want_year</a></td>";
				output_row( $row, $want_year, $row['as_month'], 1, $want_year, $row['as_month']+1, 0 );
				echo "</tr>";
				}

			mysql_free_result($result);
			echo "</table>";
			}
		else
			echo "BOOM : $sql";
		}
	else
		if( IsSet( $want_year ) and IsSet( $want_month ) )		// show all days for this month and year
			{
			$result = mysql_query("select as_day, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit-as_reship_value) as profit, sum(as_profit-as_reship_value)*100/sum(as_sales) as margin_percent from account_summary where as_year = $want_year and as_month = $want_month $gateway_clause group by as_day order by as_day asc" );
			if ($result)
				{
				echo "<table border='1'>";
				echo "<tr>";
				echo "<th>period</th>";
				output_header( );
				echo "</tr>";
				while( $row = mysql_fetch_assoc($result) )
					{
					echo "<tr>";
					echo "<td>{$row['as_day']} / $want_month / $want_year</td>";
					output_row( $row, $want_year, $want_month, $row['as_day'], $want_year, $want_month, $row['as_day'] );
					echo "</tr>";
					}

				mysql_free_result($result);
				echo "</table>";
				}
			else
				echo "BOOM";
			}
		else
			{
			// housekeeping
			$unprocessed = array( );
			$earliest_unprocessed = time(NULL);
			$earliest_tx_number = -1;
			$earliest_order_number = -1;
			$last_unprocessed = 0;

			if( $order_result = mysql_query( "select *, YEAR( or_recorded ) as OrYear, MONTH( or_recorded) as OrMonth, DAY( or_recorded) as OrDay, UNIX_TIMESTAMP(or_recorded) as OrTimestamp from shopsystem_orders join transactions on tr_id = or_tr_id where (or_archive_year IS NULL or or_archive_year = YEAR(NOW())) and or_summarised = false AND tr_completed = 1 AND or_card_denied IS NULL AND or_cancelled IS NULL AND tr_status_link < 3 order by or_country, or_site_folder, or_recorded" ) )
				{
				echo "<table border='1'>";
				echo "<tr>";
				echo "<th>period</th>";
				output_header( );
				echo "</tr>";
				while( $orw = mysql_fetch_assoc($order_result) )
					{
					// tr_charge_total IS NOT NULL
					// AND tr_completed = 1
					// AND tr_status_link < 3
					// and (or_paid IS NOT NULL or or_paid_not_shipped IS NOT NULL )
					// and or_reshipment IS NULL
					// AND or_card_denied IS NULL AND or_cancelled IS NULL

					extract( $orw );

//					print( $or_id );

					if( !$or_standby )
						{
						if( $OrTimestamp  > $last_unprocessed )
							$last_unprocessed = $OrTimestamp;
						if( $OrTimestamp < $earliest_unprocessed )
							{
							$earliest_unprocessed = $OrTimestamp;
							$earliest_tx_number = $tr_id;
							$earliest_order_number = $or_id;
							}

						if( array_key_exists( $tr_bank, $unprocessed ) )
							{

							$unprocessed[$tr_bank] = array( 'sales' => $tr_total, 'cm' => $or_profit, 'profit' => $tr_profit, 'num' => 1 );
							}
						else
							{
							$unprocessed[$tr_bank]['sales'] += $tr_total;
							$unprocessed[$tr_bank]['cm'] += $or_profit;
							$unprocessed[$tr_bank]['profit'] += $tr_profit;
							$unprocessed[$tr_bank]['num']++;
							}
						}
					}

//				print( "<br />" );

				mysql_free_result( $order_result );
				echo "</table>";
				}
			else
				{
				echo mysql_error()."<br/>";
				}

			foreach( $unprocessed as $gateway => $entry )
				{
				echo "<h2>Unprocessed Orders {$gateways[$gateway]['pg_id']}, {$gateways[$gateway]['po_currency_name']}</h2><br/>";
				echo "<br/>";
				echo "Total unprocessed sales ".number_format($entry['sales'], $gateways[$gateway]['po_currency_precision'], '.', ',')
					." ({$entry['num']} orders), potential Profit ".number_format($entry['profit'], 2, '.', ',')
					." (%".number_format( $entry['profit'] * 100 /$entry['sales'], 1).")<br/>";
				echo "Earliest unprocessed order ".output_hours( $now - $earliest_unprocessed )." hours ago (";
				echo "<a href='/index.php?act=ShopSystem.ViewOrder&or_id=$earliest_order_number&tr_id=$earliest_tx_number&as_id=514&BreadCrumbs=Administration%20:%20Orders%20:' target='_blank'>$earliest_tx_number</a>";
				echo "), last "
					.output_hours( $now - $last_unprocessed )." hours ago ("
					.number_format( $entry['sales'] * 60 * 60 / ($now - $earliest_unprocessed))."/hr)<br/>";
				}

			$currencies = array( 'EUR', 'USD', 'BTC', 'AUD', 'BRL', 'CNY', 'DKK', 'GBP', 'HKD', 'JPY', 'KRW', 'NOK', 'NZD', 'SEK', 'SGD', 'THB' );

			foreach( $gateways as $gateway=>$desc )
				{
				echo "<table border='1'>";
				echo "<tr>";
				echo "<th>period</th>";
				output_header( );
				echo "</tr>";

				echo "<br/><br/><h2>Processed Orders in {$desc['pg_name']}, gateway ID $gateway / {$desc['po_currency_name']} </h2><br/>";
				$year_result = mysql_query("select as_year, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit-as_reship_value) as profit, sum(as_profit-as_reship_value)*100/sum(as_sales) as margin_percent  from account_summary where as_gateway = $gateway and as_year >= $first_year and as_year <= $this_year group by as_year" );
				if ($year_result)
					{
					while( $row = mysql_fetch_assoc($year_result) )
						{
						echo "<tr>";
						echo "<td><a href='$this_script?year={$row['as_year']}&gateway=$gateway'>{$row['as_year']}</a></td>";
						output_row( $row, $row['as_year'], 1, 1, $row['as_year']+1, 1, 0 );
						echo "</tr>";
						}

					mysql_free_result($year_result);
					}

				$month_result = mysql_query("select sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales), sum(as_profit-as_reship_value) as profit, sum(as_profit-as_reship_value)*100/sum(as_sales) as margin_percent from account_summary where as_gateway = $gateway and as_year = $this_year and as_month = $this_month" );

				if ($month_result)
					{
					if( $row = mysql_fetch_assoc($month_result) )
						{
						echo "<tr>";
						echo "<td><a href='$this_script?year=$this_year&month=$this_month&gateway=$gateway'>This Month</a></td>";
						output_row( $row, $this_year, $this_month, 1, $this_year, $this_month+1, 0 );
						echo "</tr>";
						}

				mysql_free_result($month_result);
				}

				$day_result = mysql_query("select sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit-as_reship_value) as profit, sum(as_profit-as_reship_value)*100/sum(as_sales) as margin_percent  from account_summary where as_gateway = $gateway and as_year = $this_year and as_month = $this_month and as_day = $this_day" );

				if ($day_result)
					{
					if( $row = mysql_fetch_assoc($day_result) )
						{
						echo "<tr>";
						echo "<td>This Day</td>";
						output_row( $row, $this_year, $this_month, $this_day, $this_year, $this_month, $this_day );
						echo "</tr>";
						}

					mysql_free_result($day_result);
					}

				echo "</table>";
				}

			echo "<table border='1'>";
			echo "<tr>";
			echo "<th>period</th>";
			$gateway = 0;
			output_header( );
			echo "</tr>";

			echo "<br/><br/><h2>Processed Orders in all currencies just added togther in a meaningless way</h2><br/>";
			$year_result = mysql_query("select as_year, sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit-as_reship_value) as profit, sum(as_profit-as_reship_value)*100/sum(as_sales) as margin_percent  from account_summary where as_year >= $first_year and as_year <= $this_year group by as_year" );
			if ($year_result)
				{
				while( $row = mysql_fetch_assoc($year_result) )
					{
					echo "<tr>";
					echo "<td><a href='$this_script?year={$row['as_year']}'>{$row['as_year']}</a></td>";
					output_row( $row, $row['as_year'], 1, 1, $row['as_year']+1, 1, 0 );
					echo "</tr>";
					}

				mysql_free_result($year_result);
				}

			$month_result = mysql_query("select sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales), sum(as_profit-as_reship_value) as profit, sum(as_profit-as_reship_value)*100/sum(as_sales) as margin_percent from account_summary where as_year = $this_year and as_month = $this_month" );

			if ($month_result)
				{
				if( $row = mysql_fetch_assoc($month_result) )
					{
					echo "<tr>";
					echo "<td><a href='$this_script?year=$this_year&month=$this_month'>This Month</a></td>";
					output_row( $row, $this_year, $this_month, 1, $this_year, $this_month+1, 0 );
					echo "</tr>";
					}

			mysql_free_result($month_result);
			}

			$day_result = mysql_query("select sum(as_num_orders) as orders, sum(as_sales) as sales, sum(as_shipping_value) as shipping, sum(as_reship_value) as reship_value, sum(as_reship_boxes) as reship_boxes, sum(as_refund_value) as refund_value, sum(as_cm_value) as cm, sum(as_cm_value)*100/sum(as_sales) as cm_percent, sum(as_profit-as_reship_value) as profit, sum(as_profit-as_reship_value)*100/sum(as_sales) as margin_percent  from account_summary where as_year = $this_year and as_month = $this_month and as_day = $this_day" );

			if ($day_result)
				{
				if( $row = mysql_fetch_assoc($day_result) )
					{
					echo "<tr>";
					echo "<td>This Day</td>";
					output_row( $row, $this_year, $this_month, $this_day, $this_year, $this_month, $this_day );
					echo "</tr>";
					}

				mysql_free_result($day_result);
				}

			echo "</table>";
			}


	echo "</html>";
?>
