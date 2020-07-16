<?php
	
	// $id is passed in

	// Grab the remaining items for this id
	$Q_Items = query("
		SELECT * FROM shopsystem_supplier_order_sheets_items
		WHERE soit_sos_id = $id
	");
	$total = 0;
	
	// add up all the totals from all the items
	while ($item = $Q_Items->fetchRow()) {
		
		// calcualte the item total
		$itemTotal = ($item['soit_price'] - $item['soit_discount']) * $item['soit_qty'];
		$Q_Update = query("
			UPDATE shopsystem_supplier_order_sheets_items
			SET soit_total = ".ss_decimalFormat($itemTotal)."
			WHERE ItID = {$item['ItID']}
		");	
		
		// add it to the overall total
		$total += $itemTotal;
	}
	
	// update the total
	$Q_Update = query("
		UPDATE shopsystem_supplier_order_sheets 
		SET sos_total = ".ss_decimalFormat($total)."
		WHERE sos_id = $id
	");	

?>