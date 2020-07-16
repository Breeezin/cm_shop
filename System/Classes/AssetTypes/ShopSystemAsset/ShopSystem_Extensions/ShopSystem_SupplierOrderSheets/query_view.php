<?php

	$this->param('sos_id');

	$Q_OrderSheet = query("
		SELECT * FROM {$this->tableName}
		WHERE sos_id = ".safe($this->ATTRIBUTES['sos_id'])."
	");

	$Q_OrderSheetItems = query("
		SELECT * FROM shopsystem_supplier_order_sheets_items
		WHERE soit_sos_id = ".safe($this->ATTRIBUTES['sos_id'])."
		ORDER BY ItID
	");
	
?>