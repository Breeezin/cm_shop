<?php

	ss_paramKey( $this->ATTRIBUTES, "vendor", 2 );

	$Q_Stock = query("
		SELECT or_purchaser_firstname, or_purchaser_lastname, oi_stock_code,oi_name,oi_or_id,oi_box_number,or_tr_id, oi_ve_id
			FROM shopsystem_order_items, shopsystem_orders
		WHERE oi_or_id = or_id
			AND oi_eos_id IS NULL
			AND oi_ve_id = ".$this->ATTRIBUTES['vendor']."
			AND or_cancelled IS NULL
			AND or_deleted = 0
			AND or_shipped IS NULL
		ORDER BY or_tr_id
	");

	$this->display->layout = 'Administration';
	$this->display->title = 'External Order List';
	
?>
