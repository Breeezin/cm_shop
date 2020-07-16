<?php
	$results = array();
	$Title = "Accounting Summary";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";

	echo "</head>";

	echo "<table border='1'>";
	$show = 100;
	echo "<h2>$show Most Recent Refunds</h2>";

	$refund_result = mysql_query("select shopsystem_refunds.*, shopsystem_orders.or_tr_id from shopsystem_refunds join shopsystem_orders on or_id = rfd_or_id order by rfd_timestamp desc limit $show" );

	if ($refund_result)
		{
		echo "<tr><th>Order</th><th>Amount</th><th>When</th></tr>";
		while( $row = mysql_fetch_assoc($refund_result) )
			{
			echo "<tr>";
			echo "<td><a href='/index.php?act=ShopSystem.ViewOrder&or_id=".$row['rfd_or_id']."&tr_id=".$row['or_tr_id']."&as_id=514&BreadCrumbs=Administration%20:%20Orders%20:' target='_blank'>".$row['or_tr_id']."</a></td>";
			echo "<td>".$row['rfd_amount']."</td>";
			echo "<td>".$row['rfd_timestamp']."</td>";
			echo "</tr>";
			}

		mysql_free_result($refund_result);
		}
	echo "</table>";

	echo "<table border='1'>";
	$show = 100;
	echo "<h2>$show Most Recent Reshipments</h2>";

	$reship_result = mysql_query("select * from shopsystem_orders join transactions on tr_id = or_tr_id where or_reshipment IS NOT NULL and or_archive_year IS NULL order by or_id desc limit $show" );

	if ($reship_result)
		{
		echo "<tr><th>Order</th><th>Amount</th><th>When</th></tr>";
		while( $row = mysql_fetch_assoc($reship_result) )
			{
			echo "<tr>";
			echo "<td><a href='/index.php?act=ShopSystem.ViewOrder&or_id=".$row['or_id']."&tr_id=".$row['or_tr_id']."&as_id=514&BreadCrumbs=Administration%20:%20Orders%20:' target='_blank'>".$row['or_tr_id']."</a></td>";
			echo "<td>".(-$row['or_profit'])."</td>";
			echo "<td>".$row['or_reshipment']."</td>";
			echo "</tr>";
			}

		mysql_free_result($reship_result);
		}
	echo "</table>";
	echo "</html>";
?>
