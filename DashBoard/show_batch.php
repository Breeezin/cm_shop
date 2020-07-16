<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

$a = $_GET['date'];

if( strlen( $a ) )
{

    echo "Batched Items on date $a<br/>";
    display_query( "select or_tr_id, orsi_stock_code, orsi_pr_name, orsi_cost_price, orsi_date_shipped, orsi_box_number from shopsystem_order_sheets_items join shopsystem_orders on or_id = orsi_or_id where orsi_batched = '$a' order by or_tr_id
	", 1, array( 9999999 => 'Total' ));
    display_query( "select or_tr_id,  sum(orsi_cost_price) from shopsystem_order_sheets_items join shopsystem_orders on or_id = orsi_or_id where orsi_batched = '$a' group by or_tr_id order by or_tr_id
	", 1, array( 9999999 => 'Total' ));
}
?>
