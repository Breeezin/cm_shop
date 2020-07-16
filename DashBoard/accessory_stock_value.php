<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	display_query( "select \"Total\", sum( pro_stock_available) as num, sum(pro_stock_available*pro_supplier_price) as \$cost, sum(pro_stock_available*pro_price) as \$sales from shopsystem_products join shopsystem_product_extended_options ON pro_pr_id = pr_id where pro_stock_available > 0 and pr_offline IS NULL and pr_ve_id  = 1" );
	display_query( "select pr_name, sum( pro_stock_available) as num, sum(pro_stock_available*pro_supplier_price) as \$cost, sum(pro_stock_available*pro_price) as \$sales from shopsystem_products join shopsystem_product_extended_options ON pro_pr_id = pr_id where pro_stock_available > 0 and pr_offline IS NULL and pr_ve_id  = 1 group by pr_name order by pr_name" );

	exit;
?>
