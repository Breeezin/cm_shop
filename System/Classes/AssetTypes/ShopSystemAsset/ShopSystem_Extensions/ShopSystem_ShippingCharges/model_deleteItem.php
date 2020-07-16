<?php
	$this->param('ItID');
	$this->param('BackURL');

	// grab it
	$item = getRow("
		SELECT * FROM shopsystem_supplier_order_sheets_items
		WHERE ItID = ".safe($this->ATTRIBUTES['ItID'])."
	");
	
	// delete it
	$Q_Delete = query("
		DELETE FROM shopsystem_supplier_order_sheets_items
		WHERE ItID = ".safe($this->ATTRIBUTES['ItID'])."
	");

	// fix the total
	$this->fixTotal($item['soit_sos_id']);
	
	locationRelative("index.php?act=shopsystem_supplier_order_sheets.Edit&sos_id={$item['soit_sos_id']}&BackURL=".ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']));
		
?>