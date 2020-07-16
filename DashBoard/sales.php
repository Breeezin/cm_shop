<?php
	if( !count( $_GET )
	 && !count( $_POST ) )
	{
		echo "<html>";
		echo "<head>";
		echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";
		echo "</head>";
		echo "<body>";
		echo "<form ACTION=\"sales.php\" METHOD=GET>";
		echo "<input type=\"text\" name=\"from\" value=\"\"/> (YYYY-MM-DD)<br />";
		echo "<input type=\"text\" name=\"to\" value=\"\"/> (YYYY-MM-DD)<br />";
		echo "<input type=\"submit\" value=\"Update\" name=\"Submit\">";
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
		header('Content-Disposition: attachment; filename="sales.csv"');
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
	if( $currency && ($currency != 'ALL' ) )
		$clause .= " AND tr_currency_code = '$currency'";
	if( $gateway && ($gateway >= 0 ) )
	{
		$clause .= " AND tr_bank = $gateway";
		echo "<h2>Results from payment gateway ID $gateway only</h2>";
	}


	if( array_key_exists( 'profit', $_GET ) )
	{
		$sales_result = mysql_query("select or_id from shopsystem_orders join transactions on tr_id = or_tr_id where tr_completed = 1 and or_cancelled IS NULL and (or_paid IS NOT NULL or or_paid_not_shipped IS NOT NULL) and or_reshipment IS NULL and $clause order by or_recorded desc limit $show" );
		if ($sales_result)
		{
			while( $row = mysql_fetch_assoc($sales_result) )
			{
				$or_id = $row['or_id'];
				exec( "wget 'http://www.acmerockets.com/index.php?act=ShopSystem.AcmeCalculateOrderProfit&or_id=$or_id' -O- > /dev/null" );
			}
		}
	}

	if( $currency == 'ALL' )
		echo "<h2>Warning, Summarised currency values in this report are meaningless</h2>";

	$sql = "select * from shopsystem_orders join transactions on tr_id = or_tr_id where tr_completed = 1 and or_cancelled IS NULL and (or_paid IS NOT NULL or or_paid_not_shipped IS NOT NULL) and or_reshipment IS NULL and $clause order by or_recorded desc limit $show";
	echo $sql."<br />";
	$sales_result = mysql_query( $sql );

	$total = 0;
	$profit = 0;

	
	if( !$csv )
		echo "<table border='1'>";

	if ($sales_result)
		{
		if( $csv )
			echo "Order,Authorisation,Amount,When,From\n";
		else
			echo "<tr><th>Order</th><th>Authorisation</th><th>Amount</th><th>CM</th><th>When</th><th>From</th></tr>";
		while( $row = mysql_fetch_assoc($sales_result) )
			{
			if( $csv )
				{
				echo $row['or_tr_id'].",";
				echo "\"".$row['or_authorisation_number']."\",";
				echo $row['tr_charge_total'].",";
				echo $row['tr_profit'].",";
				echo "\"".$row['or_recorded']."\",";
				echo $row['or_site_folder']."\n";
				}
			else
				{
				echo "<tr>";
				echo "<td><a href='/index.php?act=ShopSystem.ViewOrder&or_id=".$row['or_id']."&tr_id=".$row['or_tr_id']."&as_id=514&BreadCrumbs=Administration%20:%20Orders%20:' target='_blank'>".$row['or_tr_id']."</a></td>";
				echo "<td>".$row['or_authorisation_number']."</td>";
				echo "<td>".$row['tr_charge_total']."</td>";
				if( $row['tr_charge_total'] > 0 )
				{
					echo "<td><a href='/index.php?act=ShopSystem.AcmeCalculateOrderProfit&or_id=".$row['or_id']."'>";
					echo $row['tr_profit']."(".number_format( 100*$row['tr_profit']/$row['tr_charge_total'], 1).")";
					echo "</a></td>";
				}
				else
				{
					echo "<td><a href='/index.php?act=ShopSystem.AcmeCalculateOrderProfit&or_id=".$row['or_id']."'>";
					echo $row['tr_profit'];
					echo "</a></td>";
				}
				echo "<td>".$row['or_recorded']."</td>";
				echo "<td>".$row['or_site_folder']."</td>";
				echo "</tr>";
				}


			$total += $row['tr_charge_total'];
			$profit += $row['tr_profit'];
			}

		mysql_free_result($sales_result);
		}
	if( $csv )
		echo "Total,,\"".number_format($total, 2)."\",\"".number_format($profit, 2)."\"\n";
	else
		{
		echo "</table>";
		echo "Total: ".number_format($total, 2);
		echo "<br />CM: ".number_format($profit, 2);
		echo "</html>";
		}

?>
