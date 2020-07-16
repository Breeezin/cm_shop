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

	if( $from < 0 or $to < 0 )
		{
		$show = 50;
		echo "<h2>$show Most Recent Reshipments</h2>";
		}
	else
		{
		$show = 9999;
		echo "<h2>Reshipments for orders made from $from to $to</h2>";
		}


	$clause = '1 = 1';
	if( $from > 0 )
		$clause = "DATE(reshipment.or_recorded) >= '$from'";
	if( $to > 0 )
		$clause .= " AND DATE(reshipment.or_recorded) <= '$to'";

	$clause1 = '1 = 1';
	if( array_key_exists( 'currency', $_GET ) )
		$clause1 = "tr_currency_code = '{$_GET['currency']}'";

	$clause2 = '(1 = 1';
	if( $from > 0 )
		{
		if( $pos = strpos( $from, '-' ) )
			{
			$y = substr( $from, 0, $pos );
			$clause2 = "(reshipment.or_archive_year >= $y";
			}
		}
	if( $to > 0 )
		{
		if( $pos = strpos( $to, '-' ) )
			{
			$y = substr( $to, 0, $pos );
			$clause2 .= " AND reshipment.or_archive_year <= $y";
			}
		}
	$clause2 .= ')';

	$q = "select countries.*, reshipment.*, transactions.*, original.or_id as orOrID, original.or_shipped as orOrShipped from shopsystem_orders reshipment join transactions on tr_id = reshipment.or_tr_id join shopsystem_orders original on original.or_tr_id = tr_reship_link left join countries on original.or_country = cn_id where reshipment.or_reshipment IS NOT NULL and $clause and $clause1 and ($clause2 or reshipment.or_archive_year IS NULL) order by reshipment.or_id desc limit $show";
	echo $q;
	echo "<br/>";
	$reship_result = mysql_query( $q );

	$total = 0;

	if ($reship_result)
		{
		echo "<tr><th>Country</th><th>Order</th><th>Original</th><th>Amount</th><th>Currency</th><th>Shipped</th><th>Reshipped</th></tr>";
		while( $row = mysql_fetch_assoc($reship_result) )
			{
			echo "<tr>";
			echo "<td>".$row['cn_name']."</td>";
			echo "<td><a href='/index.php?act=ShopSystem.ViewOrder&or_id=".$row['or_id']."&tr_id=".$row['or_tr_id']."&as_id=514&BreadCrumbs=Administration%20:%20Orders%20:' target='_blank'>".$row['or_tr_id']."</a></td>";
			echo "<td><a href='/index.php?act=ShopSystem.ViewOrder&or_id=".$row['orOrID']."&tr_id=".$row['tr_reship_link']."&as_id=514&BreadCrumbs=Administration%20:%20Orders%20:' target='_blank'>".$row['tr_reship_link']."</a></td>";
			echo "<td>".(-$row['tr_profit'])."</td>";
			echo "<td>".$row['tr_currency_code']."</td>";
			echo "<td>".$row['orOrShipped']."</td>";		// orginal order shipped date
			echo "<td>".$row['or_shipped']."</td>";
			echo "</tr>";

			$total -= $row['or_profit'];
			}

		mysql_free_result($reship_result);
		}
	echo "</table>";
	echo "Total: ".number_format($total, 2);
	echo "</html>";
?>
