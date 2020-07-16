<?php

	$this->param('ors_id');

	$row = getRow( "select ors_ve_id from shopsystem_order_sheets where ors_id = ".safe($this->ATTRIBUTES['ors_id']) );
	$vendor = $row['ors_ve_id'];

	$Q_OrderSheet = query("
		SELECT * FROM {$this->tableName}
		WHERE ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
	");

	query( "create temporary table order_position as select orsi_or_id, orsi_stock_code, min(orsi_sheet_pos) AS position from shopsystem_order_sheets_items where 
		orsi_ors_id = ".safe($this->ATTRIBUTES['ors_id'])." group by orsi_or_id, orsi_stock_code" );

	$Q_OrderSheetItems = query("
		SELECT * FROM shopsystem_orders join order_position on orsi_or_id = or_id
		ORDER BY position
	");

/*
	$Q_OrderSheetItems = query("
		SELECT * FROM shopsystem_order_sheets_items, shopsystem_orders
		WHERE orsi_ors_id = ".safe($this->ATTRIBUTES['ors_id'])."
		  AND orsi_or_id = or_id
		ORDER BY orsi_sheet_pos
	");
*/
		//ORDER BY or_us_id
	
?>
