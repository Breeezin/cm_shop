<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$month = (int) $_GET['month'];
	$year = (int) $_GET['year'];

	$startDate = sprintf( "%04d%02d01", $year, $month );

	$endmonth = $month+1;
	$endyear = $year;
	if( $endmonth > 12 )
	{
		$endmonth = "01";
		$endyear++;
	}

	$endDate = sprintf( "%04d%02d01", $endyear, $endmonth );

	echo "<h1>Kathia report for $month/$year </h1>";

	echo "<h2>Summary shipped for this month </h1>";
    display_query( "
	select pg_name as Gateway, sum(orsi_total) as Sales
		from shopsystem_orders join transactions on tr_id = or_tr_id join payment_gateways on tr_bank = pg_id
			left join shopsystem_order_sheets_items on or_id = orsi_or_id 
		where 
			pg_id != 3 AND
		orsi_date_shipped >= '$startDate' and orsi_date_shipped < '$endDate'
		group by pg_name
		order by pg_name
	", 1, array( 9999999 => 'Total' ));

	echo "<h2>Summary pending shipment for this month (source currency) </h1>";
    display_query( "
	select pg_name as Gateway, sum(orsi_total) as Sales
		from
			shopsystem_orders join transactions on tr_id = or_tr_id join payment_gateways on tr_bank = pg_id
				left join shopsystem_order_sheets_items on or_id = orsi_or_id 
		where
			pg_id != 3 AND
			((or_paid_not_shipped >= '$startDate' and or_paid_not_shipped < '$endDate') OR
			(or_paid >= '$startDate' and or_paid < '$endDate'))
			and orsi_date_shipped IS NULL and orsi_no_stock IS NULL
		group by 
			pg_name
		order by
			pg_name
	", 1, array( 9999999 => 'Total' ));


	echo "<h2>Detail shipped for this month (source currency)</h1>";
    display_query( "
		select pg_name as Gateway, orsi_date_shipped as DateShipped,concat(or_purchaser_firstname, ' ', or_purchaser_lastname) as Purchaser, or_tr_id as OrderNumber, orsi_pr_name as Product, 1, orsi_total as Sales
		from
			shopsystem_orders join transactions on tr_id = or_tr_id join payment_gateways on tr_bank = pg_id
				left join shopsystem_order_sheets_items on or_id = orsi_or_id 
		where
			pg_id != 3 AND
			orsi_date_shipped >= '$startDate' and orsi_date_shipped < '$endDate'
		order by 
			pg_name, orsi_date_shipped;
	", 1, array( 9999999 => 'Total' ));

	echo "<h2>Detail pending shipment for this month (source currency)</h1>";
    display_query( "
		select pg_name as Gateway, orsi_date_shipped as DateShipped,concat(or_purchaser_firstname, ' ', or_purchaser_lastname) as Purchaser, or_tr_id as OrderNumber, orsi_pr_name as Product, 1, orsi_total as Sales
		from
			shopsystem_orders join transactions on tr_id = or_tr_id join payment_gateways on tr_bank = pg_id left join shopsystem_order_sheets_items on or_id = orsi_or_id 
		where 
			pg_id != 3 AND
			((or_paid_not_shipped >= '$startDate' and or_paid_not_shipped < '$endDate') OR
			(or_paid >= '$startDate' and or_paid < '$endDate'))
			and orsi_date_shipped IS NULL and orsi_no_stock IS NULL
		order by 
			pg_name, orsi_date_shipped;
	", 1, array( 9999999 => 'Total' ));

?>
