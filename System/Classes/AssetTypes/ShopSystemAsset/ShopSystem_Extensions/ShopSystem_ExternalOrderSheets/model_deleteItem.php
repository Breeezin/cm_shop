<?php
	$this->param('orsi_id');
	$this->param('BackURL');

	// grab it
	$item = getRow("
		SELECT * FROM shopsystem_order_sheets_items
		WHERE orsi_id = ".safe($this->ATTRIBUTES['orsi_id'])."
	");
	
	// delete it
	$Q_Delete = query("
		DELETE FROM shopsystem_order_sheets_items
		WHERE orsi_id = ".safe($this->ATTRIBUTES['orsi_id'])."
	");

	// fix the total
	$this->fixTotal($item['orsi_ors_id']);
	
	locationRelative("index.php?act=shopsystem_order_sheets.Edit&ors_id={$item['orsi_ors_id']}&BackURL=".ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']));
		
?>
