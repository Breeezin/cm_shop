<?php 

	$this->param('or_id');	
	$this->param('BackURL');
	
	ss_audit( 'update', 'Orders', $this->ATTRIBUTES['or_id'], 'removing paid' );

	$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$this->ATTRIBUTES['or_id']}");
	
	$Q_Custom = getRow("SELECT * FROM user_groups WHERE ug_name LIKE 'Customers'");
				
	// now update the order record as paid.
	$Q_UpdateOrder = query("
			UPDATE shopsystem_orders 
			SET 
				or_paid = NULL
			WHERE or_id = {$this->ATTRIBUTES['or_id']}
	");
	
	
	$Q_Transaction = getRow("SELECT * FROM transactions WHERE tr_id = {$Q_Order['or_tr_id']} AND tr_completed = 1");	

	if ($Q_Transaction['tr_payment_method'] == 'WebPay_CreditCard_Manual' or $Q_Transaction['tr_payment_method'] == 'Cheque' or $Q_Transaction['tr_payment_method'] == 'Direct') {				
		$Q_Delete = query("
			DELETE FROM shopsystem_order_products 
			WHERE orpr_or_id = {$Q_Order['or_id']}
		");
	}
		
	if (ss_optionExists("Shop Acme Rockets")) {
		$result = new Request('ShopSystem.AcmeCalculateOrderProfit',array('or_id'=>$this->ATTRIBUTES['or_id']));
	}

	locationRelative($this->ATTRIBUTES['BackURL']);
		
?>
