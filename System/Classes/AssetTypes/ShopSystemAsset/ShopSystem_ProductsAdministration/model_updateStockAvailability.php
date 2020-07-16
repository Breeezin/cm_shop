<?php
	$this->param('BackURL');
	$this->param('StockLevels');
	$this->param('Prices');

	$this->param('as_id');

	set_time_limit(0);

//	$alterations = "Stock Level Updates<br/>";

	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this->ATTRIBUTES );

	// Loop thru all the lines of stock levels
	foreach (ListToArray($this->ATTRIBUTES['Prices'],chr(10)) as $def) {

		startTransaction();
		
		// Stock Code[Tab]Price[Tab]PriceType[tab]Notes
		$stockCode = ListFirst($def,chr(9));	
		$price = ListGetAt($def,2,chr(9));
		$priceType = ListGetAt($def,3,chr(9));
		$notes = escape(ListLast($def,chr(9)));	

//		ss_log_message( "Stock price update: code '$stockCode' '$priceType' -> $price as '$notes'" );

		if ($price == 'NULL' or is_numeric($price))
		{
//			$alterations .= "{$priceType} = {$price} on Stock ID ".escape($stockCode)."<br/>";
			// Update :)
			$Q_UpdateStock = query("
				UPDATE shopsystem_product_extended_options
				SET {$priceType} = {$price}
				WHERE pro_stock_code = '".escape($stockCode)."'
			");

			$pr_row = getRow( "select * from shopsystem_product_extended_options where pro_stock_code = '".escape($stockCode)."'" );
			$pr_id = $pr_row['pro_pr_id'];
			$in_stock = $pr_row['pro_stock_available'];

			if( !strlen( $in_stock ) )
				$in_stock = 0;

			query( "insert into price_changes (pc_us_id, pc_pr_id, pc_field_name, pc_notes, pc_amount, pc_in_stock )"
						." values (".ss_getUserID().", $pr_id, '$priceType', '$notes', $price, $in_stock )" );

			ss_audit( 'update', 'Products', $pr_id, "setting $priceType to $price, available $in_stock" );

		}
		
		commit();
	}	

	$notificationTemplate = ss_getAssetCereal($this->ATTRIBUTES['as_id'],'AST_SHOPSYSTEM_STOCK_NOTIFICATION_EMAIL');
	$adminEmail = ss_getAssetCereal($this->ATTRIBUTES['as_id'],'AST_SHOPSYSTEM_ADMINEMAIL');
	
	// Loop thru all the lines of stock levels
	foreach (ListToArray($this->ATTRIBUTES['StockLevels'],chr(10)) as $def) {

		
		// Stock Code[Tab]Level
		$stockCode = ListFirst($def,chr(9));	
		$level = ListLast($def,chr(9));			
		
		// Check old value
		$old = getRow("
			SELECT pro_stock_available, pro_pr_id FROM shopsystem_product_extended_options
			WHERE pro_stock_code = '".escape($stockCode)."'
		");
		$oldStock = $old['pro_stock_available'];
		$pr_id = $old['pro_pr_id'];
		
		if ($level == 'NULL' or is_numeric($level)) {
			// Just make sure no floats slip thru 
			if ($level !== 'NULL') {
				$level = round($level);
			}
			
			/*
			print( "
				UPDATE shopsystem_product_extended_options
				SET pro_stock_available = {$level}
				WHERE pro_stock_code = '".escape($stockCode)."'
			");
			die;
			*/
			// Update :)
//			$alterations .= "Stock Level = {$level} on Stock ID ".escape($stockCode)."<br/>";
			$Q_UpdateStock = query("
				UPDATE shopsystem_product_extended_options
				SET pro_stock_available = {$level}
				WHERE pro_stock_code = '".escape($stockCode)."'
			");

			ss_audit( 'update', 'Products', $pr_id, 'setting available stock to '.$level );

			if (ss_optionExists('Shop Product Stock Notifications')) {
				// If we now have stock and before we didn't have stock, 
				// notify the customers
				if ($level > 0 and $oldStock !== NULL and $oldStock < 1)
				{
					// update the date that the product came back in stock
					$Q_UpdateStock = query("
						UPDATE shopsystem_product_extended_options
						SET pro_date_in_stock = Now()
						WHERE pro_stock_code = '".escape($stockCode)."'
					");
					
					$Q_Notifications = query("
						SELECT * FROM shopsystem_stock_notifications JOIN users ON shopsystem_stock_notifications.stn_us_id = users.us_id
						WHERE us_no_spam IS NULL and us_bl_id IS NULL and stn_stock_code = '".escape($stockCode)."' order by rand()
					");	
					
					$product = getRow("
						SELECT * FROM shopsystem_products, shopsystem_product_extended_options
						WHERE pro_stock_code = '".escape($stockCode)."'
							AND pro_pr_id = pr_id
					");
					
					@query( "insert into lastest_product_additions (la_pr_id, la_pr_sales_zone) values ({$product['pr_id']}, {$product['pr_sales_zone']})" );
					/*
					$price = $product['pro_price'];
					if ($product['pro_special_price'] !== null) {
						$price = $product['pro_special_price'];
					}
					
					if (ss_optionExists('Shop Acme Rockets')) {
						if ($product['PrExOpFreightCodeLink'] !== null) {
							// find out the freight charge
							$freight = getRow("
								SELECT Rate FROM ShopSystem_FreightRates
								WHERE {$product['PrExOpFreightCodeLink']} = FreightCodeLink
							");
									
							if ($freight !== null) {
								$price += $freight['Rate'];
							}					
						}
					}					
					$price = ss_HTMLEditFormat($price);
					*/
					$price = 'Visit our site!';
				
					
					
					$Q_BackStampCode = query("
						SELECT soit_date_changed, soit_bs_code
						FROM shopsystem_supplier_order_sheets_items, shopsystem_products, shopsystem_product_extended_options
						WHERE pr_id = pro_pr_id
							AND pr_id = {$product['pr_id']}
							AND pro_stock_code LIKE soit_stock_code
							AND soit_bs_code IS NOT NULL 
							AND soit_date_changed IS NOT NULL
						ORDER BY soit_date_changed DESC
						LIMIT 0,1					
					");
					
					if ($Q_BackStampCode->numRows() > 0) {
						$row = $Q_BackStampCode->fetchRow();	
						$boxCode = $row['soit_bs_code'].' and was received on '.date('j M Y',$row['soit_date_changed']);
					} else {
						$boxCode = 'unknown';
					}
					$boxCode = ss_HTMLEditFormat($boxCode);
									
					$productName = ss_HTMLEditFormat($product['pr_name']);
					
					$Q_Notifications->prefetch();
					
					if( !strlen($product['pr_offline'] ) )
					{
						$c = 0;
						while ( (++$c < $level*5 ) && ( $row = $Q_Notifications->fetchRow() ) )
						{
							// send email
							$emailText = $notificationTemplate;
							$emailText = stri_replace('[first_name]',$row['us_first_name'],$emailText);
							$emailText = stri_replace('[BoxCode]',$boxCode,$emailText);
							$emailText = stri_replace('[ProductName]',$productName,$emailText);
							$emailText = stri_replace('[Price]',$price,$emailText);

							$input = array(
								'to'	=>	$row['us_email'],
								'from'	=>	$adminEmail,
								'subject'	=>	"Product back in stock!",
								'html'	=>	$emailText,
								'templateFolder'	=>	$row['stn_site_folder'],
								'smtpPort' => 25,
							);
							
							
							$result = new Request('Email.Send',$input);	

							$Q_Delete = query("
								UPDATE shopsystem_stock_notifications
								SET stn_sent_email = 1
								WHERE stn_stock_code = '".escape($stockCode)."'
									AND stn_us_id = '".$row['stn_us_id']."'
									AND stn_site_folder = '".escape($row['stn_site_folder'])."'
							");
							
							/*$mailer = new htmlMimeMail();		
							$mailer->setFrom($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']);
							$mailer->setSubject("Order Received - {$GLOBALS['cfg']['website_name']}");		
							//$textMessage = $this->processTemplate('Email_OrderReceived', $emaildata);
							$mailer->setHTML('product is back in stock now');				
							$mailer->send(array($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']));*/

						}
					}
				}
			}
		}
	}


/*
	$result = new Request('Email.Send',array(
						'to'	=>	'acme@admin.com', 
						'from'	=>	'webserver@acmerockets.com',
						'subject'	=>	"Stock Updates",
						'html'	=>	$alterations,
					));
*/
	
	locationRelative($this->ATTRIBUTES['BackURL']);
	
?>
