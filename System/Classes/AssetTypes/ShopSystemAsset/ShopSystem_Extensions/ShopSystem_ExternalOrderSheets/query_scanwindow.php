<?php

	$this->param('ors_id');

	$row = getRow( "select ors_ve_id from shopsystem_order_sheets where ors_id = ".safe($this->ATTRIBUTES['ors_id']) );
	$vendor = $row['ors_ve_id'];

	$Q_OrderSheet = query("
		SELECT * FROM {$this->tableName}
		WHERE ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
	");

/*
	$Q_OrderSheetItems = query("
		SELECT * FROM shopsystem_order_sheets_items, shopsystem_orders
		WHERE orsi_ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
		  AND orsi_or_id = or_id
		ORDER BY orsi_id
	");
*/

	$Q_OrderSheetItems = query("
		SELECT orsi_stock_code, orsi_pr_name, 
			pr_location,
			count(orsi_box_number) as Boxes
		FROM shopsystem_order_sheets_items, shopsystem_orders, shopsystem_product_extended_options, shopsystem_products
		WHERE orsi_ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
		  AND orsi_or_id = or_id
		  and pr_id = pro_pr_id
		  and pro_stock_code = orsi_stock_code
		  and or_cancelled IS NULL
		  and or_standby IS NULL
		GROUP BY orsi_stock_code, orsi_pr_name, pr_location
		ORDER BY pr_location, orsi_stock_code
	");
	
?>
