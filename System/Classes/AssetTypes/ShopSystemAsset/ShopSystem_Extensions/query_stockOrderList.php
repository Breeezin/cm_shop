<?php

	$Q_Stock = query("
		SELECT or_purchaser_firstname, or_purchaser_lastname, sto_stock_code,sto_name,sto_or_id,sto_qty,or_tr_id
			FROM shopsystem_stock_orders, shopsystem_orders
		WHERE sto_or_id = or_id
			AND sto_sos_id IS NULL
		ORDER BY sto_name
	");

	$this->display->layout = 'Administration';
	$this->display->title = 'Stock Order List';
	
?>
