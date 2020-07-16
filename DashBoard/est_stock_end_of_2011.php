<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	$sql = "select pr_id, pr_name, pro_stock_available, pro_source_currency, pro_supplier_price from shopsystem_products join  PrExOp2012 on pr_id = pro_pr_id where pr_ve_id = 2 and pr_combo IS NULL and pro_stock_available > 0";
	echo "<h1>Estimated Stock as at end of 2011</h1>";
    echo $sql;
    display_query( $sql, 1, array( 9999999 => 'Total' ));
	die;
?>
