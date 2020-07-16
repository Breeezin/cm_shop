<?php

	$this->param('ors_id');
	$this->param('Distributor', 0);

	$row = getRow( "select ors_ve_id from shopsystem_order_sheets where ors_id = ".safe($this->ATTRIBUTES['ors_id']) );
	$vendor = $row['ors_ve_id'];

	$Q_OrderSheet = query("
		SELECT * FROM {$this->tableName}
		WHERE ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
	");

	$Q_OrderSheetItems = query("
		SELECT * FROM shopsystem_order_sheets_items join shopsystem_orders on orsi_or_id = or_id left join countries on or_country = cn_id
		join shopsystem_product_extended_options on pro_stock_code = orsi_stock_code
		WHERE orsi_ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
		ORDER BY orsi_or_id, or_us_id, orsi_stock_code
	");
	
//		ORDER BY orsi_stock_code
?>
