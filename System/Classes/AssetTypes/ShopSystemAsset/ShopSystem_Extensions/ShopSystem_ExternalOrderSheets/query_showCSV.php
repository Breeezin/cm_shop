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
		ORDER BY or_us_id
	");

	$Q_OrderSheetItems = query("
		SELECT * FROM shopsystem_orders
		WHERE or_id in (select distinct orsi_or_id from shopsystem_order_sheets_items where orsi_ors_id = ".safe($this->ATTRIBUTES['ors_id']).")
		ORDER BY or_id
	");

*/
	$Q_Orders = query( "select distinct orsi_or_id from shopsystem_order_sheets_items where orsi_ors_id = ".safe($this->ATTRIBUTES['ors_id']) );

?>
