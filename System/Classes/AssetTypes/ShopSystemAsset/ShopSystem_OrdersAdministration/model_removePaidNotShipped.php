<?php 

	$this->param('or_id');	
	$this->param('BackURL');

	ss_audit( 'update', 'Orders', $this->ATTRIBUTES['or_id'], 'removing paid not shipped' );

	$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$this->ATTRIBUTES['or_id']}");

	$Q_Custom = getRow("SELECT * FROM user_groups WHERE ug_name LIKE 'Customers'");

	// we need to update the Availabilities in the or_basket, so they new show up
	if (ss_optionExists('Shop Auto Order'))
	{
		$OrderDetails = unserialize($Q_Order['or_basket']);

		foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
		{
			for ($qty=0; $qty < $entry['Qty']; $qty++) 
			{
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'Availabilities',array());
				$OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] = 'undecided';
			}
		}

		// Serialize back into the order
		$OrderDetailsSerialized = serialize($OrderDetails);

		// now update the order record as unpaid.
		$Q_UpdateOrder = query("
			UPDATE shopsystem_orders 
			SET 
				or_paid_not_shipped = NULL,
				or_paid = NULL,
				or_basket = '".escape($OrderDetailsSerialized)."'
				WHERE or_id = {$this->ATTRIBUTES['or_id']}
			");

		// only if not already on a Supplier Order sheet.
		query( "delete from shopsystem_stock_orders where sto_sos_id is NULL and sto_or_id = ".safe($Q_Order['or_id']) );
		query( "delete from shopsystem_order_items where oi_eos_id is NULL and oi_or_id = ".safe($Q_Order['or_id']) );
	}
	else
	{
		// now update the order record as unpaid.
		$Q_UpdateOrder = query("
			UPDATE shopsystem_orders 
			SET 
				or_paid_not_shipped = NULL,
				or_paid = NULL
				WHERE or_id = {$this->ATTRIBUTES['or_id']}
			");
	}

	$Q_Transaction = getRow("SELECT * FROM transactions WHERE tr_id = {$Q_Order['or_tr_id']} AND tr_completed = 1");	

	if ($Q_Transaction['tr_payment_method'] == 'WebPay_CreditCard_Manual' 
	 or $Q_Transaction['tr_payment_method'] == 'Cheque' 
	 or $Q_Transaction['tr_payment_method'] == 'Direct') 
	{
		$Q_Delete = query("
			DELETE FROM shopsystem_order_products 
			WHERE orpr_or_id = {$Q_Order['or_id']}
			");
	}


	if (ss_optionExists("Shop Acme Rockets")) 
	{
		$result = new Request('ShopSystem.AcmeCalculateOrderProfit',array('or_id'=>$this->ATTRIBUTES['or_id']));
	}

	locationRelative($this->ATTRIBUTES['BackURL']);
?>
