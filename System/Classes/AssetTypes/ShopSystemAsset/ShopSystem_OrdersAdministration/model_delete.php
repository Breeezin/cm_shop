<?php 

	$this->param('or_id');	
	$this->param('tr_id');	
	$this->param('BackURL');	

	ss_audit( 'delete', 'Orders', $this->ATTRIBUTES['or_id'], '' );

	// add back the stock level
	if (ss_optionExists('Shop Auto Order'))
	{
		$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$this->ATTRIBUTES['or_id']}");
		// not already added back
		if( !strlen( $Q_Order['or_cancelled'] ) )
		{
			$OrderDetails = unserialize($Q_Order['or_basket']);
			foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
			{
				// add back $entry['Qty'] of $OrderDetails['Basket']['Products'][$id]['Product']['pr_id']
				$Q_stockback = query("
						UPDATE shopsystem_product_extended_options 
						set pro_stock_available = pro_stock_available + ".$entry['Qty']." 
						where pro_pr_id = ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']);

				ss_audit( 'update', 'Products', $OrderDetails['Basket']['Products'][$id]['Product']['pr_id'], 'increasing available stock by '.$entry['Qty'] );
			}
		}
	}

	$Q_DeleteOrder = query("
			DELETE FROM shopsystem_orders 			
			WHERE or_id = {$this->ATTRIBUTES['or_id']}
			");

	if (ss_optionExists('Shop Auto Order'))
	{
		query( "delete from shopsystem_stock_orders where sto_sos_id is NULL 
							and sto_or_id = {$this->ATTRIBUTES['or_id']}" );
		query( "delete from shopsystem_order_items where oi_eos_id is NULL 
							and oi_or_id = {$this->ATTRIBUTES['or_id']}" );
	}

	$Q_DeleteTransaction = query("
			DELETE FROM transactions 			
			WHERE tr_id = {$this->ATTRIBUTES['tr_id']}
			");

	locationRelative($this->ATTRIBUTES['BackURL']);

?>
