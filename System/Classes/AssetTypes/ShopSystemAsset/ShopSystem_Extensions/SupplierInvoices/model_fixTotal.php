<?php
	
	// $id is passed in

	// Grab the remaining items for this id
	$Q_Items = query("
		SELECT * FROM shopsystem_order_sheets_items
		WHERE orsi_ors_id = $id
	");
	$total = 0;
	
	// add up all the totals from all the items
	while ($item = $Q_Items->fetchRow()) {
		
		// calcualte the item total
		$itemTotal = ($item['orsi_price'] + $item['orsi_shipping']);
		$Q_Update = query("
			UPDATE shopsystem_order_sheets_items
			SET orsi_total = ".ss_decimalFormat($itemTotal)."
			WHERE orsi_id = {$item['orsi_id']}
		");	
		
		// add it to the overall total
		$total += $itemTotal;
	}
	
	// update the total
	$Q_Update = query("
		UPDATE shopsystem_order_sheets 
		SET ors_total = ".ss_decimalFormat($total)."
		WHERE ors_id = $id
	");	

?>
