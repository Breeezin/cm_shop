<?php

	// Firstly, we'll grab some users =b
	$Q_StockList = query("
		SELECT pr_name, pro_stock_code, pro_price, pro_stock_available from shopsystem_products, shopsystem_product_extended_options where pr_id = pro_pr_id
	");

	$stockList = ss_queryToTab($Q_StockList);

?>
