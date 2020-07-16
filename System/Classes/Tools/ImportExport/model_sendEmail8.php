<?php 

	$debug = false;
	$smtp_relay_host = "localhost";
	$smtp_relay_port = 25;
	$smtp_relay_from = "webserver@acmerockets.com";
	$smtp_relay_to_array = array( "acme@admin.com" );
	if( !$debug )
		$smtp_relay_to_array[] = "llamas@fulfillmentflorida.com";
	$smtp_relay_subject = "Packing Details from Bjorck Bros";
	$smtp_relay_body = "See attached";
	require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');

	$mailer = new htmlMimeMail();
	$mailer->setSMTPParams($smtp_relay_host, $smtp_relay_port, 'acmerockets.com' );
	$mailer->setFrom($smtp_relay_from);
	$mailer->setSubject($smtp_relay_subject);
//	$mailer->setText( $smtp_relay_body );


//		for forcing the sending of old packing detail files.
	if( array_key_exists('date', $_GET ) && (int)$_GET['date'] > 20100101 && (int)$_GET['date'] < 20300101 )
	{
		$debug = true;
		$old_date = (int)$_GET['date'];
		$basename_csv = "".$old_date.".csv";
		$basename_pdf = "".$old_date.".pdf";
		$fullname_csv = "/tmp/$basename_csv";
		$fullname_pdf = "/tmp/$basename_pdf";
		$lines = 1;
	}
	else
	{
		$dbs = array( 
				"valuehumidors" => array( "host" => "localhost", "user" => "ckam", "pass" => "phtybt", 'opid' => array(), 'link' => NULL, 'type' => 'OsC' ),
				"pe" => array( "host" => "localhost", "user" => "uSd7sDf8", "pass" => "hFd8sdfU", 'opid' => array(), 'link' => NULL, 'type' => 'IM' )
				);

		require('fpdf.php');

		$lines = 0;

		class PDF extends FPDF
		{
			var $position_x;

			function CellNextLine( $font, $attr, $size, $align, $colour_r, $colour_g, $colour_b, $text )
			{
				$this->SetFont($font,$attr,$size);
				$this->SetTextColor( $colour_r, $colour_g, $colour_b );
				$w=$this->GetStringWidth($text)+6;
		//		ss_log_message ("x:".$this->position_x );
				$this->SetX( $this->position_x );
				$this->Cell($w,$size-2,$text,0,1,$align);
				//$this->Cell($w,$size-2,$text,1,1,$align);
			}

			function CellHeader( $pos_x, $pos_y, $font, $attr, $size, $align, $colour_r, $colour_g, $colour_b, $text )
			{
		//		ss_log_message ("x:".$x.", y:".$y );
				$this->position_x = $pos_x;
				$this->SetY($pos_y);
				$this->SetX($this->position_x );
				$this->CellNextLine( $font, $attr, $size, $align, $colour_r, $colour_g, $colour_b, $text );
			}

		}

		$pdf=new PDF( 'P','mm','Letter' );

		$pdf->SetLeftMargin(1);
		$pdf->SetRightMargin(1);
		$pdf->SetTopMargin(1);
		$pdf->SetAutoPageBreak( true, 1);


	//$pdf->AddPage();
	//$pdf->SetFont('Arial','B',16);
	//Move to 8 cm to the right
	//$pdf->Cell(80);
	//Centered text in a framed 20*10 mm cell and line break
	//$pdf->Cell(20,10,'Title',1,1,'C');

		$basename_csv = strftime( "%Y%m%d" ).".csv";
		$basename_pdf = strftime( "%Y%m%d" ).".pdf";
		$fullname_csv = "/tmp/$basename_csv";
		$fullname_pdf = "/tmp/$basename_pdf";
		$fd_csv = fopen( $fullname_csv, "w+" );
	/*	$fd_pdf = fopen( $fullname_pdf, "w+" );	*/
	/*	if( !$fd_csv || !$fd_pdf )	*/
		if( !$fd_csv )
		{
			echo "Unable to open temp file<br/>";
			die;
		}

		fwrite( $fd_csv, "Order ID,Name First,Name Last,Ship to Address,Ship to City,Ship to State,Ship to Country,Ship to Zip,Phone,Email Address\n" );
		$order_id = "Bananas";
		$link = array();
		foreach( $dbs as $db => $connect )
		{
			if( $dbs[$db]['link'] = mysqli_connect( $connect['host'], $connect['user'], $connect['pass'] ) )
			{
				if( mysqli_select_db( $dbs[$db]['link'], $db ) )
				{
					if( $dbs[$db]['type'] == 'OsC' )		// OsCommerce db with DPS payment plug-in
					{
						if( $res = mysqli_query( $dbs[$db]['link'], "select * from dps_pxpay p join orders o on o.orders_id = p.order_id
								join orders_products op on op.orders_id = p.order_id
								left join orders_total ot on ot.orders_id = o.orders_id and ot.class = 'ot_shipping'
								where p.response_text = 'APPROVED'
								  and op.batched = 0
								  order by orders_products_id", $dbs[$db]['link'] ) )
						{
							while ( $row = mysqli_fetch_array( $res ) )
							{
								$dbs[$db]['opid'][] = $row['orders_products_id'];
								$lines++;
								// another order line
								if( strcmp( $order_id, $db.'-'.$row['order_id'] ) )
								{
									$order_id = $db.'-'.$row['order_id'];
									$firstname = $row['delivery_name'];
									$surname = "";
									if( $pos = strpos( $firstname, " " ) )
									{
										$surname = substr( $firstname, $pos+1 );
										$firstname = substr( $firstname, 0, $pos );
									}
									fwrite( $fd_csv, "$order_id, \"$firstname\", \"$surname\", \"{$row['delivery_street_address']}\", \"{$row['delivery_city']}\", \"{$row['delivery_state']}\", \"{$row['delivery_country']}\", \"{$row['delivery_postcode']}\",  \"{$row['customers_telephone']}\", \"{$row['customers_email_address']}\"\n" );

									$pdf->AddPage();
									$pdf->CellHeader( 10, 10,  "Arial", "B", "16", "L", 0,0,0, "ORDER ".$order_id );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['title'] );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['delivery_name'] );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['delivery_street_address'] );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['delivery_suburb'].' '.$row['delivery_city'] );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['delivery_state'] );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['delivery_country'] );
									$pdf->CellNextLine( "Arial", "B", "12", "L", 0, 0, 0, $row['delivery_postcode'] );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, "" );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, "" );

								}

								if( $Q = query( "select pro_stock_code from shopsystem_product_extended_options where pro_pr_id = {$row['products_id']}" ))
								{
									if( $prod_row = $Q->fetchRow( ) )
									{
										$stock_code = $prod_row['pro_stock_code'];
										/*
										$pdf->AddPage();
										$pdf->CellHeader( 10, 10,  "Arial", "B", "16", "L", 0,0,0, "ORDER ".$order_id );
										$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, "" );
										$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['delivery_name'] );
										$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['delivery_street_address'] );
										$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['delivery_suburb'].' '.$row['delivery_city'] );
										$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['delivery_state'] );
										$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['delivery_country'] );
										$pdf->CellNextLine( "Arial", "B", "12", "L", 0, 0, 0, $row['delivery_postcode'] );
										$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, "" );
										$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, "" );
										*/
										$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['products_quantity']." ".$row['products_name'] );
										$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $stock_code );
										$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, "" );
									}
									else
									{
										echo "Unable to grab product info for {$row['products_id']}<br/>";
									}
								}
								else
								{
									echo "Unable to grab product info for {$row['products_id']}<br/>";
								}

								// per product order in PDF
								/*
								echo nl2br( print_r( $row, true ) );
									[dps_pxpay_id] => 8
									[txn_id] => 5784af46d1e927ac
									[txn_type] => Purchase
									[merchant_ref] => 8-20091106133822
									[order_id] => 6
									[success] => 1
									[response_text] => APPROVED
									[auth_code] => 043913
									[txn_ref] => 0000000602dac473
									[orders_id] => 6
									[customers_id] => 8
									[customers_name] => Eric Starr
									[customers_company] =>
									[customers_street_address] => 21560 SW Farmington Rd.
									[customers_suburb] =>
									[customers_city] => Beaverton
									[customers_postcode] => 97007
									[customers_state] => Oregon
									[customers_country] => United country_states
									[customers_telephone] => 503-476-7411
									[customers_email_address] => ejaystarr@yahoo.com
									[customers_address_format_id] => 2
									[delivery_name] => Eric Starr
									[delivery_company] =>
									[delivery_street_address] => 21560 SW Farmington Rd.
									[delivery_suburb] =>
									[delivery_city] => Beaverton
									[delivery_postcode] => 97007
									[delivery_state] => Oregon
									[delivery_country] => United country_states
									[delivery_address_format_id] => 2
									[billing_name] => Eric Starr
									[billing_company] =>
									[billing_street_address] => 21560 SW Farmington Rd.
									[billing_suburb] =>
									[billing_city] => Beaverton
									[billing_postcode] => 97007
									[billing_state] => Oregon
									[billing_country] => United country_states
									[billing_address_format_id] => 2
									[payment_method] => Secure Credit Card (via DPS PxPay)
									[cc_type] =>
									[cc_owner] =>
									[cc_number] =>
									[cc_expires] =>
									[last_modified] =>
									[date_purchased] => 2009-11-06 13:39:31
									[orders_status] => 1
									[orders_date_finished] =>
									[currency] => USD
									[currency_value] => 1.000000
									[orders_products_id] => 7
									[products_id] => 1440
									[products_model] =>
									[products_name] => The Tuscany - Light Burl finish
									[products_price] => 59.9900
									[final_price] => 59.9900
									[products_tax] => 0.0000
									[products_quantity] => 1
									[batched] => 0
								*/
							}
						}
						else
						{
							echo "Unable to select from $db<br/>\n";

						}
					}
					
					if( $dbs[$db]['type'] == 'IM' )		// this is an IM format database, manual charging
					{
						// we need to batch accessories....

						if( !array_key_exists( 'bypass', $_GET ) OR !((int)$_GET['bypass'] > 0 ) )
						{
							$vendor = 1;

							startTransaction();
							
							$newOrderID = newPrimaryKey('shopsystem_order_sheets','ors_id');
							
							// Make a new order sheet
							$Q_Insert = query("
								INSERT INTO shopsystem_order_sheets
									(ors_id, ors_date, ors_ve_id)
								VALUES
									($newOrderID, NOW(), ".$vendor.")
							");
							
							// Grab all the products that haven't been ordered yet
							$Q_StockOrders = query("
								SELECT * FROM shopsystem_order_items, shopsystem_orders
								WHERE oi_eos_id IS NULL
								  AND or_not_new = 2
								  AND oi_or_id = or_id
								  AND or_card_denied IS NULL and or_deleted = 0 and or_cancelled IS NULL
								  AND or_shipped IS NULL
								  AND or_standby IS NULL
								  AND oi_ve_id = ".$vendor."
							");

							$generalDiscount = null;
							
							$productsOrdered = array();
							
							// Loop thru all the products and add em to the order sheet
							$grandTotal = 0;
							while ($row = $Q_StockOrders->fetchRow()) {
								// Grab the product that the order is for
								$prod = getRow("
									SELECT * FROM shopsystem_product_extended_options, shopsystem_products
									WHERE pro_stock_code = '".$row['oi_stock_code']."'
										AND pro_pr_id = pr_id
										AND pr_ve_id = ".$vendor."
								");
								
								// If the product doesn't exist any more we can't really order it...
								if ($prod !== null) {

									$Order = getRow("
										SELECT or_basket FROM shopsystem_orders
										WHERE or_id = {$row['oi_or_id']}
									");

									if ($Order !== null) {

										// Figure out prices etc and insert order sheet item
										
										if ($generalDiscount === null) {
											// Find out the discount that this shop uses
											$shop = getRow("
												SELECT as_serialized FROM assets
												WHERE as_id = {$prod['pr_as_id']}
											");
											$settings = unserialize($shop['as_serialized']);
					//						$generalDiscount = $settings['AST_SHOPSYSTEM_SUPPLIER_DISCOUNT'];
					//						if (!strlen($generalDiscount)) {
												$generalDiscount = 0;	
					//						}
										}

										$discount = 0;
										$price = 0;
										if (strlen($prod['pro_supplier_price'])) {
											$price = $prod['pro_supplier_price'];
										}
										if (strlen($prod['pro_supplier_disount'])) {
											$discount = ss_decimalFormat($prod['pro_supplier_disount']*$price/100);
										} else {
											$discount = ss_decimalFormat($generalDiscount*$price/100);
										}
										//$total = ($price-$discount)*$row['oi_qty'];
										$total = ($price-$discount);
										
										$productsOrdered[$row['oi_stock_code']] = array(
											//'qty'	=>	$row['oi_qty'],
											'qty'	=>	1,
											'price'	=>	$price-$discount,
										);
										
										$grandTotal += $total;
										$Q_Insert = query("
											INSERT INTO shopsystem_order_sheets_items
												(orsi_ors_id, orsi_stock_code, orsi_pr_name,
												orsi_box_number, orsi_price, orsi_discount, orsi_total, orsi_or_id)
											VALUES
												($newOrderID, '".escape($row['oi_stock_code'])."', '".escape($prod['pr_name'])."',
												{$row['oi_box_number']}, $price, $discount, $total, {$row['or_id']})
										");
										
										// Now update the order so that we know that the product has been ordered
										
										$Order = getRow("
											SELECT or_basket FROM shopsystem_orders
											WHERE or_id = {$row['oi_or_id']}
										");

										$OrderDetails = unserialize($Order['or_basket']);
										
										$box = $row['oi_box_number'];
//										$qtyToBuy = $row['oi_qty'];
										// Loop thru all the products in the order.. ugh -_-'
										foreach ($OrderDetails['Basket']['Products'] as $id => $entry)
											// Gotta have an availablility
											if (array_key_exists('Availabilities',$OrderDetails['Basket']['Products'][$id]))
												// If its the right product
												if ($entry['Product']['pro_stock_code'] == $prod['pro_stock_code'])
													if( array_key_exists( $box, $OrderDetails['Basket']['Products'][$id]['Availabilities'] ) )
														if( $OrderDetails['Basket']['Products'][$id]['Availabilities'][$box] == 'buy')
															$OrderDetails['Basket']['Products'][$id]['Availabilities'][$box] = 'ordered';
										
										// Serialize back into the order
										$OrderDetailsSerialized = serialize($OrderDetails);
										
										// Update the order
										$Q_Update = query("
											UPDATE shopsystem_orders
											SET or_basket = '".escape($OrderDetailsSerialized)."'
											WHERE or_id = {$row['oi_or_id']}
										");

										
										// Update the stock order with the supplier order sheet id
										$Q_UpdateStockOrders = query("
											UPDATE shopsystem_order_items
											SET oi_eos_id = $newOrderID
											WHERE oi_or_id = {$row['oi_or_id']}
												AND oi_stock_code LIKE '".escape($row['oi_stock_code'])."'
										");
									}
									
								}
							}
							
							// Add total to new order sheet
							$Q_Insert = query("
								UPDATE shopsystem_order_sheets
								SET ors_total = $grandTotal
								WHERE ors_id = $newOrderID
							");
							
							commit();
						}
						else
							$newOrderID = (int) $_GET['bypass'];

						// pass through with a batch number $newOrderID

						$Q_OrderSheetItems = query( "select * from shopsystem_order_sheets JOIN  shopsystem_order_sheets_items on orsi_ors_id = ors_id
								join shopsystem_orders on or_id = orsi_or_id
								where ors_id = $newOrderID order by orsi_or_id" );
						{
							while ( $row = $Q_OrderSheetItems->fetchRow() )
							{
								$dbs[$db]['opid'][] = $row['orsi_id'];
								$lines++;
								// another order line
								$shippingDetails = unserialize( $row['or_shipping_details'] );

								$state_country = $shippingDetails['ShippingDetails']['0_50A4'];
								$pos = strpos( $state_country, "<BR>" );
								if( $pos )
								{
									$state = substr( $state_country, 0, $pos );
									$country = substr( $state_country, $pos + 4 );
								}
								else
								{
									$state = $state_country;
									$country = $state_country;
								}

								if( strcmp( $order_id, $db.'-'.$row['or_tr_id'] ) )
								{
									$order_id = $db.'-'.$row['or_tr_id'];

									$firstname = $shippingDetails['ShippingDetails']['Name'];
									$surname = "";
									if( strlen( $shippingDetails['ShippingDetails']['0_B4BF'] ) )
										$surname = $shippingDetails['ShippingDetails']['0_B4BF'];
									else
										if( $pos = strpos( $firstname, " " ) )
										{
											$surname = substr( $firstname, $pos+1 );
											$firstname = substr( $firstname, 0, $pos );
										}

									$delivery_street_address = strip_tags($shippingDetails['ShippingDetails']['0_50A1']);
									$delivery_city = strip_tags($shippingDetails['ShippingDetails']['0_50A2']);
									$delivery_state = strip_tags($state);
									$delivery_country = strip_tags($country);
									$delivery_postcode = strip_tags($shippingDetails['ShippingDetails']['0_B4C0']);
									$customers_telephone = strip_tags($shippingDetails['ShippingDetails']['0_B4C1']);
									$customers_email_address = strip_tags($shippingDetails['ShippingDetails']['Email']);
									$delivery_name = $firstname." ".$surname;

									fwrite( $fd_csv, "$order_id, \"$firstname\", \"$surname\", \"$delivery_street_address\", \"$delivery_city\", \"$delivery_state\", \"$delivery_country\", \"$delivery_postcode\",  \"$customers_telephone\", \"$customers_email_address\"\n" );

									$pdf->AddPage();
									$pdf->CellHeader( 10, 10,  "Arial", "B", "16", "L", 0,0,0, "ORDER ".$order_id );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, "" );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $delivery_name );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $delivery_street_address );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $delivery_city );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $delivery_state );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $delivery_country );
									$pdf->CellNextLine( "Arial", "B", "12", "L", 0, 0, 0, $delivery_postcode );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, "" );
									$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, "" );


								}

								$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, "Box.#".($row['orsi_box_number']+1)." ".$row['orsi_pr_name'] );
								$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, $row['orsi_stock_code'] );
								$pdf->CellNextLine( "Arial", "", "12", "L", 0, 0, 0, "" );

							}
						}
					}
				}
			else
				{
					echo "Unable to connect to $db with supplied parameters<br/>\n";
				}
			}
			else
			{
				echo "Unable to connect to {$connect['host']} as {$connect['user']} with supplied parameters<br/>\n";
			}
		}

		$pdf->Output( $fullname_pdf, "F" );
		fclose( $fd_csv );
	}

	$mailer->addAttachment( $mailer->getFile($fullname_csv), $basename_csv );
	$mailer->addAttachment( $mailer->getFile($fullname_pdf), $basename_pdf );

	$sent = false;
	if( $lines > 0 )
	{
		// fire off the email.
		foreach ( $smtp_relay_to_array as $smtp_relay_to )
		{
			echo "Sending to $smtp_relay_to via $smtp_relay_host:$smtp_relay_port<br/>\n";
			//if( $mailer->send( array($smtp_relay_to), 'smtp' ) )
			if( $mailer->send( array($smtp_relay_to) ) )
				$sent = true;
			else
			{
				print_r( $mailer->errors );
				echo "<br/>\n";
			}
		}
	}
	else
	{
		if( !$mailer->send( array('acme@admin.com'), 'smtp' ) )
			print_r( $mailer->errors );
	}

	if( $sent && !$debug )
	{
		// mark as sent....
		foreach( $dbs as $db => $connect )
			foreach( $connect['opid'] as $opid )
			{
				if( $connect['type'] == 'IM' )
					$update = "update shopsystem_order_sheets_items set orsi_batched = now() where orsi_id = $opid";
				else
					$update = "update orders_products set batched = 1 where orders_products_id = $opid";

				if( !mysqli_query( $connect['link'], $update ) )
				{
					// fix this....
					echo "Nasty error, unable to update $opid as batch in $db<br/>\n";
					echo $update."<br/>\n";
				}
			}
	}

?>

