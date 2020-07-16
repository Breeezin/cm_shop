<?php 

	requireOnceClass('Field');

	$this->param('or_id');
	$this->param('pr_id');
	$this->param('BoxNo');
	$this->param('BackURL');


	// ok nitty gritty, swap vendor this order product box number.  Make sure that any services for this box are swapped too and adjust stock number accordingly.

	$this->display->layout = 'Administration';
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'];

	$or_id = safe( $this->ATTRIBUTES['or_id'] );
	
	//ss_audit( 'view', 'Orders', $or_id, serialize(print_r( $this->ATTRIBUTES, true )) );

	$Order = getRow("SELECT * FROM shopsystem_orders, transactions left join payment_gateways on tr_bank = pg_id WHERE or_id = $or_id AND tr_id = or_tr_id");
	
	if( strlen( $Order['or_basket'] ) == 0 )
		ss_DumpVarDie( $Order );

	$OrderDetails = unserialize($Order['or_basket']);

	$pr_id = (int) $this->ATTRIBUTES['pr_id'];
	$StockCode = '';
	$BoxNo = 0;
	$newVendor = 0;
	$newStockCode = '';
	$newBoxNo = NULL;

	ss_log_message( "Start of swap, basket looks like this" );
	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $OrderDetails['Basket']['Products'] );

	foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
	{
		if( $OrderDetails['Basket']['Products'][$id]['Product']['pr_id'] == $this->ATTRIBUTES['pr_id'] )
		{
			// this is the one.
			$vendor = $OrderDetails['Basket']['Products'][$id]['Product']['pr_ve_id'];
			$StockCode = $OrderDetails['Basket']['Products'][$id]['Product']['pro_stock_code'];

			$newPrID = 0;
			if( $vendor == 2 )
			{
				$newVendor = 4;
				$newPrID = getField( "select pvm_4_pr_id from product_vendor_map where pvm_2_pr_id = $pr_id" );
			}
			else
				if( $vendor == 4 )
				{
					$newVendor = 2;
					$newPrID = getField( "select pvm_2_pr_id from product_vendor_map where pvm_4_pr_id = $pr_id" );
				}

			if( $newPrID )
			{
				ss_log_message( "new pr_id:$newPrID" );

				// is this already here?
				$found = false;
				foreach ($OrderDetails['Basket']['Products'] as $id2 => $entry2)
				{
					ss_log_message( "Basket position $id2 is pr_id:".$OrderDetails['Basket']['Products'][$id2]['Product']['pr_id'] );
					if( $OrderDetails['Basket']['Products'][$id2]['Product']['pr_id'] == $newPrID )
					{
						ss_log_message( "found at position $id2" );

						// yay, already here
						$BoxNo = $OrderDetails['Basket']['Products'][$id]['Qty']-1;
						$OrderDetails['Basket']['Products'][$id2]['Qty']++;
						$newBoxNo = $OrderDetails['Basket']['Products'][$id2]['Qty']-1;
						$newStockCode = $OrderDetails['Basket']['Products'][$id2]['Product']['pro_stock_code'];
						$found = true;
					}
				}

				if( !$found )
				{
					ss_log_message( "Not found in basket, adding" );

					$newProduct = GetRow( "Select * from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id where pr_id = $newPrID" );
					$newProdEntry = $OrderDetails['Basket']['Products'][$id]['Product'];
					$newProdEntry['pr_id'] = $newPrID;
					$newStockCode = $newProdEntry['pro_stock_code'] = $newProduct['pro_stock_code'];
					$newProdEntry['pr_ve_id'] = $newVendor;
					$newServEntry = array();
					foreach( $OrderDetails['Basket']['Products'][$id]['AddService'] as $sv_id )
					{
						ss_log_message( "swapping service id $sv_id" );
						if( $sv_id > 0 )
						{
							$svPrID = getField( "select sv_pr_id_service from product_service_options where sv_id = $sv_id" );

							ss_log_message( "which is service product ID $svPrID" );
							if( $svPrID > 0 )
							{
								if( $vendor == 2 )
									$newsvPrID = getField( "select pvm_4_pr_id from product_vendor_map where pvm_2_pr_id = $svPrID" );
								else
									if( $vendor == 4 )
										$newsvPrID = getField( "select pvm_2_pr_id from product_vendor_map where pvm_4_pr_id = $svPrID" );

								ss_log_message( "which is for vendor $vendor is service product ID $newsvPrID" );

								if( $newsvPrID > 0 )
								{
									$newsv_id = getField( "select sv_id from product_service_options where sv_pr_id = $newPrID and sv_pr_id_service = $newsvPrID" );
									ss_log_message( "and is new service ID $newsv_id" );

									if( $newsv_id )
										$newServEntry[] = $newsv_id;
								}
							}
						}
					}

					$ne = count( $OrderDetails['Basket']['Products'] );
					ss_log_message( "New entry at position $ne" );
					$OrderDetails['Basket']['Products'][$ne]['Product'] = $newProdEntry;
					$OrderDetails['Basket']['Products'][$ne]['Qty'] = 1;
					$OrderDetails['Basket']['Products'][$ne]['AddService'] = $newServEntry;
					$newBoxNo = 0;
				}

				$OrderDetails['Basket']['Products'][$id]['Qty']--;
				if( $OrderDetails['Basket']['Products'][$id]['Qty'] == 0 )
				{
					$n = count( $OrderDetails['Basket']['Products'] );
					ss_log_message( "Compacting array of size $n" );
					for( $i = $id; $i <= $n-2; $i++ )
					{
						ss_log_message( "$i <- ".$i+1 );
						$OrderDetails['Basket']['Products'][$i] = $OrderDetails['Basket']['Products'][$i+1];
					}

					ss_log_message( "unset element ".($n-1) );
					unset ($OrderDetails['Basket']['Products'][$n-1] );
				}

				// Update the order
				$OrderDetailsSerialized = serialize($OrderDetails);
				$sql = "UPDATE shopsystem_orders SET or_basket = '".escape($OrderDetailsSerialized)."' WHERE or_id = $or_id";
				ss_log_message( $sql );
				query( $sql );

				// stock quantities

				$sql = "Update shopsystem_product_extended_options set pro_stock_available = pro_stock_available + 1 where pro_pr_id = $pr_id";
				ss_log_message( $sql );
				query( $sql );

				$sql = "Update shopsystem_product_extended_options set pro_stock_available = pro_stock_available - 1 where pro_pr_id = $newPrID";
				ss_log_message( $sql );
				query( $sql );

				ss_audit( 'update', 'Products', $pr_id, "-1: Order {$Order['or_tr_id']} one box swapped from vendor $vendor" );
				ss_audit( 'update', 'Products', $newPrID, "+1: Order {$Order['or_tr_id']} one box swapped to vendor $vendor" );

				// OK what else?
				if( strlen( $newStockCode ) && strlen( $StockCode ) && $newVendor && $newBoxNo !== NULL && $BoxNo !== NULL )
				{
					// shopsystem_order_items
					$sql = "update shopsystem_order_items set oi_ve_id = $newVendor, oi_stock_code = '$newStockCode', oi_box_number = $newBoxNo where oi_stock_code = '$StockCode'
								and oi_or_id = $or_id and oi_box_number = $BoxNo" ;

					ss_log_message( $sql );
					query( $sql );

					// ordered_products

					$oldop = GetRow( "select * from ordered_products where op_or_id = $or_id and op_pr_id = $pr_id" );

					if( $oldop )
					{
						if( $oldop['op_quantity'] > 1 )
							$sql = "update ordered_products set op_quantity = op_quantity - 1 where op_or_id = $or_id and op_pr_id = $pr_id";
						else
							$sql = "delete from ordered_products where op_or_id = $or_id and op_pr_id = $pr_id";

						ss_log_message( $sql );
						query( $sql );

						foreach( $oldop as $a => $b )
							if( $b === NULL )
								$oldop[$a] = 'NULL';

						$newop = GetRow( "select * from ordered_products where op_or_id = $or_id and op_pr_id = $newPrID" );

						if( $newop )
							$sql = "update ordered_products set op_quantity = op_quantity + 1 where op_or_id = $or_id and op_pr_id = $newPrID";
						else
							$sql = "insert into ordered_products
										(op_or_id, op_pr_id, op_stock_code, 
										 op_quantity, op_currency_code, op_price_paid,
										 op_supplier_price, op_included_freight, op_extra_freight, op_tracking, 
										 op_pr_name, op_site_folder, op_usd_rate )
									values 
										($or_id, $newPrID, '$newStockCode',
											1, '{$oldop['op_currency_code']}', {$oldop['op_price_paid']},
											{$oldop['op_supplier_price']}, {$oldop['op_included_freight']}, {$oldop['op_extra_freight']}, {$oldop['op_tracking']},
											'{$oldop['op_pr_name']}', '{$oldop['op_site_folder']}', {$oldop['op_usd_rate']} )";

						ss_log_message( $sql );
						query( $sql );
					}
				}
				else
					ss_log_message( "newStockCode:$newStockCode, StockCode:$StockCode, newVendor:$newVendor, newBoxNo:$newBoxNo, BoxNo:$BoxNo" );

				$Q_Notes = query("INSERT INTO shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id)
									VALUES ('".escape("Swapped product code $StockCode to $newStockCode")."', NOW(), ".safe($this->ATTRIBUTES['or_id']).") ");
			}
		}
	}

	ss_log_message( "End of swap, basket looks like this" );
	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $OrderDetails['Basket']['Products'] );

	location($this->atts['BackURL']);

?>
