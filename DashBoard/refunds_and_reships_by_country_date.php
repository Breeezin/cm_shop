<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$now = getdate();
	$max_year = $now['year'];

    echo "Refunds by Country/Date<br/>";
    display_query( "select tr_currency_code, cn_name, date( or_shipped ) as ShippedDate, min(or_tr_id) as FirstOrder, sum(rfd_amount) as Amount, count( distinct or_id ) as Orders from shopsystem_refunds join shopsystem_orders on rfd_or_id = or_id join transactions on tr_id = or_tr_id left join countries on or_country = cn_id where (or_archive_year >= 2015 or or_archive_year IS NULL)  and  or_deleted = 0 and or_cancelled IS NULL  group by tr_currency_code, cn_name, date( or_shipped )
	", 1, array( 9999999 => 'Total' ));
    echo "Reshipments by Country/Date<br/>";
    display_query( "select tr_currency_code, cn_name, date( original.or_shipped ) as OriginalOrderDate, count(*) as Count1, min(tr_id) as FirstOrdert, -sum(tr_profit) as ReshipCost, count( distinct original.or_id ) as Count2 from shopsystem_orders original join transactions on tr_reship_link = original.or_tr_id join shopsystem_orders reshipment on tr_id = reshipment.or_tr_id left join countries on original.or_country = cn_id where (original.or_archive_year >= 2015 or original.or_archive_year IS NULL) and  reshipment.or_deleted = 0 and reshipment.or_cancelled IS NULL and reshipment.or_reshipment IS NOT NULL and tr_completed = 1  group by tr_currency_code, cn_name, date( original.or_shipped )
	", 1, array( 9999999 => 'Total' ));
?>
