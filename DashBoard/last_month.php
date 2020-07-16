<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

    echo "In Stock, Product, Sold<br/>";
    display_query( "select pro_stock_available as in_stock, ca_name, pr_name, sum(Quantity) as sold_last_month from shopsystem_orders JOIN transactions on or_tr_id = tr_id JOIN ShopSystem_AcmeOrderProducts ON or_id = OrderLink JOIN shopsystem_products ON pr_id = ProductLink JOIN shopsystem_product_extended_options ON pr_id = pro_pr_id JOIN shopsystem_categories on ca_id = pr_ca_id LEFT JOIN countries on cn_id = or_country where pr_deleted IS NULL and or_archive_year IS NULL and PrExternal = 2 and or_recorded >= subdate( now(), interval 1 month) group by pro_stock_available, pr_name order by 2, 3
	", 1, array( 9999999 => 'Total' ));
?>
