<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	display_query( "select pr_name as Name, pro_stock_code as SKU, sum( pro_stock_available) as AvailableToBuy from shopsystem_products join shopsystem_product_extended_options ON pro_pr_id = pr_id where pro_stock_available > 0 and pr_ve_id  = 2 group by pro_stock_code order by pro_stock_code" );
	display_query( " SELECT oi_name, oi_stock_code as SKU, count(oi_box_number) as SoldButNotSent
FROM shopsystem_order_items, shopsystem_orders
WHERE oi_or_id = or_id
AND oi_eos_id IS NULL
AND oi_ve_id = 2
AND or_cancelled IS NULL
AND or_deleted = 0
AND or_shipped IS NULL
group by oi_stock_code
ORDER BY oi_stock_code
" );

	exit;
?>
