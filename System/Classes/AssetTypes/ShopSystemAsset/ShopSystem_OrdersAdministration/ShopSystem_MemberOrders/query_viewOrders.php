<?php 
	$usID = ss_getUserID();
	$this->display->title = "View Orders";
	if (array_key_exists('Layout', $this->ATTRIBUTES) and strlen($this->ATTRIBUTES['Layout'])) {
		$this->display->layout = $this->ATTRIBUTES['Layout'];
	}
	$Q_Orders = query("
		SELECT * FROM shopsystem_orders, transactions 
		WHERE tr_id = or_tr_id AND or_us_id = $usID
		AND tr_completed = 1 AND (or_deleted = 0 OR or_deleted IS NULL)
	");	
	
	$data = array();
	
	$data['Q_Orders'] = $Q_Orders;
	$this->useTemplate('ViewOrders', $data);
?>