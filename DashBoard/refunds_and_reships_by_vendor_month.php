<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$now = getdate();
	$max_year = $now['year'];

	mysql_query( "create temporary table foobar as select ve_name as vendor, EXTRACT( YEAR from rfd_timestamp ) as Year, EXTRACT( MONTH from rfd_timestamp ) as Month, SUM( rfd_amount ) as Refund, SUM( tr_total ) as Total  from shopsystem_orders join transactions on tr_id = or_tr_id left join shopsystem_refunds on rfd_or_id = or_id left join shopsystem_products on pr_id = SUBSTRING_INDEX(rfd_key_qty, '_', 1 ) join vendor on pr_ve_id = ve_id where or_deleted = 0 and or_cancelled IS NULL and (or_archive_year IS NULL OR or_archive_year >= 2016) group by 1, 2, 3" );

	mysql_query( "create temporary table foobar2 as select ve_name as 2Vendor, EXTRACT( YEAR from or_recorded ) as OriginalYear, EXTRACT( MONTH from or_recorded ) as OriginalMonth, sum(op_supplier_price/op_usd_rate) as Cost, min(or_tr_id) as FirstOrderInSet, max(or_tr_id) as LastOrderInSet from shopsystem_orders join transactions on tr_reship_link = or_tr_id  join ordered_products on op_or_id = or_id join shopsystem_products on op_pr_id = pr_id left join vendor on pr_ve_id = ve_id where or_deleted = 0 and or_cancelled IS NULL and tr_completed = 1 and pr_deleted IS NULL and (or_archive_year IS NULL OR or_archive_year >= 2016)  group by 1, 2, 3" );

//	mysql_query( "create temporary table foobar3 as select distinct vendor, Year, Month, 0.0 as Refund, 0.0 as Reship, 0.0 as Total from foobar union select 2Vendor, OriginalYear, OriginalMonth, 0, 0, 0 from foobar2 order by 1, 2, 3" );
	mysql_query( "create temporary table foobar3 as select ve_name as vendor, YEAR( or_recorded ) as Year, MONTH( or_recorded ) as Month, CAST(0 as double) as Refund, CAST(0 as double) as Reship, sum( op_price_paid/op_usd_rate ) as Total, sum( op_profit/op_usd_rate ) as Profit from shopsystem_orders JOIN transactions on or_tr_id = tr_id join ordered_products on op_or_id = or_id join shopsystem_products on op_pr_id = pr_id left join vendor on pr_ve_id = ve_id where or_card_denied IS NULL and or_cancelled IS NULL and or_deleted = 0 and tr_completed = 1 and pr_deleted IS NULL and (or_archive_year IS NULL OR or_archive_year >= 2016) group by pr_ve_id, YEAR( or_recorded ), MONTH( or_recorded )" );

	mysql_query( "update foobar3, foobar set foobar3.Refund = foobar.Refund  where foobar3.vendor = foobar.vendor and foobar3.Year = foobar.Year and foobar3.Month = foobar.Month" );
	mysql_query( "update foobar3, foobar2 set foobar3.Reship = Cost where foobar3.vendor = foobar2.2Vendor and foobar3.Year = foobar2.OriginalYear and foobar3.Month = foobar2.OriginalMonth" );
	mysql_query( "create temporary table foototals as select vendor, Year, 9999999, sum(Refund), sum(Reship), sum(Total), sum(Profit) from foobar3 group by vendor, Year" );
	mysql_query( "insert into foobar3 select * from foototals" );

    echo "Refunds/Reships by vendor/Date<br/>";
    display_query( "select vendor, Year, Month, Refund as '\$Refund', Reship as '\$Reship', Profit as '\$Profit', Total as '\$Total' from foobar3 where vendor IS NOT NULL order by 1, 2, 3", 1, array( 9999999 => 'Total' ));
?>

