<?php
	$results = array();
	$Title = "Refunds Summary";
    require_once('session.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/bigger.css\");</style>";

	echo "</head>";

	$q = "select distinct or_archive_year from shopsystem_orders";
	$result = mysql_query($q);
	if ($result)
		{
		echo "This report for year... ";
		while( $row = mysql_fetch_assoc($result) )
			{
			echo "<a href='refunds_summary.php?year={$row['or_archive_year']}'>{$row['or_archive_year']}</a> ";
			}
		}

	echo "<table border='1'>";

	$year = -1;
	if( array_key_exists( 'year', $_GET) )
		$year = $_GET['year'];

	if( $year < 0 )
		echo "<h2>Current Refunds</h2>";
	else
		echo "<h2>Refunds from $year</h2>";

	$clause = 'original.or_archive_year IS NULL';
	if( $year > 0 )
		$clause = "original.or_archive_year = $year";

    $q = "select cn_name, date( original.or_shipped ) as Shipped, tr_currency_code, count(*) as Count, min(tr_id) as minTrID, sum(rfd_amount) as Cost from shopsystem_orders original join transactions on tr_id = original.or_tr_id join shopsystem_refunds on rfd_or_id = or_id left join countries on original.or_country = cn_id where $clause and original.or_cancelled IS NULL and tr_completed = 1  group by cn_name, date( original.or_shipped), tr_currency_code";

	print($q);
	echo "<br/>";
	$refund_result = mysql_query($q);

	$total = 0;

	if ($refund_result)
		{
		echo "<tr><th>Country</th><th>Date</th><th>Count</th><th>Min</th><th>Cost</th></tr>";
		while( $row = mysql_fetch_assoc($refund_result) )
			{
			echo "<tr>";
			echo "<td>".$row['cn_name']."</td>";
			echo "<td><a href='/DashBoard/refunds.php?from=".$row['Shipped']."&to=".$row['Shipped']."' target='_blank'>".$row['Shipped']."</a></td>";
//			echo "<td><a href='/index.php?act=ShopSystem.ViewOrder&or_id=".$row['OrReshipID']."&tr_id=".$row['tr_reship_link']."&as_id=514&BreadCrumbs=Administration%20:%20Orders%20:' target='_blank'>".$row['tr_reship_link']."</a></td>";
			echo "<td>".$row['tr_currency_code']."</td>";
			echo "<td>".$row['Count']."</td>";
			echo "<td>".$row['minTrID']."</td>";
			echo "<td>".$row['Cost']."</td>";
			echo "</tr>";

			$total += $row['Cost'];
			}

		mysql_free_result($refund_result);
		}
	echo "</table>";
	echo "Total: ".number_format($total, 2);
	echo "</html>";
?>
