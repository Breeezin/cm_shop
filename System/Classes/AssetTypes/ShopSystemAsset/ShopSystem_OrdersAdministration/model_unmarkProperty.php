<?php 

	$this->param('or_id');	
	$this->param('BackURL');
	
	$this->param('Property','');
	
	
	if( ss_adminCapability( ADMIN_ORDER_STATUS ) )
	{
		/*$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$this->ATTRIBUTES['or_id']}");
		
		$extraSQL = '';
		if (ss_optionExists('Shop Advanced Ordering')) {
			if ($Q_Order['or_paid_not_shipped'] !== null) {
				$extraSQL = ", or_paid_not_shipped = NULL";
			}
		}*/	
		
		ss_audit( 'update', 'Orders', $this->ATTRIBUTES['or_id'], 'removing '.$this->ATTRIBUTES['Property'] );

		$notes = array();
		$notes[] = $_SESSION['User']['us_email'].' removing '.$this->ATTRIBUTES['Property'];
		$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$this->ATTRIBUTES['or_id']}");
		$OrderDetails = unserialize($Q_Order['or_basket']);
		if (is_array($Q_Order['or_details']))
			$basket = $Q_Order['or_details'];
		else
			$basket = unserialize($Q_Order['or_details']);

		switch($this->ATTRIBUTES['Property']) {
			case 'Cancelled':
				// this needs to take back the stock....
				if (ss_optionExists('Shop Auto Order'))
				{
					foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
					{
						// remove $entry['Qty'] of $OrderDetails['Basket']['Products'][$id]['Product']['pr_id']
						$avail = getField( "select pro_stock_available from shopsystem_product_extended_options where pro_pr_id = ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']);
						if( $entry['Qty'] <= $avail )
						{
							$Q_stockback = query("
								UPDATE shopsystem_product_extended_options 
								set pro_stock_available = pro_stock_available - ".$entry['Qty']." 
								where pro_pr_id = ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']);

							ss_audit( 'update', 'Products', $OrderDetails['Basket']['Products'][$id]['Product']['pr_id'], 'descreasing available stock by '.$entry['Qty'] );
						}
						else
						{
							$notes[] = 'Unable to reserve '.$entry['Qty'].' of '.$OrderDetails['Basket']['Products'][$id]['Product']['pr_name'].". $avail available, none reserved for this order.";
						}
					}
				}
				// fall through

			case 'CardDenied':
			case 'Standby':
			case 'Shipped':
			case 'Actioned':
			case 'Reshipment':
			case 'OutOfStock':
			case 'TrackedAndTraced':
				$Q_UpdateOrder = query("
					UPDATE shopsystem_orders 
					SET Or{$this->ATTRIBUTES['Property']} = NULL
					WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
				");
				break;
			
			default: 
				break;

		}

		foreach( $notes as $note )
		{
			$Q_Notes = query("INSERT INTO shopsystem_order_notes
									(orn_text, orn_timestamp, orn_or_id)
								VALUES ('".escape($note)."', NOW(), ".safe($this->ATTRIBUTES['or_id']).") ");
		}

		if (ss_optionExists("Shop Acme Rockets")) {
			$result = new Request('ShopSystem.AcmeCalculateOrderProfit',array('or_id'=>$this->ATTRIBUTES['or_id']));
		}

		doOrderSheetSync( $Q_Order['or_id'] );
	}

	locationRelative($this->ATTRIBUTES['BackURL']);
		
?>
