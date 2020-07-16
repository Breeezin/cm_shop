<?php
    require_once('session.php');
    require_once('func.php');
	$year = 2011;
	if( array_key_exists( 'year', $_GET ) )
		$year = (int) $_GET['year'];
	$nextyear = $year+1;

    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

    display_query( "select pg_name, tr_id, DATE(or_recorded), or_purchaser_firstname , or_purchaser_lastname , tr_charge_total from shopsystem_orders join transactions on tr_id = or_tr_id left join payment_gateways on tr_bank = pg_id where or_recorded >= '$year-01-01' and or_recorded < '$nextyear-01-01' and tr_completed >=1 and or_cancelled IS NULL and or_paid IS NOT NULL and or_reshipment IS NULL ORDER BY 1, 2;",
		1, array( 9999999 => 'Total' ));

?>
