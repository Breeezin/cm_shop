<?php
    require_once('session.php');
    require_once('func.php');
    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

	mysql_query("create temporary table foobar as select pr_id, pro_stock_code, pr_name, sum( pro_stock_available) as Stock, 0 as InBasket, 0 as OnShelf from shopsystem_products join shopsystem_product_extended_options ON pro_pr_id = pr_id group by pr_id order by pr_id" );
	mysql_query("create temporary table foobar2 as select StockCode, sum(op_quantity) as Qty from shopsystem_orders join ordered_products on op_or_id = or_id where or_archive_year IS NULL and or_shipped IS NULL and or_deleted = 0 and or_cancelled is NULL and or_out_of_stock IS NULL group by StockCode" );
	mysql_query("update foobar join foobar2 on pro_stock_code = StockCode set InBasket = Qty" );
	mysql_query("update foobar set OnShelf = Stock + InBasket" );
	display_query( "select * from foobar order by Stock desc" );
?>
