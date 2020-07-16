<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	echo "<h1>2013</h1>";
    display_query( "select cn_name, count(orsi_box_number), sum(orsi_price ) from shopsystem_orders JOIN transactions on or_tr_id = tr_id JOIN shopsystem_order_sheets_items ON or_id = orsi_or_id JOIN shopsystem_product_extended_options ON orsi_stock_code = pro_stock_code JOIN shopsystem_products ON pr_id = pro_pr_id JOIN countries on cn_id = or_country where pr_deleted IS NULL and pr_ve_id = 2 and or_archive_year  = 2013 group by cn_name order by 1
	", 1, array( 9999999 => 'Total' ));

?>
