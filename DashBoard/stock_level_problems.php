<?php
    require_once('session.php');
    require_once('func.php');
	$year = 2011;
	if( array_key_exists( 'year', $_GET ) )
		$year = (int) $_GET['year'];
	$nextyear = $year+1;

    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

    mysql_query("create temporary table StockCheck as
select pr_id, SUBSTRING( pr_name, 1, 60), pro_stock_code , pro_stock_available as AvailableForSale, 
	0 as AwaitingPacking, 0 as OnShelf, sum(sil_qty_put_in_stock - sil_shipped_count) as LeftOnInvoices 
	from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id 
		left join supplier_invoice_line on sil_pr_id = pr_id 
	where pr_ve_id = 2 group by pr_id" );

    mysql_query("create temporary table OnPackingList as
select oi_stock_code, count(oi_box_number) as Awaiting from shopsystem_order_items where oi_eos_id IS NULL
group by oi_stock_code" );

    mysql_query("update StockCheck, OnPackingList set AwaitingPacking = Awaiting where pro_stock_code = oi_stock_code");
    mysql_query("update StockCheck set OnShelf = AvailableForSale + AwaitingPacking");

    display_query( "select * from StockCheck where OnShelf != LeftOnInvoices",
		1, array( 9999999 => 'Total' ));

?>
