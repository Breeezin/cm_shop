<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

    echo "Product/Stock Codes<br/>";
    display_query( "select pro_pr_id, pro_stock_code from shopsystem_product_extended_options order by pro_pr_id desc
	", 1, array( 9999999 => 'Total' ));
?>
