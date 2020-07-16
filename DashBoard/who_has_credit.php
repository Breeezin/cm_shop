<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$sql = "select us_id, us_first_name, us_last_name, us_account_credit, pg_name from users join payment_gateway_options on us_credit_from_gateway_option = po_id join payment_gateways on po_pg_id = pg_id where us_account_credit > 0";
	echo "<h1>Creditors</h1>";
    echo $sql;
    display_query( $sql, 1, array( 9999999 => 'Total' ));
	die;
?>
