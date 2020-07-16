<?php
	if( !count( $_GET )
	 && !count( $_POST ) )
	{
		echo "<html>";
		echo "<head>";
		echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";
		echo "</head>";
		echo "<body>";
		echo "<h2>Sales by Payment Gateway and Date</h2><br />Please specify a date range for the report<br />";
		echo "<form ACTION=\"sales_by_gateway.php\" METHOD=GET>";
		echo "<input type=\"text\" name=\"from\" value=\"\"/> (YYYY-MM-DD)<br />";
		echo "<input type=\"text\" name=\"to\" value=\"\"/> (YYYY-MM-DD)<br />";
		echo "<input type=\"submit\" value=\"OK\" name=\"Submit\">";
		/*
		echo "<select name=\"week_start\">";
		echo "<option value=1>Sunday</option>";
		echo "<option value=2>Monday</option>";
		echo "<option value=3>Tuesday</option>";
		echo "<option value=4>Wednesday</option>";
		echo "<option value=5>Thusday</option>";
		echo "<option value=6>Friday</option>";
		echo "<option value=7>Saturday</option>";
		echo "</select>";
		*/
		echo "</form>";
		echo "</body>";
		echo "</html>";
		die;
	}
	$results = array();
	$Title = "Accounting Summary";
	$csv = false;
	if( array_key_exists( 'csv', $_GET ) )
		$csv = true;

	if( $csv )
		{
		require_once('session_quiet.php');
		header('Content-type: application/csv');
		header('Content-Disposition: attachment; filename="sales_by_gateway.csv"');
		}
	else
		{
		require_once('session.php');
		echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";
		echo "</head>";
		echo "<a href=\"".$_SERVER['REQUEST_URI']."&csv=1\">Download as CSV</a><br/>";
		}


	$from = -1;
	if( array_key_exists( 'from', $_GET) )
		$from = $_GET['from'];

	$to = -1;
	if( array_key_exists( 'to', $_GET) )
		$to = $_GET['to'];

	$currency = NULL;
	if( array_key_exists( 'currency', $_GET) )
		$currency = $_GET['currency'];

	$week_start = 1;
	if( array_key_exists( 'week_start', $_GET ) )
		if( (int)( $_GET['week_start'] ) > 0 )
			$week_start = (int)( $_GET['week_start'] );

	$gateway = NULL;
	if( array_key_exists( 'gateway', $_GET ) )
		if( (int)( $_GET['gateway'] ) > 0 )
			$gateway = (int)( $_GET['gateway'] );

	if( $from < 0 or $to < 0 )
		{
		$show = 50;
		if( $csv )
			echo "$show Most Recent Sales\n";
		else
			echo "<h2>$show Most Recent Sales</h2>";
		}
	else
		{
		$show = 9999;
		if( $csv )
			echo "Sales from $from to $to\n";
		else
			echo "<h2>Sales from $from to $to</h2>";
		}

	$clause = '(or_archive_year IS NULL';
	if( $from > 0 )
		$clause .= " OR or_archive_year = ".(int)$from;
	if( $to > 0 )
		$clause .= " OR or_archive_year = ".(int)$to;
	$clause .= ') AND ';
	if( $from > 0 )
		$clause .= "DATE(or_recorded) >= '$from'";
	if( $to > 0 )
		$clause .= " AND DATE(or_recorded) <= '$to'";
	if( $gateway && ($gateway >= 0 ) )
	{
		$clause .= " AND tr_bank = $gateway";
		echo "<h2>Results from payment gateway ID $gateway only</h2>";
	}

	$pp = array();
	$sql = "select pg_id, max( po_currency_precision ) as prec from payment_gateways join payment_gateway_options on pg_id = po_pg_id group by 1";
	if( $prec_results = mysql_query( $sql ) )
		while( $row = mysql_fetch_assoc($prec_results) )
			$pp[$row['pg_id']] = $row['prec'];

	$sql = "select pg_id, pg_name, tr_id, tr_currency_code, tr_order_total, tr_total, DATE(or_recorded) as recDate, DAYOFWEEK(or_recorded) as dow from shopsystem_orders join transactions on tr_id = or_tr_id join payment_gateways where tr_bank = pg_id and tr_completed = 1 and or_cancelled IS NULL and (or_paid IS NOT NULL or or_paid_not_shipped IS NOT NULL) and or_reshipment IS NULL and tr_total > 0 and $clause order by pg_id, DATE(or_recorded) desc limit $show";
	echo $sql."<br />";
	$sales_result = mysql_query( $sql );

	$pg_total = 0;
/*	$pg_wk_total = 0;	*/
	
	if( !$csv )
		echo "<table border='1'>";

	if ($sales_result)
		{
		$headings = array( 'PaymentGateway', 'Day', 'Currency', 'Amount' );

		if( $csv )
			echo implode( ', ', $headings )."\n";
		else
			echo '<tr><th>'.implode( '</th><th>', $headings ).'</th></tr>';

		$amt_key1 = '';
		$amt_key2 = '';
		$amt_key3 = '';		// ==== key1
		$amt_key4 = 0;		// ==== key1
		$amt_total = -1;

		while( $row = mysql_fetch_assoc($sales_result) )
			{
			if( ( $amt_key1 != $row['pg_name'] )
			 || ( $amt_key2 != $row['recDate'] ) )
				{
				if( $amt_total >= 0 ) // emit detail row
					{

					if( $csv )
						echo "$amt_key1, $amt_key2, $amt_key3, $amt_total\n";
					else
						{
						echo "<tr>";
						echo "<td>";
						echo $amt_key1;
						echo "</td>";
						echo "<td>";
						echo $amt_key2;
						echo "</td>";
						echo "<td>";
						echo $amt_key3;
						echo "</td>";
						echo "<td>";
						echo number_format($amt_total, $pp[$amt_key4] );
						echo "</td>";
						echo "</tr>\n";
						}

					if( $amt_key1 != $row['pg_name'] )	// emit pg totals row
						{
						$pg_total += $amt_total;

						/*
						$pg_wk_total += $amt_total;

						if( $csv )
							echo "$amt_key1, WeekTotal , $amt_key3, $pg_wk_total\n";
						else
							{
							echo "<tr>";
							echo "<td>";
							echo $amt_key1;
							echo "</td>";
							echo "<td>";
							echo "WeekTotal";
							echo "</td>";
							echo "<td>";
							echo $amt_key3;
							echo "</td>";
							echo "<td>";
							echo number_format($pg_wk_total, $pp[$amt_key4] );
							echo "</td>";
							echo "</tr>\n";
							}
						*/

						if( $csv )
							echo "$amt_key1, PGTotal , $amt_key3, $pg_total\n";
						else
							{
							echo "<tr>";
							echo "<td>";
							echo $amt_key1;
							echo "</td>";
							echo "<td>";
							echo "PGTotal";
							echo "</td>";
							echo "<td>";
							echo $amt_key3;
							echo "</td>";
							echo "<td>";
							echo number_format($pg_total, $pp[$amt_key4] );
							echo "</td>";
							echo "</tr>\n";
							}

						$pg_total = 0;
/*						$pg_wk_total = 0;	*/
						}
					else	// same pg
						{
						/*
						if( $row['dow'] == $week_start )		// end of week?
							{
							$pg_wk_total += $amt_total;

							if( $pg_wk_total > 0 )
								{
								if( $csv )
									echo "$amt_key1, WeekTotal , $amt_key3, $pg_wk_total\n";
								else
									{
									echo "<tr>";
									echo "<td>";
									echo $amt_key1;
									echo "</td>";
									echo "<td>";
									echo "WeekTotal";
									echo "</td>";
									echo "<td>";
									echo $amt_key3;
									echo "</td>";
									echo "<td>";
									echo number_format($pg_wk_total, $pp[$amt_key4] );
									echo "</td>";
									echo "</tr>\n";
									}

								$pg_wk_total = 0;
								}
							}

						$pg_wk_total += $amt_total;
						*/
						$pg_total += $amt_total;
						}
					}

				$amt_key1 = $row['pg_name'];
				$amt_key2 = $row['recDate'];
				$amt_key3 = $row['tr_currency_code'];
				$amt_key4 = $row['pg_id'];
				$amt_total = $row['tr_total'];
				}
			else
				$amt_total += $row['tr_total'];
			}

		// last line
		if( $csv )
			echo "$amt_key1, $amt_key2, $amt_key3, $amt_total\n";
		else
			{
			echo "<tr>";
			echo "<td>";
			echo $amt_key1;
			echo "</td>";
			echo "<td>";
			echo $amt_key2;
			echo "</td>";
			echo "<td>";
			echo $amt_key3;
			echo "</td>";
			echo "<td>";
			echo number_format($amt_total, $pp[$amt_key4] );
			echo "</td>";
			echo "</tr>\n";
			}

		$pg_total += $amt_total;
		if( $pg_total > 0 )
			{
			if( $csv )
				echo "$amt_key1, PGTotal , $amt_key3, $pg_total\n";
			else
				{
				echo "<tr>";
				echo "<td>";
				echo $amt_key1;
				echo "</td>";
				echo "<td>";
				echo "PGTotal";
				echo "</td>";
				echo "<td>";
				echo $amt_key3;
				echo "</td>";
				echo "<td>";
				echo number_format($pg_total, $pp[$amt_key4] );
				echo "</td>";
				echo "</tr>\n";
				}
			$pg_total = 0;
			}

		mysql_free_result($sales_result);
		}

	if( $csv )
		;
	else
		{
		echo "</table>";
		echo "</html>";
		}

?>
