<?php 

	$this->param('OrderList');
	$this->param('From');
	$this->param('To');


	$transactions = ListToArray( $this->ATTRIBUTES['OrderList'], "," );
	foreach( $transactions as $Transaction )
	{
		echo "Altering Availability on Order #".$Transaction." from ".$this->ATTRIBUTES['From']." to ".$this->ATTRIBUTES['To']."<br>";

		$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_tr_id = {$Transaction}");
		$OrderDetails = unserialize($Q_Order['or_basket']);

		if (is_array($Q_Order['or_details']))
			$basket = $Q_Order['or_details'];
		else
			$basket = unserialize($Q_Order['or_details']);

		foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
		{

			if (ss_optionExists('Shop Auto Order'))
			{
				// we actually need to alter the entries in shopsystem_stock_orders

				if( array_key_exists( 'pr_ve_id', $entry['Product'] )
				  && $entry['Product']['pr_ve_id'] == 1 )
				{
					// this is an external product, do nothing for now.
				}
				else
				{
					// remove the stock order
					query( "delete from shopsystem_stock_orders where sto_sos_id is NULL and sto_or_id = {$Q_Order['or_id']}" );

					if( $this->ATTRIBUTES['To'] == 'buy' )
					{
						// add it back
						$productName = $entry['Product']['pr_name'];
						if (strlen($entry['Product']['Options']))
							$productName .= ' ('.$entry['Product']['Options'].')';

						$Q_InsertStockOrder = query("
							INSERT INTO shopsystem_stock_orders
								(sto_stock_code, sto_name, sto_or_id, sto_qty)
							VALUES
								('".escape($entry['Product']['pro_stock_code'])."',
								 '$productName', ".safe($Q_Order['or_id']).", ".$entry['Qty'].")
							");

					}
				}

			}

			// take care of the visibility bits in or_basket
			for ($qty=0; $qty < $entry['Qty']; $qty++)
			{
				echo "Altering Availability Display entry ".$qty."<br>";
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'Availabilities',array());

				if( $this->ATTRIBUTES['From'] == "Anything"
				 || (IsSet( $OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty])
				     && $OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] 
				     	== $this->ATTRIBUTES['From'] )
				 || (!IsSet($OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty])
				     && $this->ATTRIBUTES['From'] == "undecided" ))
					$OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] = $this->ATTRIBUTES['To'];
			}
		}
		// Serialize back into the order
		$OrderDetailsSerialized = serialize($OrderDetails);

		$Q_UpdateOrder = query("UPDATE shopsystem_orders 
					SET or_basket = '".escape($OrderDetailsSerialized)."'
					WHERE or_tr_id = {$Transaction}
					");


	}


?>
