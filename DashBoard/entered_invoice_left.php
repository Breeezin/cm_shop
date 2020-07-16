<?php
    require_once('session.php');
    require_once('func.php');
	$year = 2011;
	if( array_key_exists( 'year', $_GET ) )
		$year = (int) $_GET['year'];
	$nextyear = $year+1;

    echo "<style type=\"text/css\">@import url(\"/DashBoard/site.css\");</style>";

    display_query( "select pr_id, pr_name, pro_stock_code , pro_stock_available as AvailableForSale, count(oi_box_number) as AwaitingPacking , sum(sil_qty) as TotalOnInvoices from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id left join supplier_invoice_line on sil_pr_id = pr_id left join shopsystem_order_items on pro_stock_code = oi_stock_code and oi_eos_id IS NULL where pr_ve_id = 2 group by pr_id  having pro_stock_available  > sum(sil_qty) - count(oi_box_number)",
		1, array( 9999999 => 'Total' ));

?>
