<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

$sql = 'select pro_stock_code , pr_name, pro_weight as ShippingWeight, pro_net_weight as NetWeight from shopsystem_products join shopsystem_product_extended_options on pro_pr_id = pr_id where pr_ve_id = 2 and pro_weight > 0 OR pro_net_weight > 0 order by pr_name';
	echo "<h1>Swiss Stock Weights</h1>";
    echo $sql;
    display_query( $sql, 1, array( 9999999 => 'Total' ));
	die;
?>
