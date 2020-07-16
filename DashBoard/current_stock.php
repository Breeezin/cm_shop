<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$sql = "select pr_id, pr_name, pro_stock_code, pro_stock_available, pro_source_currency, pro_supplier_price, pro_price from shopsystem_products join  shopsystem_product_extended_options on pr_id = pro_pr_id where pr_ve_id = 2 or pr_ve_id = 4";
	echo "<h1>Current Stock</h1>";
    echo $sql;
    display_query( $sql, 1, array( 9999999 => 'Total' ));
	die;
?>
