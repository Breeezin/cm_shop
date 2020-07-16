<?php
	$results = array();
	$Title = "Accounting Summary";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";

	echo "</head>";

	echo "<table border='1'>";

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
		echo "<h2>$show Most Recent Refunds</h2>";
		}
	else
		{
		$show = 9999;
		echo "<h2>Refunds for orders refunded from $from to $to</h2>";
		}


	$clause = '1 = 1';
	if( $from > 0 )
		$clause = "DATE(rfd_timestamp) >= '$from'";
	if( $to > 0 )
		$clause .= " AND DATE(rfd_timestamp) <= '$to'";
	if( $currency )
		$clause .= " AND tr_currency_code = '$currency'";

	if( $gateway && ($gateway >= 0 ) )
	{
		$clause .= " AND tr_bank = $gateway";
		echo "<h2>Results from payment gateway ID $gateway only</h2>";
	}

	$clause2 = '(1 = 1';
	if( $from > 0 )
		{
		if( $pos = strpos( $from, '-' ) )
			{
			$y = substr( $from, 0, $pos );
			$clause2 = "(or_archive_year >= $y";
			}
		}
	if( $to > 0 )
		{
		if( $pos = strpos( $to, '-' ) )
			{
			$y = substr( $to, 0, $pos );
			$clause2 .= " AND or_archive_year <= $y";
			}
		}
	$clause2 .= ')';

	$q = "select countries.*, shopsystem_refunds.*, shopsystem_orders.or_recorded, shopsystem_orders.or_shipped, shopsystem_orders.or_tr_id, shopsystem_orders.or_authorisation_number from shopsystem_orders join transactions on tr_id = or_tr_id join shopsystem_refunds on rfd_or_id = or_id left join countries on or_country = cn_id where tr_completed >= 1 and $clause and ($clause2 or or_archive_year IS NULL) order by rfd_timestamp desc limit $show";
	echo $q;
	echo "<br/>";

	$refund_result = mysql_query($q);

	$total = 0;

	if ($refund_result)
		{
		echo "<tr><th>Country</th><th>Order</th><th>Recorded</th><th>Shipped</th><th>Authorisation</th><th>Amount</th><th>Refunded</th></tr>";
		while( $row = mysql_fetch_assoc($refund_result) )
			{
			echo "<tr>";
			echo "<td>".$row['cn_name']."</td>";
			echo "<td><a href='/index.php?act=ShopSystem.ViewOrder&or_id=".$row['rfd_or_id']."&tr_id=".$row['or_tr_id']."&as_id=514&BreadCrumbs=Administration%20:%20Orders%20:' target='_blank'>".$row['or_tr_id']."</a></td>";
			echo "<td>".$row['or_recorded']."</td>";
			echo "<td>".$row['or_shipped']."</td>";
			echo "<td>".$row['or_authorisation_number']."</td>";
			echo "<td>".$row['rfd_amount']."</td>";
			echo "<td>".$row['rfd_timestamp']."</td>";
			echo "</tr>";

			$total += $row['rfd_amount'];
			}

		mysql_free_result($refund_result);
		}
	echo "</table>";
	echo "Total: ".number_format($total, 2);
	echo "</html>";
?>
