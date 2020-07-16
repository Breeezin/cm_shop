<?php
	
	$borked = false;
	foreach( $_POST as $shipLine => $val )
	{
		if( strstr( $shipLine, "tracking_" ) )
		{
			if( $pos = strpos( $shipLine, "_" ) )
			{
				$orsi_id = substr( $shipLine, $pos+1 );
				$trackingNumber = escape( $val );
				$itemLine = getRow("select * from shopsystem_order_sheets_items where orsi_id = ".escape($orsi_id) );

				if( !$itemLine )
					continue;

				if( strlen( $trackingNumber ) && ((int) $orsi_id > 0 ) )
				{
					query( "update shopsystem_order_sheets_items
								  set orsi_tracking_number = '$trackingNumber'
									where orsi_id = ".(int)($orsi_id) );

					ss_audit( 'update', 'Orders Sheet Items', $itemLine['orsi_or_id'], 'tracking number on item '.$orsi_id.' is '.$trackingNumber );
				}
			}
		}
		
		if( strstr( $shipLine, "MarkShipped_" ) )
		{
			if( $pos = strpos( $shipLine, "_" ) )
			{
				$orsi_id = substr( $shipLine, $pos+1 );
				$itemLine = getRow("select * from shopsystem_order_sheets_items where orsi_id = ".escape($orsi_id) );

				if( !$itemLine )
					continue;

				if( strlen( $val ) && ((int) $orsi_id > 0 ) )
				{
					ss_audit( 'update', 'Orders Sheet Items', $itemLine['orsi_or_id'], 'mark shipped on '.$itemLine['orsi_stock_code'].' box '.$itemLine['orsi_box_number'] );

					if( !strlen( $itemLine['orsi_date_shipped'] ) )
					{
						$Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$itemLine['orsi_or_id']}");

						query( "update shopsystem_order_sheets_items
									  set orsi_date_shipped = NOW()
										where orsi_id = ".(int)($orsi_id) );

						$prod = getRow( "select * from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id where pro_stock_code = '{$itemLine['orsi_stock_code']}'" );

						if( ( $prod['pr_ve_id'] == 2 ) || ($prod['pr_ve_id'] == 5 ) )
						{
							if( !array_key_exists('MarkNoEmail_'.$orsi_id, $_POST)
							 || ($_POST['MarkNoEmail_'.$orsi_id] != '1') )
							{
								$trackingText = "";

								// fire off an email...
								$emailData = array(
									'first_name'	=>	$Order['or_purchaser_firstname'],
									'Box'	=>	$prod['pr_name'],
									'OrderID'	=>	$Order['or_tr_id'],
									'Tracking' => $trackingText,
								);

								$emailText = $this->processTemplate('../../Templates/AcmeShippingEmail',$emailData);
								if (file_exists(expandPath("Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".'ShopSystemAsset/sty_invoice.css')))
									$stylesheet = "Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".'ShopSystemAsset/sty_invoice.css';
								else
									$stylesheet = 'System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css';

								ss_log_message( "Sending shipping email to ".$Order['or_purchaser_email']." shipped ".$itemLine['orsi_stock_code']." in ".$itemLine['orsi_or_id'] );

								$emailResult = new Request('Email.Send',array(
									'from'	=>	$GLOBALS['cfg']['EmailAddress'],
									'to'	=>	$Order['or_purchaser_email'],
									'subject'	=>	"Box shipped from {$GLOBALS['cfg']['website_name']}",
									'html'	=>	$emailText,
									'css'	=>	$stylesheet,
									'templateFolder'	=>	$Order['or_site_folder'],
									'smtpPort' => 25,
								));
							}
							else
								ss_log_message( "Not sending shipping email" );
						}

						$OrderDetails = unserialize($Order['or_basket']);

						// interface glue...

						// Update the shipped status

		//				ss_DumpVarDie( $OrderDetails['Basket']['Products'] );
						// search through gory order details for this product
						$found_value = null;

						$search = array();
						if( $prod['pr_combo'] )			// this is a combo product, we'll need to split this up before searching.
						{
							$Qp = query( "select * from shopsystem_combo_products join shopsystem_product_extended_options on cpr_pr_id = pro_pr_id where cpr_element_pr_id = {$prod['pr_id']}" );
							while( $r = $Qp->fetchRow() )
							{
								for( $i = $r['cpr_qty']*$itemLine['orsi_box_number']; $i < $r['cpr_qty']*($itemLine['orsi_box_number']+1); $i++ )
									$search[$i] = $r['pro_stock_code'];
							}
						}
						else
						{
							$search[$itemLine['orsi_box_number']] = $itemLine['orsi_stock_code'];
						}


						foreach( $OrderDetails['Basket']['Products'] as $index => $value )
							foreach( $search as $key => $find )
							{
								ss_log_message( "compared {$value['Product']['pro_stock_code']} and $find" );

								if( array_key_exists('Product', $value) && !strcmp($value['Product']['pro_stock_code'], $find ) )
								{
									ss_paramKey($OrderDetails['Basket']['Products'][$index],'Availabilities',array());
									ss_paramKey($OrderDetails['Basket']['Products'][$index],'Shipped',array());

									$OrderDetails['Basket']['Products'][$index]['Shipped'][$key] = date('Y-m-d',time());
									$OrderDetails['Basket']['Products'][$index]['Availabilities'][$key] = 'instock';

									$found_value = $value;
								}
							}

						if( $found_value == null )
						{
							// umm, someone altered the order?
							print( "Unable to find product ".$itemLine['orsi_stock_code']." in order ".$Order['or_tr_id']);
							ss_log_message( "Unable to find product any of these in Order ".$Order['or_tr_id']);
							ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $search );
							$borked = true;
						}
						else
						{
							// it can go back in the order...
							$OrderDetailsSerialized = serialize($OrderDetails);

							// Update the order
							$Q_Update = query("
								UPDATE shopsystem_orders
								SET or_basket = '".escape($OrderDetailsSerialized)."'
								WHERE or_id = ".safe($itemLine['orsi_or_id'])."
							");
						}

						// now look through all the items in this order to see if all are shipped, if so, mark the whole thing as shipped.
						$sh1 = getRow( "select count(*) as count from shopsystem_order_sheets_items where orsi_or_id = {$itemLine['orsi_or_id']} and orsi_date_shipped IS NOT NULL" );
						$sh2 = getRow( "select count(*) as count from shopsystem_order_items where oi_or_id = {$itemLine['orsi_or_id']}" );
						if( $sh1['count'] == $sh2['count'] )
							$Q_Update = query("
								UPDATE shopsystem_orders
								SET or_shipped = NOW(),
									or_paid_not_shipped = NULL
								WHERE or_id = ".safe($itemLine['orsi_or_id'])."
							");
					}
				}
			}
		}

		if( strstr( $shipLine, "MarkNoStock_" ) )
			if( $pos = strpos( $shipLine, "_" ) )
			{
				$orsi_id = substr( $shipLine, $pos+1 );
				$itemLine = getRow("select * from shopsystem_order_sheets_items where orsi_id = ".escape($orsi_id) );

				if( !$itemLine )
					continue;

				if( strlen( $val ) && ((int) $orsi_id > 0 ) )
				{
					ss_audit( 'update', 'Orders Sheet Items', $itemLine['orsi_or_id'], 'mark out of stock on '.$itemLine['orsi_stock_code'].' box '.$itemLine['orsi_box_number'] );

					query( "update shopsystem_order_sheets_items
								  set orsi_no_stock = NOW()
									where orsi_id = ".(int)($orsi_id) );

					$prod = getRow( "select * from shopsystem_products join shopsystem_product_extended_options on pr_id = pro_pr_id where pro_stock_code = '{$itemLine['orsi_stock_code']}'" );

					$Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$itemLine['orsi_or_id']}");
					$OrderDetails = unserialize($Order['or_basket']);
					// interface glue...

					// Update the shipped status

	//				ss_DumpVarDie( $OrderDetails['Basket']['Products'] );
					// search through gory order details for this product
					$found_value = null;

					// these are components of a combo product?

					$search = array();
					if( $prod['pr_combo'] )			// this is a combo product, we'll need to split this up before searching.
					{
						$Qp = query( "select * from shopsystem_combo_products join shopsystem_product_extended_options on cpr_pr_id = pro_pr_id where cpr_element_pr_id = {$prod['pr_id']}" );
						while( $r = $Qp->fetchRow() )
						{
							for( $i = $r['cpr_qty']*$itemLine['orsi_box_number']; $i < $r['cpr_qty']*($itemLine['orsi_box_number']+1); $i++ )
								$search[$i] = $r['pro_stock_code'];
						}
					}
					else
					{
						$search[$itemLine['orsi_box_number']] = $itemLine['orsi_stock_code'];
					}

					$Price = 'Unknown';

					foreach( $OrderDetails['Basket']['Products'] as $index => $value )
						foreach( $search as $key => $find )
							if( array_key_exists('Product', $value) )
							{
								ss_log_message( "Comparing {$value['Product']['pro_stock_code']} to $find" );
								if( !strcmp($value['Product']['pro_stock_code'], $find ) )
								{
									ss_paramKey($OrderDetails['Basket']['Products'][$index],'Availabilities',array());
									$OrderDetails['Basket']['Products'][$index]['Availabilities'][$key] = 'outofstock';
									$Price = $value['Product']['Price'];

									$found_value = $value;
								}
							}

					if( $found_value === null )
					{
					// umm, someone altered the order?
						print( "Unable to find product ".$itemLine['orsi_stock_code']." in order ".$Order['or_tr_id']);
						ss_log_message( "Unable to find product any of these in Order ".$Order['or_tr_id']);
						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $search );
						$borked = true;
					}

					// price needs to be got out of the serialised mess, can't be if it's part of a combo product

					//if( ( $prod['pr_ve_id'] == 2 ) || ($prod['pr_ve_id'] == 5 ) )
					if( true )
					{
						if( !array_key_exists('MarkNoEmail_'.$orsi_id, $_POST)
						 || ($_POST['MarkNoEmail_'.$orsi_id] != '1') )
						{
							$Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$itemLine['orsi_or_id']}");

							// fire off an email...
							$emailData = array(
								'first_name'	=>	$Order['or_purchaser_firstname'],
								'Box'	=>	$prod['pr_name'],
								'Price'	=>	$Price,
								'OrderID'	=>	$Order['or_tr_id'],
							);

							$emailText = $this->processTemplate('../../Templates/AcmeOutOfStockEmail',$emailData);
							if (file_exists(expandPath("Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".'ShopSystemAsset/sty_invoice.css')))
								$stylesheet = "Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".'ShopSystemAsset/sty_invoice.css';
							else
								$stylesheet = 'System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css';

							$emailResult = new Request('Email.Send',array(
								'from'	=>	$GLOBALS['cfg']['EmailAddress'],
									'to'	=>	$Order['or_purchaser_email'],
//								'to'	=>	'vicky@admin.com',
								'subject'	=>	"Stock problem with order at {$GLOBALS['cfg']['website_name']}",
								'html'	=>	$emailText,
								'css'	=>	$stylesheet,
								'templateFolder'	=>	$Order['or_site_folder'],
							));
						}
						else
							ss_log_message( "Not sending no stock email" );
					}

					if( $found_value )
					{
						// it can go back in the order...
						$OrderDetailsSerialized = serialize($OrderDetails);
				
						// Update the order
						$Q_Update = query("
							UPDATE shopsystem_orders
							SET or_basket = '".escape($OrderDetailsSerialized)."'
							WHERE or_id = ".safe($itemLine['orsi_or_id'])."
						");
					}

					$pr_id = getField( "select pro_pr_id from shopsystem_product_extended_options where pro_stock_code = '{$itemLine['orsi_stock_code']}'" );
					ss_audit( 'update', 'Products', $pr_id, 'zero stock of '.$itemLine['orsi_stock_code'] );

					query( "Update shopsystem_product_extended_options set pro_stock_available = 0 where pro_stock_code = '".$itemLine['orsi_stock_code']."'" );
					ss_log_message( "Stock on '".$itemLine['orsi_stock_code']."' set to zero by packers" );

					$Q_Update = query("
						UPDATE shopsystem_orders
						SET or_out_of_stock = NOW()
						WHERE or_id = ".safe($itemLine['orsi_or_id'])."
					");

					// now look through all the items in this order to see if all are shipped, if so, mark the whole thing as shipped.
					//$sh = getRow( "select count(*) as count from shopsystem_order_sheets_items where orsi_or_id = {$itemLine['orsi_or_id']} and orsi_date_shipped IS NULL and orsi_no_stock IS NULL" );
					$sh1 = getRow( "select count(*) as count from shopsystem_order_sheets_items where orsi_or_id = {$itemLine['orsi_or_id']} and orsi_date_shipped IS NOT NULL" );
					$sh2 = getRow( "select count(*) as count from shopsystem_order_items where oi_or_id = {$itemLine['orsi_or_id']}" );
					if( $sh1['count'] == $sh2['count'] )
						$Q_Update = query("
							UPDATE shopsystem_orders
							SET or_shipped = NOW(),
								or_paid_not_shipped = NULL
							WHERE or_id = ".safe($itemLine['orsi_or_id'])."
						");
				}
			}
	}

	if( !$borked )
		locationRelative('index.php?act=shopsystem_order_sheets.ViewPacking&BackURL='.ss_URLEncodedFormat($this->ATTRIBUTES['BackURL']).'&ors_id='.$this->ATTRIBUTES['ors_id']);

?>
