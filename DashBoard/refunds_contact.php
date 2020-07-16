<?php
	$results = array();
	$Title = "Accounting Summary";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";

	echo "</head>";
	echo "<table border='1'>";

	$from = NULL;
	if( array_key_exists( 'from', $_GET) )
		$from = $_GET['from'];

	$to = NULL;
	if( array_key_exists( 'to', $_GET) )
		$to = $_GET['to'];

	if( !$from || !$to )
		{
		$show = 50;
		echo "<h2>$show Most Recent Refunds</h2>";
		}
	else
		{
		$show = 9999;
		echo "<h2>Refunds for orders shipped from $from to $to</h2>";
		}


	$clause = '1 = 1';
	if( $from )
		$clause = "or_shipped >= '$from'";
	if( $to )
		$clause .= " AND or_shipped <= '$to'";


	$clause2 = '(1 = 1';
	if( $from )
		{
		if( $pos = strpos( $from, '-' ) )
			{
			$y = substr( $from, 0, $pos );
			$clause2 = "(or_archive_year >= $y";
			}
		}
	if( $to )
		{
		if( $pos = strpos( $to, '-' ) )
			{
			$y = substr( $to, 0, $pos );
			$clause2 .= " AND or_archive_year <= $y";
			}
		}
	$clause2 .= ')';

	$q = "select cn_name, or_id, or_tr_id, or_purchaser_email, or_shipped, sum(rfd_amount) as refund_amount from shopsystem_refunds join shopsystem_orders on rfd_or_id = or_id left join countries on or_country = cn_id where $clause and ($clause2 or or_archive_year IS NULL) and  or_deleted = 0 and or_cancelled IS NULL group by cn_name, or_id, or_tr_id, or_purchaser_email, or_shipped order by cn_id limit $show";
	echo $q;
	echo "<br/>";
	$refund_result = mysql_query( $q );

	$total = 0;

	if ($refund_result)
		{
		echo "<tr><th>Country</th><th>Order</th><th>Email</th><th>Amount</th><th>Shipped</th><th>Notes</th></tr>";
		while( $row = mysql_fetch_assoc($refund_result) )
			{
			echo "<tr>";
			echo "<td>".$row['cn_name']."</td>";
			echo "<td><a href='/index.php?act=ShopSystem.ViewOrder&or_id=".$row['or_id']."&tr_id=".$row['or_tr_id']."&as_id=514&BreadCrumbs=Administration%20:%20Orders%20:' target='_blank'>".$row['or_tr_id']."</a></td>";
			echo "<td>".$row['or_purchaser_email']."</td>";
			echo "<td>".(-$row['refund_amount'])."</td>";
			echo "<td>".$row['or_shipped']."</td>";
			echo "<td>";
			$qn = "select * from shopsystem_order_notes where orn_or_id = ".$row['or_id'];
			$rn = mysql_query( $qn );
			while( $rwn = mysql_fetch_assoc($rn) )
				echo $rwn['orn_text']."&nbsp";
			echo "</td>";
			echo "</tr>";

			$total -= $row['refund_amount'];
			}

		mysql_free_result($refund_result);
		}
	echo "</table>";
	echo "Total: ".number_format($total, 2);
	echo "</html>";
?>
