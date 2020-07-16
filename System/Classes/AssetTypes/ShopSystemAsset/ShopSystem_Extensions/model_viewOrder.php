<?php
	if (array_key_exists('Go',$this->ATTRIBUTES)) {		// save the order
		ss_audit( 'update', 'Orders', $this->ATTRIBUTES['or_id'], serialize(print_r( $this->ATTRIBUTES, true )) );
		$this->param('Submit','');
		$newCounterInvoiceID = -1;


		$remove = 0;
		if ($this->ATTRIBUTES['Submit'] == 'Return Selected Boxes')
		{
			$countb = 0;
			foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
			{
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'Availabilities',array());
				for ($qty=0; $qty < $entry['Qty']; $qty++)
				{
					if (array_key_exists('Reship',$this->ATTRIBUTES))
					{
						if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['Reship']))
						{
							$OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] = 'deleted';
							$remove += 	$OrderDetails['Basket']['Products'][$id]['Product']['Price'];

							$pr_id = ListFirst($entry['Key'],'_');	
							query( "Update shopsystem_product_extended_options set pro_stock_available = pro_stock_available + 1
								where pro_pr_id  = $pr_id" );

							ss_audit( 'update', 'Products', $pr_id, "Order {$this->ATTRIBUTES['tr_id']} returned 1 box");
							$countb++;

							$option = getRow("
								SELECT pr_name, pro_stock_code, pr_ve_id FROM shopsystem_products
									join shopsystem_product_extended_options on pr_id = pro_pr_id
								WHERE pro_pr_id = {$pr_id}
							");

							$Q_Notes = query("INSERT INTO shopsystem_order_notes
									(orn_text, orn_timestamp, orn_or_id)
								VALUES ('".escape("RETURNED TO STOCK: box number ".($qty+1)." of {$option['pr_name']}")."', NOW(), ".safe($this->ATTRIBUTES['or_id']).") ");

						}
					}
				}
			}

			// Serialize back into the order
			$OrderDetailsSerialized = serialize($OrderDetails);
			
			// Update the order
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_basket = '".escape($OrderDetailsSerialized)."',
					or_follow_up_status = 'Returned $countb'
				WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
			");

/* nooooooooooo
			$total = getField( "select tr_total from transactions where tr_id = ".safe($this->ATTRIBUTES['tr_id']) );
			$total -= $remove;
			$totalCharged = "'".'&euro; '.ss_decimalFormat($total).' EUR'."'";

			$UpdateTransaction = query("
				UPDATE transactions
				SET tr_total = tr_total - $remove,
					tr_charge_total = {$totalCharged},
					tr_order_total = tr_order_total - $remove
				WHERE tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
			");
*/


			$result = new Request('ShopSystem.AcmeCalculateOrderProfit',
							array('or_id'=>$this->ATTRIBUTES['or_id'])
						      );

			locationRelative("index.php?act=ShopSystem.ViewOrder&or_id={$this->ATTRIBUTES['or_id']}&tr_id={$this->ATTRIBUTES['tr_id']}&as_id={$this->ATTRIBUTES['as_id']}");
/*			die;	*/

		}		// returned boxes

		if ($this->ATTRIBUTES['Submit'] == 'Resend Selected Boxes')
		{

			// Resend the ticked boxes, ie reset the sheet entry in the order list so they are accumulated onto another sheet and sent again.
			foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
			{
				$buyThis = 0;
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'InvoiceNumbers',array());
				
				for ($qty=0; $qty < $entry['Qty']; $qty++)
				{
					if (array_key_exists('Reship',$this->ATTRIBUTES))
					{
						if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['Reship']))
						{
							$pr_id = ListFirst($entry['Key'],'_');	
							$option = getRow("
								SELECT pr_name, pro_stock_code, pr_ve_id, pr_is_service FROM shopsystem_products
									join shopsystem_product_extended_options on pr_id = pro_pr_id
								WHERE pro_pr_id = {$pr_id}
							");

							$code = $option['pro_stock_code'];

							if( $option['pr_is_service'] == 'false' )
							{
								query( "Update shopsystem_order_items set oi_eos_id = NULL "
									." where oi_stock_code = '$code' and oi_or_id = {$this->ATTRIBUTES['or_id']} and oi_box_number = ".$qty );
								// check number of lines updated, old format records have no box number
								if( affectedRows() == 0 )
								{
									query( "Update shopsystem_order_items set oi_eos_id = NULL "
										." where oi_stock_code = '$code' and oi_or_id = {$this->ATTRIBUTES['or_id']}" );
									if( affectedRows() == 0 )
									{
										query( "Insert into shopsystem_order_items "
											." ( oi_name, oi_ve_id, oi_eos_id, oi_stock_code, oi_or_id, oi_box_number )"
											." VALUES ( '{$option['pr_name']}', {$option['pr_ve_id']}, NULL, '$code', {$this->ATTRIBUTES['or_id']}, $qty )" );
									}
								}
								$Q_Notes = query("INSERT INTO shopsystem_order_notes
													(orn_text, orn_timestamp, orn_or_id)
												VALUES ('".escape("RESEND: Box ".($qty+1)." of {$option['pr_name']}")."', NOW(), ".safe($this->ATTRIBUTES['or_id']).") ");
							}
						}
					}
				}
			}

			locationRelative("index.php?act=ShopSystem.ViewOrder&or_id={$this->ATTRIBUTES['or_id']}&tr_id={$this->ATTRIBUTES['tr_id']}&as_id={$this->ATTRIBUTES['as_id']}");
		}		// resend boxes


		// create another order with selected items on it.
		if ($this->ATTRIBUTES['Submit'] == 'Reship Selected Boxes')
		{
			// $_SESSION['DefaultCurrency'] = 'EUR';
			// $_SESSION['DefaultCurrency'] = getField( "select tr_currency_code from transactions where tr_id = {$this->ATTRIBUTES['tr_id']}" );	DFW
			$_SESSION['GatewayOption'] = getField( "select tr_gateway_option  from transactions where tr_id = {$this->ATTRIBUTES['tr_id']}" );
			if( $_SESSION['GatewayOption'] == 0 )			// this was done on credit.
				$_SESSION['GatewayOption'] = getField( "select us_credit_from_gateway_option from users where us_id = {$Order['or_us_id']}" );
			// make sure it still exists

			if( !$rw = getRow( "select * from payment_gateway_options where po_id = {$_SESSION['GatewayOption']}" ) )
			{
				$_SESSION['DefaultCurrency'] = getField( "select tr_currency_code from transactions where tr_id = {$this->ATTRIBUTES['tr_id']}" );
				$_SESSION['GatewayOption'] = getField( "select po_id from payment_gateway_options where po_currency = '{$_SESSION['DefaultCurrency']}'" );
			}

			// First step.. empty the basket
			$result = new Request("Asset.Display",array(
				'as_id'	=>	$Shop['as_id'],
				'Service'	=>	'UpdateBasket',
				'Mode'	=>	'Empty',
				'AsService'	=>	true,
				'NoHusk'	=>	1,
			));
			
			// Reship the ticked boxes
			$keyInvoices = array();
			foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
			{
				$buyThis = 0;
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'InvoiceNumbers',array());
				
				for ($qty=0; $qty < $entry['Qty']; $qty++)
				{
					// Update the availability

					if (array_key_exists('Reship',$this->ATTRIBUTES))
					{
						if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['Reship'])) {
							
							$pr_id = ListFirst($entry['Key'],'_');	
							$option = getRow("
								SELECT pro_id, pro_stock_available, pr_name, pr_is_service FROM shopsystem_product_extended_options JOIN  shopsystem_products on pr_id = pro_pr_id
								WHERE pro_pr_id = {$pr_id}
							");
							/*
							if ($option['pro_stock_available'] > 0 )
							{
								ss_log_message( "Reship: removing a box of product {$option['pr_name']} from stock" );
								// If the product option is using the stock level management..
								$Q_UpdateProductOption = query("
									UPDATE shopsystem_product_extended_options
									SET pro_stock_available = ".($option['pro_stock_available']-1)."
									WHERE pro_id = {$option['pro_id']}");

								ss_audit( 'update', 'Products', $pr_id, 'removing/reshipping 1 box');
							}
							else
							{
//								echo "<html>I'm sorry, i can't do that.<br /><br />You have {$option['pro_stock_available']} of product {$option['pr_name']} left<br/>"
//									."<a href='index.php?act=ShopSystem.ViewOrder&or_id={$this->ATTRIBUTES['or_id']}&tr_id={$this->ATTRIBUTES['tr_id']}&as_id={$this->ATTRIBUTES['as_id']}'>"
//									."Back</a></html";
//								die;
							}
							*/

							if( $option['pr_is_service'] == 'false' )
							{
//								ss_log_message( "BEFORE PRODUCT $pr_id" );
//								ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket'] );

								// add the product to the basket
								$result = new Request("Asset.Display",array(
									'as_id'	=>	$Shop['as_id'],
									'Service'	=>	'UpdateBasket',
									'Mode'	=>	'Add',
									'Key'	=>	$pr_id.'_'.$option['pro_id'],
									'Qty'	=>	1,
									'AsService'	=>	true,
									'NoHusk'	=>	1,
									'Reship'    =>  1,
								));

//								ss_log_message( "AFTER PRODUCT $pr_id" );
//								ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket'] );

								// need to select product services as per the original....
								if( array_key_exists( 'AddService', $entry )
								 && is_array( $entry['AddService'] )
								 && count( $entry['AddService'] ) )
									foreach( $entry['AddService'] as $sv_idi )
									{
										// add the service to the basket
										$result = new Request("Asset.Display",array(
											'as_id'	=>	$Shop['as_id'],
											'Service'	=>	'UpdateBasket',
											'Mode'	=>	'Add',
											'Key'	=>	$pr_id.'_'.$option['pro_id'],
											'Qty'	=>	1,
											'AsService'	=>	true,
											'NoHusk'	=>	1,
											'DoIt'    =>  1,
											'Reship'    =>  1,
											'AddService'	=> $sv_idi,
										));
									}

//								ss_log_message( "AFTER SERVICES FOR $pr_id" );
//								ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION['Shop']['Basket'] );
							}

							if (array_key_exists($qty,$OrderDetails['Basket']['Products'][$id]['InvoiceNumbers']))
							{
								$inv = $OrderDetails['Basket']['Products'][$id]['InvoiceNumbers'][$qty];
								if (!array_key_exists($pr_id,$keyInvoices))
									$keyInvoices[$pr_id] = array();
								array_push($keyInvoices[$pr_id],$inv);
							}
						}
					}
				}
			}

			$shippingDetails = unserialize($Order['or_shipping_details']);
			$shippingValues = unserialize($Order['or_shipping_values']);

			// add the order
			$result = new Request("Asset.Display",
				array(  'as_id'	=>	$Shop['as_id'],
						'Service'	=>	'GenerateOrder',
						'ShippingDetails'	=>	$shippingDetails['ShippingDetails'],
						'PurchaserDetails'	=>	$shippingDetails['PurchaserDetails'],
						'ShippingValues'	=>	$shippingValues,
						'us_id'	=>	$Order['or_us_id'],
						'us_name'	=>	array(  'first_name'	=>	$Order['or_purchaser_firstname'],
												'last_name'	=>	$Order['or_purchaser_lastname'],
												),
						'us_email'	=>	$Order['or_purchaser_email'],
						'NoHusk'	=>	1,
						));

			ss_log_message( "Reship result" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
			
			// Update the product's stock availability since this product
			// option has been sold.
			// We do this always.. to prevent people over-ordering products, instead of
			// when the product has been paid for
			foreach($_SESSION['Shop']['Basket']['Products'] as $aProduct)
			{
				$ProductOption = getRow("
					SELECT * FROM shopsystem_product_extended_options
					WHERE pro_pr_id = '{$aProduct['Product']['pr_id']}'
				");
				if ($ProductOption['pro_stock_available'] > $aProduct['Qty'])
				{
					// If the product option is using the stock level management..
					$Q_UpdateProductOption = query("
						UPDATE shopsystem_product_extended_options
						SET pro_stock_available = ".($ProductOption['pro_stock_available']-$aProduct['Qty'])."
						WHERE pro_id = {$ProductOption['pro_id']}
					");
					ss_audit( 'update', 'Products', $aProduct['Product']['pr_id'], "Order {$this->ATTRIBUTES['tr_id']} reship, stock less ".$aProduct['Qty'] );
				}
				else
				{
//					echo "<html>I'm sorry, i can't do that.<br /><br />You have {$ProductOption['pro_stock_available']} of {$aProduct['Product']['pr_name']} left<br/>"
//						."<a href='index.php?act=ShopSystem.ViewOrder&or_id={$this->ATTRIBUTES['or_id']}&tr_id={$this->ATTRIBUTES['tr_id']}&as_id={$this->ATTRIBUTES['as_id']}'>"
//						."Back</a></html";
//					die;
				}
			}						

			$NewOrder = getRow("
				SELECT or_id, or_country FROM shopsystem_orders
				WHERE or_tr_id = ".safe($result->display)."
			");

			$UpdateOrder = query("
				UPDATE shopsystem_orders
				SET or_reshipment = ".ss_TimeStampToSQL(now()).",
					or_paid_not_shipped = ".ss_TimeStampToSQL(now()).",
					or_country = ".((int)$Order['or_country'])."
				WHERE or_tr_id = ".safe($result->display)."
			");

			// now we need to feed in invoice numbers for the products in the order... hmm
			// we have previously stored the invoice numbers of the reshipped products.. so 
			// now we'll apply them to the products in THIS order..
			$reshipOrder = getRow("SELECT * FROM shopsystem_orders, transactions WHERE tr_id = or_tr_id AND tr_id = ".safe($result->display));
			$reshipOrderDetails = unserialize($reshipOrder['or_basket']);			

			$counterInvoices = array();
			foreach ($reshipOrderDetails['Basket']['Products'] as $id => $entry) {

				ss_paramKey($reshipOrderDetails['Basket']['Products'][$id],'InvoiceNumbers',array());
				ss_paramKey($reshipOrderDetails['Basket']['Products'][$id],'Abono',array());
				ss_paramKey($reshipOrderDetails['Basket']['Products'][$id],'Availabilities',array());
				
				for ($qty=0; $qty < $entry['Qty']; $qty++) {

					$reshipOrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] = 'buy';

					$pr_id = ListFirst($entry['Key'],'_');
					if (array_key_exists($pr_id,$keyInvoices) and count($keyInvoices[$pr_id])) {
						$newInvoice = array_pop($keyInvoices[$pr_id]);
						$reshipOrderDetails['Basket']['Products'][$id]['InvoiceNumbers'][$qty] = $newInvoice;
						if (!array_key_exists($newInvoice,$counterInvoices)) {
							// make a new "Abono"
							$oldInvoice = getRow("
								SELECT in_total_value, in_date FROM shopsystem_invoices
								WHERE inv_id = $newInvoice
							");
							if( $oldInvoice['in_total_value'] > 0 )
							{
								$newCounterInvoiceID = newPrimaryKey('ShopSystem_CounterInvoices','CoInID');
								// Make the reverse invoice date, not NOW() but the date of the invoice.

								// TODO if not invoiced, $oldInvoice['in_total_value'] is blank...
								$Q_CreateCounterInvoice = query("
									INSERT INTO ShopSystem_CounterInvoices
										(CoInID, CoInDate, CoInOriginalInvoiceLink, CoInTotal, CoInType)
									VALUES
										($newCounterInvoiceID, '".$oldInvoice['in_date']."'"
										.", $newInvoice ,". $oldInvoice['in_total_value'].", 'Reshipment')
								");
								$counterInvoices[$newInvoice] = $newCounterInvoiceID;
							}
						}
						// apply the counter invoice for the invoice in use
						$reshipOrderDetails['Basket']['Products'][$id]['Abono'][$qty] = $counterInvoices[$newInvoice];
					}
					
				}
			}
			// Serialize back into the order
			$reshipOrderDetailsSerialized = serialize($reshipOrderDetails);
			
			// Update the order
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_basket = '".escape($reshipOrderDetailsSerialized)."'
				WHERE or_tr_id = ".safe($result->display)."
			");

			if( array_key_exists( 'NastyHackNoteValue', $_SESSION ) and (strlen($_SESSION['NastyHackNoteValue']) > 0) )
			{
				$Q_Insert = query("INSERT INTO shopsystem_order_notes
									(orn_text, orn_timestamp, orn_or_id)
								VALUES ('".escape($_SESSION['NastyHackNoteValue'])."', NOW(), ".
														safe($reshipOrder['or_id']).") ");
				unset( $_SESSION['NastyHackNoteValue'] );
			}
			
			
			$UpdateTransaction = query("
				UPDATE transactions
				SET tr_completed = 1,
					tr_timestamp = NOW(),
					tr_total = 0,
					tr_order_total = 0,
					tr_charge_total = '',
					tr_reship_link = ".$this->ATTRIBUTES['tr_id']."
				WHERE tr_id = ".safe($result->display)."
			");

			// if needed Fix tr_gateway_option
			if( getField( "select tr_gateway_option from transactions where tr_id = ".safe($result->display) ) == 0 )
				query( "update transactions, users set tr_gateway_option = us_credit_from_gateway_option where tr_id = ".safe($result->display)." and us_id = {$Order['or_us_id']}" );

			$OrderNotes = query( "Insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) select orn_text, orn_timestamp, {$NewOrder['or_id']} from shopsystem_order_notes where orn_or_id = {$this->ATTRIBUTES['or_id']}" );
			
			locationRelative("index.php?act=ShopSystem.MarkPaidNotShipped&or_id={$NewOrder['or_id']}&BackURL=/index.php?act%3DOnlineShop.ViewOrder%26OrID%3D{$NewOrder['or_id']}%26TrID%3D{$result->display}%26AssetID%3D{$this->ATTRIBUTES['as_id']}");

			//locationRelative("index.php?act=WebPayAdministration.List&as_id={$this->ATTRIBUTES['as_id']}");
			
			//locationRelative("index.php?act=ShopSystem.AcmeReship&or_id={$this->ATTRIBUTES['or_id']}&Reship=");
		}			// END OF RESHIP SELECTED BOXES

		
		startTransaction();
		
		// start of order save....

		if ($this->ATTRIBUTES['Submit'] == 'Invoice Now') 
		{

			$newInvoiceID = newPrimaryKey('shopsystem_invoices','inv_id');
			
			$shippingDetails = unserialize($Order['or_shipping_details']);
			$orderedBy = escape($shippingDetails['ShippingDetails']['first_name'].' '.$shippingDetails['ShippingDetails']['last_name']);
			
//			$orderedBy = escape($Order['or_purchaser_firstname'].' '.$Order['or_purchaser_lastname']);
			
			$Q_Create = query("
				INSERT INTO shopsystem_invoices
					(inv_id, in_document, in_sender_company, in_sender_address, in_sender_phone, in_date,
						in_paymethod, in_destination, in_country, in_boxes, in_parcels,
						in_value, in_unit_value, in_units, in_origin, in_total_value, in_or_id
					)
				VALUES
					($newInvoiceID, 'Factura Björck Bros. S.L.', 'Bjork Bros. S.L.U. Cif:B35702786', 'C/Andres Perdomo s/n Edif. ZF M214, Las Palmas de GC 35008', '928 466336, Fax: 928 468656 ', NOW(),
						'Ingreso en Cuenta', '".$orderedBy."', 'EEUU', '', '1', 
						'1 Kg', '', '', 'Peru', '', ".safe($this->ATTRIBUTES['or_id']).")
			");

		}

		$totalInvoiced = 0;
		$totalCigars = 0;
		$totalCigarBoxes = 0;

		$stockOrderLink = getRow( "select sto_sos_id from shopsystem_stock_orders where sto_or_id = ".safe($this->ATTRIBUTES['or_id']) );

		if( $stockOrderLink['sto_sos_id'] == null )
			// Removing existing products from the out of stock list
			$Q_RemoveExisting = query("
				DELETE FROM shopsystem_stock_orders
				WHERE sto_sos_id is null and sto_or_id = ".safe($this->ATTRIBUTES['or_id'])."
			");		// Removing existing products from the out of stock list


		// Removing existing products from the shipped lines table
		$Q_RemoveExisting = query("
			DELETE FROM shopsystem_shipped_products
			WHERE shp_or_id = ".safe($this->ATTRIBUTES['or_id'])."
		");
		
		$linesCompleted = 0;
		$linesShipped = 0;
		$linesOutOfStock = 0;
		$totalLines = 0;
		
		$invoiceProducts = array();
		$invoiceSwissProducts = array();
		$counterInvoices = array();
		
		// Loop thru all the products in the order
		foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
		{
			
			$buyThis = 0;
			$totalLines += $entry['Qty'];
			
			for ($qty=0; $qty < $entry['Qty']; $qty++) {
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'Availabilities',array());
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'InvoiceNumbers',array());
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'DUANumbers',array());
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'TrackingNumbers',array());
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'DUAStatus',array());
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'Shipped',array());
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'Refund',array());
				ss_paramKey($OrderDetails['Basket']['Products'][$id],'Abono',array());

				// Update the availability
				if (array_key_exists('Status',$this->ATTRIBUTES)) {
					if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['Status'])) {
						$OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] = $this->ATTRIBUTES['Status'][$entry['Key'].'_'.$qty];
						
						// If we need to buy this product, add it to the stock order list
						if ($this->ATTRIBUTES['Status'][$entry['Key'].'_'.$qty] == 'buy') {
							$buyThis++;	
						}
						
					}
				}

				// Update the DUA Status
				if (array_key_exists('DUAStatus',$this->ATTRIBUTES)) {
					// If its entered
					if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['DUAStatus'])) {
						$OrderDetails['Basket']['Products'][$id]['DUAStatus'][$qty] = $this->ATTRIBUTES['DUAStatus'][$entry['Key'].'_'.$qty];
//						ss_DumpVar( $OrderDetails['Basket']['Products'][$id]['DUAStatus'] );
					}
//					else
//						ss_DumpVarDie( $this->ATTRIBUTES['DUAStatus'] );
				}
//				else
//					ss_DumpVarDie( $this->ATTRIBUTES );
					
				

				// Update the DUA Numbers
				if (array_key_exists('DUANumbers',$this->ATTRIBUTES)) {
					// If its entered
					if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['DUANumbers'])) {
						// Then record the date... 
						$OrderDetails['Basket']['Products'][$id]['DUANumbers'][$qty] = $this->ATTRIBUTES['DUANumbers'][$entry['Key'].'_'.$qty];
					}
				}
				
				// Update the Refunds
				if (array_key_exists('Refund',$this->ATTRIBUTES)) 
				{
					$script = getField( "select pg_script from transactions left join payment_gateways on tr_bank = pg_id where tr_id = {$this->ATTRIBUTES['tr_id']}" );
					$request_refund = !strcmp( $script, 'bank_manual.php' );
					ss_log_message( "Request refund:$request_refund tr_id:{$this->ATTRIBUTES['tr_id']}" );

					// If its entered
					if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['Refund']))
					{
						/*
						print_r( $this->ATTRIBUTES['Refund'] );
						Array
						(
							[748_66924_0] => refund_42.5
							[1225_66493_0] => 
							[2046_66679_0] => 
						)
						die;
						*/
						if (ListFirst($this->ATTRIBUTES['Refund'][$entry['Key'].'_'.$qty],'_') == 'refund')
						{
							// there already?
							$there = getRow( "select * from shopsystem_refunds where rfd_or_id = {$this->ATTRIBUTES['or_id']} 
								and rfd_key_qty = '".escape($entry['Key'].'_'.$qty)."'" );

							if( !$there )
							{
								// Then record the refund... 
								if( strpos( $this->ATTRIBUTES['Refund'][$entry['Key'].'_'.$qty], 'card' ) )
									$Q_Refund = query("
										INSERT INTO shopsystem_refunds 
											(rfd_or_id, rfd_key_qty, rfd_amount, rfd_timestamp, rfd_pending)
										VALUES
											({$this->ATTRIBUTES['or_id']}, '".escape($entry['Key'].'_'.$qty)."', '"
											.escape(ListLast($this->ATTRIBUTES['Refund'][$entry['Key'].'_'.$qty],'_'))."', NOW(), true) ");
								else
									$Q_Refund = query("
										INSERT INTO shopsystem_refunds 
											(rfd_or_id, rfd_key_qty, rfd_amount, rfd_timestamp)
										VALUES
											({$this->ATTRIBUTES['or_id']}, '".escape($entry['Key'].'_'.$qty)."', '"
											.escape(ListLast($this->ATTRIBUTES['Refund'][$entry['Key'].'_'.$qty],'_'))."', NOW()) ");

								$OrderDetails['Basket']['Products'][$id]['Refund'][$qty] = 'Refunded_'.ListLast($this->ATTRIBUTES['Refund'][$entry['Key'].'_'.$qty],'_');

								// add an abono for it also..
								if (array_key_exists($qty,$OrderDetails['Basket']['Products'][$id]['InvoiceNumbers']) and
									strlen($OrderDetails['Basket']['Products'][$id]['InvoiceNumbers'][$qty])) {
												
									$invoiceNumber = $OrderDetails['Basket']['Products'][$id]['InvoiceNumbers'][$qty];
									$oldInvoice = getRow("
											SELECT in_total_value, in_date FROM shopsystem_invoices
											WHERE inv_id = $invoiceNumber
											");
									$newCounterInvoiceID = newPrimaryKey('ShopSystem_CounterInvoices','CoInID');
									$abonoTotal = escape(ListLast($this->ATTRIBUTES['Refund'][$entry['Key'].'_'.$qty],'_'));
									$abonoTotal = str_replace(',','.',$abonoTotal);
									$Q_CreateCounterInvoice = query("
										INSERT INTO ShopSystem_CounterInvoices
											(CoInID, CoInDate, CoInOriginalInvoiceLink, CoInTotal, CoInType)
										VALUES
											($newCounterInvoiceID, '".$oldInvoice['in_date']
											."', $invoiceNumber, $abonoTotal, 'Refund')
									");
									
									// apply the counter invoice for the invoice in use
									$OrderDetails['Basket']['Products'][$id]['Abono'][$qty] = $newCounterInvoiceID;							
								}
							}
						}
						if ($this->ATTRIBUTES['Refund'][$entry['Key'].'_'.$qty] == 'unrefund') {
							// Then remove the refund... 
							$Q_Refund = query("
								DELETE FROM shopsystem_refunds 
								WHERE rfd_or_id = {$this->ATTRIBUTES['or_id']}
									AND rfd_key_qty = '".escape($entry['Key'].'_'.$qty)."'
									AND rfd_authorisation_number is NULL
							");

							$OrderDetails['Basket']['Products'][$id]['Refund'][$qty] = '';
						}
					}
				}
				
				// Update the Tracking Numbers
				if (array_key_exists('TrackingNumbers',$this->ATTRIBUTES)) {
					// If its entered
					if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['TrackingNumbers'])) {
						// Then record the date... 
						$OrderDetails['Basket']['Products'][$id]['TrackingNumbers'][$qty] = $this->ATTRIBUTES['TrackingNumbers'][$entry['Key'].'_'.$qty];
					}
				}
				
				// Update the shipped status
				if (array_key_exists('Shipped',$this->ATTRIBUTES)) {
					// If its ticked
					if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['Shipped'])) {
						
						// And not already set
						$justSet = false;
						if (!array_key_exists($qty,$OrderDetails['Basket']['Products'][$id]['Shipped'])) {
							// Then record the date... 
							$OrderDetails['Basket']['Products'][$id]['Shipped'][$qty] = date('Y-m-d',time());
							
							// Send an email to notify the customer..
							$product = getRow("
								SELECT pr_name FROM shopsystem_products
								WHERE pr_id = ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']."
							");
							$emailData = array(
								'first_name'	=>	$Order['or_purchaser_firstname'],
								'Box'	=>	$product['pr_name'],
								'OrderID'	=>	$Order['or_tr_id'],
							);
							
							$emailText = $this->processTemplate('AcmeShippingEmail',$emailData);
							//print($emailText);
							if (file_exists(expandPath("Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".'ShopSystemAsset/sty_invoice.css'))) {
								$stylesheet = "Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".'ShopSystemAsset/sty_invoice.css';
							} else {
								$stylesheet = 'System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css';
							}
							/*$configContactDetails = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true);
							$emailText = "<html><head><STYLE type=\"text/css\">{$stylesheet}</STYLE></head><body>".$emailText."<p>$configContactDetails<p></body></html>";*/

							$emailResult = new Request('Email.Send',array(
								'from'	=>	$shopSetting['AST_SHOPSYSTEM_ADMINEMAIL'],
								'to'	=>	$Order['or_purchaser_email'],
								'subject'	=>	"Re: Your order at {$GLOBALS['cfg']['website_name']}",
								'html'	=>	$emailText,
								'css'	=>	$stylesheet,
								'templateFolder'	=>	$Order['or_site_folder'],
							));
							
							/*require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
							$mailer = new htmlMimeMail();		
							$mailer->setFrom($shopSetting['AST_SHOPSYSTEM_ADMINEMAIL']);
							$mailer->setSubject("Re: Your order at {$GLOBALS['cfg']['website_name']}");				
							$mailer->setHTML($emailText);				*/
							//$mailer->send(array($Order['or_purchaser_email']));				
							$justSet = true;
							
						} 
						
						if ((array_key_exists('Shipped'.$entry['Key'].'_'.$qty,$this->ATTRIBUTES) and !$justSet) or 
						($justSet and array_key_exists('Shipped'.$entry['Key'].'_'.$qty,$this->ATTRIBUTES) and strlen($this->ATTRIBUTES['Shipped'.$entry['Key'].'_'.$qty]))
						
						) {
							$Shipped = new DateField(array(
								'name'			=>	'Shipped',
								'displayName'	=>	'Shipping Date',
								'note'			=>	null,			
								'required'		=>	false,
								'class'			=>	'formborder',
								'verify'		=>	false,
								'unique'		=>	false,
								'defaultValue'	=>	'Now',
								'showCalendar'	=> 	false,
								'size'	=>	'8',	'maxLength'	=>	'10',
								'rows'	=>	'6',	'cols'		=>	'40',			
							));	
							$Shipped->value = $this->ATTRIBUTES['Shipped'.$entry['Key'].'_'.$qty];
							$Shipped->processFormInputValues(null);
							$errors = $Shipped->validate();
							if ($errors === null) {
								$OrderDetails['Basket']['Products'][$id]['Shipped'][$qty] = str_replace("'",'',$Shipped->valueSQL());
							}
						}
						
						//die('');
						// Insert it into the shipped products table again:
						$product = getRow("
							SELECT pr_name FROM shopsystem_products
							WHERE pr_id = ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']."
						");
						
						$daysSinceOrdered = 'NULL';
						// Only calculate where they only have one box
						if (count($OrderDetails['Basket']['Products']) == 1 and $entry['Qty'] == 1) {
							$daysSinceOrdered = round((time() - ss_SQLToTimeStamp($Order['or_recorded']))/(60*60*24));
						}

						if( !IsSet( $OrderDetails['Basket']['Products'][$id]['DUANumbers'][$qty] ) )
							$OrderDetails['Basket']['Products'][$id]['DUANumbers'][$qty] = '';

						$insert = query("
							INSERT INTO shopsystem_shipped_products 
								(shp_or_id, shp_pr_name, shp_stock_code,
									shp_qty,shp_date,shp_customs_number, shp_days_since_ordered)
							VALUES
								({$this->ATTRIBUTES['or_id']}, '".escape($product['pr_name'])."', '".escape($entry['Product']['pro_stock_code'])."',
									1, '".escape($OrderDetails['Basket']['Products'][$id]['Shipped'][$qty])."', '".escape($OrderDetails['Basket']['Products'][$id]['DUANumbers'][$qty])."', $daysSinceOrdered)
						");
						
						$linesShipped++;
					}
				}		// shipped status

				// If we're being invoiced..
				if ($this->ATTRIBUTES['Submit'] == 'Invoice Now') {
					if (array_key_exists('Invoice',$this->ATTRIBUTES)) {
						if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['Invoice']) and $this->ATTRIBUTES['Invoice'][$entry['Key'].'_'.$qty]) {
							// ..set the invoice number
							$OrderDetails['Basket']['Products'][$id]['InvoiceNumbers'][$qty] = $newInvoiceID;
							if ($Order['or_reshipment'] !== null) {
								// if its a reshipment, save the counter invoice number for it also...
								$OrderDetails['Basket']['Products'][$id]['Abono'][$qty] = $newCounterInvoiceID;
							}

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
						}
					}
				}		// end invoice now
	
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

			// if this is an external product...
//			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $entry['Product'] );

			// old ordering code.
			if( array_key_exists('pr_ve_id', $entry['Product']) && ($entry['Product']['pr_ve_id'] == 0) )
			{
//				ss_log_message( "buy = ".$buyThis );
				if ($buyThis > 0) 
				{
//					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $entry['Product'] );
					$productName = getField("
						SELECT pr_name FROM shopsystem_products, shopsystem_product_extended_options
						WHERE pro_stock_code LIKE '".escape($entry['Product']['pro_stock_code'])."'
							AND pr_id = pro_pr_id
					");//$entry['Product']['pr_name'];
					
					if (strlen($entry['Product']['Options'])) {
						$productName .= ' ('.$entry['Product']['Options'].')';
					}
					if( $stockOrderLink['sto_sos_id'] == null )
						$Q_InsertStockOrder = query("
							INSERT INTO shopsystem_stock_orders
								(sto_stock_code, sto_name, sto_or_id, sto_qty)
							VALUES
								('".escape($entry['Product']['pro_stock_code'])."',
								 '$productName', ".safe($this->ATTRIBUTES['or_id']).", ".safe($buyThis).")
						");
				}
			}
		}
		
		// Serialize back into the order
		$OrderDetailsSerialized = serialize($OrderDetails);
		
		// Update the order
		$Q_Update = query("
			UPDATE shopsystem_orders
			SET or_basket = '".escape($OrderDetailsSerialized)."'
			WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
		");
		
		// See if the order has now been fully invoiced (except for out of stock items) etc
		if ($linesCompleted == $totalLines) {
			// If so, flag the order as fully invoiced, as much as possible
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_invoiced = NOW()
				WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
			");
		}

		ss_log_message( "View order ".$this->ATTRIBUTES['or_id']." Shipped $linesShipped of $totalLines" );

		// See if the order has now been fully shipped (except for out of stock items) etc
		if ($linesShipped == $totalLines) {
			// If so, flag the order as fully shipped, as much as possible
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_shipped = NOW(),
					or_paid_not_shipped = NULL
				WHERE or_shipped IS NULL and or_id = ".safe($this->ATTRIBUTES['or_id'])."
			");
		} else {
		/*
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_shipped = NULL
				WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
			");
		*/
		}
		
		// if anythign is out of stock.. flag the order accordingly.
		$oos = getField( "select count(*) from shopsystem_order_sheets_items where orsi_no_stock IS NOT NULL and  orsi_or_id = {$this->ATTRIBUTES['or_id']}" );

		if ($oos > 0) {
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_out_of_stock = NOW()
				WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
			");
		} else {
			$Q_Update = query("
				UPDATE shopsystem_orders
				SET or_out_of_stock = NULL
				WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
			");
		}
		
		commit();	

		// update the received boxes
		if (array_key_exists('Received',$this->ATTRIBUTES) and is_array($this->ATTRIBUTES['Received']) and (count($this->ATTRIBUTES['Received']) > 0 ) )
		{
//			print_r( $this->ATTRIBUTES['Received'] ); die;
			// Resend the ticked boxes, ie reset the sheet entry in the order list so they are accumulated onto another sheet and sent again.
			foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
			{
				for ($qty=0; $qty < $entry['Qty']; $qty++)
				{
					if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['Received']))
					{
						$pr_id = ListFirst($entry['Key'],'_');
						$option = getRow("
							SELECT pr_name, pro_stock_code, pr_ve_id FROM shopsystem_products
								join shopsystem_product_extended_options on pr_id = pro_pr_id
							WHERE pro_pr_id = {$pr_id}
						");

						$code = $option['pro_stock_code'];

						query( "Update shopsystem_order_sheets_items set orsi_received = now() "
							." where orsi_stock_code = '$code' and orsi_or_id = {$this->ATTRIBUTES['or_id']} and orsi_box_number = ".$qty );
						// check number of lines updated, old format records have no box number
						if( affectedRows() == 0 )
						{
							query( "insert into shopsystem_order_sheets_items ( orsi_received, orsi_stock_code, orsi_or_id, orsi_box_number )"
								. " values ( now(), '$code' ,{$this->ATTRIBUTES['or_id']}, $qty )" );
						}
					}
				}
			}
		}

		if (array_key_exists('UndoReceived',$this->ATTRIBUTES) and is_array($this->ATTRIBUTES['UndoReceived']) and (count($this->ATTRIBUTES['UndoReceived']) > 0 ) )
		{
//			print_r( $this->ATTRIBUTES['UndoReceived'] ); die;
			foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
			{
				for ($qty=0; $qty < $entry['Qty']; $qty++)
				{
					if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['UndoReceived']))
					{
						$pr_id = ListFirst($entry['Key'],'_');
						$option = getRow("
							SELECT pr_name, pro_stock_code, pr_ve_id FROM shopsystem_products
								join shopsystem_product_extended_options on pr_id = pro_pr_id
							WHERE pro_pr_id = {$pr_id}
						");

						$code = $option['pro_stock_code'];

						query( "Update shopsystem_order_sheets_items set orsi_received = NULL "
							." where orsi_stock_code = '$code' and orsi_or_id = {$this->ATTRIBUTES['or_id']} and orsi_box_number = ".$qty );
					}
				}
			}
		}

		if (array_key_exists('UndoShipped',$this->ATTRIBUTES) and is_array($this->ATTRIBUTES['UndoShipped']) and (count($this->ATTRIBUTES['UndoShipped']) > 0 ) )
		{
			foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
			{
				for ($qty=0; $qty < $entry['Qty']; $qty++)
				{
					if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['UndoShipped']))
					{
						$pr_id = ListFirst($entry['Key'],'_');
						$option = getRow("
							SELECT pr_name, pro_stock_code, pr_ve_id FROM shopsystem_products
								join shopsystem_product_extended_options on pr_id = pro_pr_id
							WHERE pro_pr_id = {$pr_id}
						");

						$code = $option['pro_stock_code'];

						query( "Update shopsystem_order_sheets_items set orsi_date_shipped = NULL "
							." where orsi_stock_code = '$code' and orsi_or_id = {$this->ATTRIBUTES['or_id']} and orsi_box_number = ".$qty );
					}
				}
			}
		}


		if (array_key_exists('UndoNoStock',$this->ATTRIBUTES) and is_array($this->ATTRIBUTES['UndoNoStock']) and (count($this->ATTRIBUTES['UndoNoStock']) > 0 ) )
		{
			foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
			{
				for ($qty=0; $qty < $entry['Qty']; $qty++)
				{
					if (array_key_exists($entry['Key'].'_'.$qty,$this->ATTRIBUTES['UndoNoStock']))
					{
						$pr_id = ListFirst($entry['Key'],'_');
						$option = getRow("
							SELECT pr_name, pro_stock_code, pr_ve_id FROM shopsystem_products
								join shopsystem_product_extended_options on pr_id = pro_pr_id
							WHERE pro_pr_id = {$pr_id}
						");

						$code = $option['pro_stock_code'];

						query( "Update shopsystem_order_sheets_items set orsi_no_stock = NULL "
							." where orsi_stock_code = '$code' and orsi_or_id = {$this->ATTRIBUTES['or_id']} and orsi_box_number = ".$qty );
					}
				}
			}
		}

		doOrderSheetSync( $this->ATTRIBUTES['or_id'], $OrderDetails['Basket']['Products'] );
		
		$result = new Request('ShopSystem.AcmeCalculateOrderProfit',array('or_id'=>$this->ATTRIBUTES['or_id']));
		// mark the summary as dirty
		$sql = "update account_summary set as_dirty = true where
							as_country = ".((int)$Order['or_country'])."
							and as_site = '{$Order['or_site_folder']}'
							and as_gateway = {$Order['tr_bank']}
							and as_currency = '{$Order['tr_currency_code']}'
							and as_year = YEAR( '{$Order['or_recorded']}' )
							and as_month = MONTH( '{$Order['or_recorded']}' )
							and as_day = DAY( '{$Order['or_recorded']}' )";

		ss_log_message( "Marking summary as dirty, $sql" );
		query( $sql );

		if ($this->ATTRIBUTES['Submit'] == 'Invoice Now') {
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

			if ($Order['or_reshipment'] !== null) {

				// Update the total for the counter invoice
				$Q_Update = query("
					UPDATE ShopSystem_CounterInvoices
					SET CoInTotal = -".ss_decimalFormat($totalInvoiced)."
					WHERE CoInID = $newCounterInvoiceID
				");
			}
			
			
			// Display the invoice fields
			locationRelative("index.php?act=ShopSystem.ViewOrder&or_id={$this->ATTRIBUTES['or_id']}&tr_id={$this->ATTRIBUTES['tr_id']}&as_id={$this->ATTRIBUTES['as_id']}");
		}
		
	}

?>
