<?php

	function autoInvoice( $or_id )
	{

		$updateOrders = query("select * from shopsystem_orders, transactions where tr_id = or_tr_id 
											and or_invoiced is null and or_reshipment is null and or_id = $or_id" );

		while ($Order = $updateOrders->fetchRow())
		{
			if( strlen( $Order['or_basket'] ) > 0 )
			{
				$OrderDetails = unserialize($Order['or_basket']);

				startTransaction();
				
				$newInvoiceID = newPrimaryKey('shopsystem_invoices','inv_id');
				
				$shippingDetails = unserialize($Order['or_shipping_details']);
				$orderedBy = escape($shippingDetails['ShippingDetails']['first_name'].' '.$shippingDetails['ShippingDetails']['last_name']);
				
	//			$orderedBy = escape($Order['or_purchaser_firstname'].' '.$Order['or_purchaser_lastname']);
				
				echo "Creating invoice for ".$orderedBy."<br/>";

				$Q_Create = query("
					INSERT INTO shopsystem_invoices
						(inv_id, in_document, in_sender_company, in_sender_address, in_sender_phone, in_date,
							in_paymethod, in_destination, in_country, in_boxes, in_parcels,
							in_value, in_unit_value, in_units, in_origin, in_total_value, in_or_id
						)
					VALUES
						($newInvoiceID, 'Factura Björck Bros. S.L.', 'Bjork Bros. S.L.U. Cif:B35702786', 'C/Andres Perdomo s/n Edif. ZF M214, Las Palmas de GC 35008', '928 466336, Fax: 928 468656 ', '".$Order['or_paid']."',
							'Ingreso en Cuenta', '".$orderedBy."', 'EEUU', '', '1', 
							'1 Kg', '', '', 'Peru', '', ".$Order['or_id'].")
				");


				$totalInvoiced = 0;
				$totalCigars = 0;
				$totalCigarBoxes = 0;

				$linesCompleted = 0;
				$linesShipped = 0;
				$linesOutOfStock = 0;
				
				$invoiceProducts = array();
				$invoiceSwissProducts = array();
				$counterInvoices = array();
				
				// Loop thru all the products in the order
				foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
				{
					for ($qty=0; $qty < $entry['Qty']; $qty++) {
						ss_paramKey($OrderDetails['Basket']['Products'][$id],'Availabilities',array());
						ss_paramKey($OrderDetails['Basket']['Products'][$id],'InvoiceNumbers',array());
						ss_paramKey($OrderDetails['Basket']['Products'][$id],'DUANumbers',array());
						ss_paramKey($OrderDetails['Basket']['Products'][$id],'TrackingNumbers',array());
						ss_paramKey($OrderDetails['Basket']['Products'][$id],'DUAStatus',array());
						ss_paramKey($OrderDetails['Basket']['Products'][$id],'Shipped',array());
						ss_paramKey($OrderDetails['Basket']['Products'][$id],'Refund',array());
						ss_paramKey($OrderDetails['Basket']['Products'][$id],'Abono',array());

						// If its been shipped, auto create an invoice
						$OrderDetails['Basket']['Products'][$id]['InvoiceNumbers'][$qty] = $newInvoiceID;
						echo "Marking box $id as invoiced<br/>";

						if ($entry['Product']['Price'] == 0) {
							// Patrick doesn't seem to want this for the free boxes anymore..
							/*$supplierPrice = getRow("
								SELECT * FROM shopsystem_product_extended_options
								WHERE pro_stock_code LIKE '".escape($entry['Product']['pro_stock_code'])."'
							");
							if ($supplierPrice != null and $supplierPrice['pro_supplier_price'] != null) {
								// if the box is free, we record the supplier price and no shipping cost
								$totalInvoiced += $supplierPrice['pro_supplier_price'];
							} else {
								$totalInvoiced += 0;
							}*/
						} else {
							$totalInvoiced += 1 * ($entry['Product']['Price']); // price including freight
						}
						
						$ext = false;
						if( array_key_exists( 'pr_ve_id', $entry['Product'] )
						 && $entry['Product']['pr_ve_id'] >= 1 )
							$ext = true;
						if( !$ext )		// OK, need to make this distinctive for Swiss products
						{
							ss_paramKey($invoiceProducts,$entry['Product']['pr_name'],0);
							$invoiceProducts[$entry['Product']['pr_name']]++;
						}
						else
						{
							ss_paramKey($invoiceSwissProducts,$entry['Product']['pr_name'],0);
							$invoiceSwissProducts[$entry['Product']['pr_name']]++;
						}
						
						// Need to find out how many llamas in this product
						$productCigars = getRow("
							SELECT pr0_883_f AS TheCount FROM shopsystem_products
							WHERE pr_id = ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']."
						");
						$totalCigars += $productCigars['TheCount']; // $entry['Qty']
						$totalCigarBoxes++;
			
						// check if everything is invoiced or not
						if( array_key_exists( $qty, $OrderDetails['Basket']['Products'][$id]['Availabilities'] ) ) {
							if ($OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] == 'instock') {
								if (array_key_exists($qty,$OrderDetails['Basket']['Products'][$id]['InvoiceNumbers'])
									and strlen($OrderDetails['Basket']['Products'][$id]['InvoiceNumbers'][$qty])) {
									$linesCompleted++;
								}
							} else if ($OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] == 'outofstock') {
								$linesCompleted++;						
							}
							if ($OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] == 'outofstock') {
								$linesOutOfStock++;	
							}
						}
						else		// external product
							$linesCompleted++;
					}

				}

				// Serialize back into the order
				$OrderDetailsSerialized = serialize($OrderDetails);

				echo "Reserializeing order<br/>";

				$Q_Update = query("
						UPDATE shopsystem_orders
						SET or_basket = '".escape($OrderDetailsSerialized)."',
							or_invoiced = NOW()
						WHERE or_id = ".$Order['or_id']."
					");

				
				$invoiceProducts = escape(serialize($invoiceProducts));
				$invoiceSwissProducts = escape(serialize($invoiceSwissProducts));
				
				// Update the total for the invoice
				$Q_Update = query("
					UPDATE shopsystem_invoices
					SET in_total_value = '".ss_decimalFormat($totalInvoiced)."',
						in_boxes = '".$totalCigarBoxes."',
						in_units = '".$totalCigars."',
						in_unit_value = '".ss_decimalFormat($totalInvoiced)."',
						in_products = '$invoiceProducts',
						in_swiss_products = '$invoiceSwissProducts'
					WHERE inv_id = $newInvoiceID
				");

				commit();	

			}
		else
			{
			echo "Corrupt basket<br/>";
			}
		} 
	} 

?>
