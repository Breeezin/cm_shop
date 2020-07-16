<?php 

	$this->param('bo_id');	
	$this->param('tr_id');	
	$this->param('BackURL');
	
	
	/*$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$this->ATTRIBUTES['or_id']}");
	
	$Q_Custom = getRow("SELECT * FROM user_groups WHERE ug_name LIKE 'Customers'");
				
	// check the customer has the 'Customers' user group 
	$Q_UserGroups = query("
			SELECT * FROM user_user_groups 
			WHERE uug_us_id = {$Q_Order['or_us_id']} AND uug_ug_id = {$Q_Custom['ug_id']}
	");
	//if the user doenst have the group, then add one
	if (!$Q_UserGroups->numRows()) {
		$Q_UpdateGroup = query("
			INSERT INTO user_user_groups 
				(uug_us_id, uug_ug_id) 
			VALUES 
				({$Q_Order['or_us_id']},  {$Q_Custom['ug_id']})
		");
	}
	*/
	
	// now update the order record as paid.
	$Q_UpdateOrder = query("
			UPDATE booking_form_bookings
			SET bo_paid = 1		
			WHERE bo_id = {$this->ATTRIBUTES['bo_id']}
	");

	
	
	$res = new Request("WebPay.MarkPaid",array(
		'tr_id'	=>	$this->ATTRIBUTES['tr_id'],
	));
		
	/*$basket = unserialize($Q_Order['or_details']);
	//ss_DumpVarDie($basket['OrderProducts']['Products']);
	// add order products into the db.
	foreach($basket['OrderProducts'] as $aProduct) {
		$name = escape("{$aProduct['Product']['pr_name']} ({$aProduct['Product']['Options']})");
		$price = $aProduct['Qty'] * $aProduct['Product']['Price'];
		//$price = escape($this->formatPrice('display', $price));
		
		$Q_Insert = query("
				INSERT INTO shopsystem_order_products 
					(orpr_or_id, orpr_pr_id, orpr_pr_name, orpr_price, orpr_qty) 
				VALUES
					({$Q_Order['or_id']}, {$aProduct['Product']['pr_id']}, '$name', '$price', {$aProduct['Qty']})		
		");
	}
		
	$Q_UpdateOrder = query("
			UPDATE shopsystem_orders 
			SET or_paid = Now() 
			WHERE or_id = {$this->ATTRIBUTES['or_id']}
	")*/;
	
	locationRelative($this->ATTRIBUTES['BackURL']);
		
?>
