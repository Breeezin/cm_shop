<?php 

	$this->param('or_id');	
	$this->param('BackURL', '');

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

		$property = $this->ATTRIBUTES['Property'];

		$notes = array();
		$notes[] = $_SESSION['User']['us_email'].' setting '.$this->ATTRIBUTES['Property'];

		ss_audit( 'update', 'Orders', $this->ATTRIBUTES['or_id'], 'setting '.$this->ATTRIBUTES['Property'] );

		switch($property)
		{
			case 'Cancelled':
			case 'Returned':

	/*
				$existing = getRow( "select * from shopsystem_stock_orders where sto_or_id = {$this->ATTRIBUTES['or_id']}" );
				if( strlen( $existing['sto_sos_id'] ) )
				{
					echo "<html>Unable to cancel/standby, order batched in Stock Sheet {$existing['sto_sos_id']}<br/>";
					echo "<a href='".$this->ATTRIBUTES['BackURL']."'>Back</a></html>";
					die;
				}
	*/

				$some_shipped = false;

				$Q_Order = getRow("SELECT * FROM shopsystem_orders join transactions on tr_id = or_tr_id WHERE or_id = {$this->ATTRIBUTES['or_id']}");
				$OrderDetails = unserialize($Q_Order['or_basket']);

				if( $property == 'Cancelled' )
					foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
					{
						$addback = 0;
						for ($qty=0; $qty < $entry['Qty']; $qty++) 
						{
							ss_paramKey($OrderDetails['Basket']['Products'][$id],'Availabilities',array());
							if( array_key_exists( $qty, $OrderDetails['Basket']['Products'][$id]['Availabilities'] )  )
							{
								if( $OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] != 'outofstock' )
									$addback++;
							}
							else
								$addback++;
							$OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] = 'undecided';
						}

						if( $addback > 0 )
						{
							if( $Q_Order['tr_completed'] > 0 )
							{
								ss_log_message( "Stock adjustment, add $addback to pr_id ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']." from order {$this->ATTRIBUTES['or_id']}");
								ss_audit( 'update', 'Products', $OrderDetails['Basket']['Products'][$id]['Product']['pr_id'], "Order {$Q_Order['tr_id']} $property stock + $addback" );

								$Q_stockback = query("
									UPDATE shopsystem_product_extended_options 
									set pro_stock_available = pro_stock_available + $addback
									where pro_pr_id = ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']);


								// need to make sure that anything allocated on supplier invoices is de-allocated
							}
						}
						else
							ss_log_message( "Stock adjustment, all out of stock on pr_id ".$OrderDetails['Basket']['Products'][$id]['Product']['pr_id']." from order {$this->ATTRIBUTES['or_id']}");
					}

				if( $property != 'Returned' )
				{
					foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
					{
						for ($qty=0; $qty < $entry['Qty']; $qty++) 
						{
							ss_paramKey($OrderDetails['Basket']['Products'][$id],'Shipped',array());
							if( array_key_exists( $qty, $OrderDetails['Basket']['Products'][$id]['Shipped']) 
							 && strlen( $OrderDetails['Basket']['Products'][$id]['Shipped'][$qty] ) )
								//$some_shipped = true;
								;
						}
					}

					if( $some_shipped )
					{
						echo "<html>Unable to cancel/standby, part of order already shipped<br/>";
						echo "<a href='".$this->ATTRIBUTES['BackURL']."'>Back</a></html>";
						die;
					}
				}
				else
					$property = 'Cancelled';

				$OrderDetailsSerialized = serialize($OrderDetails);

				$Q_UpdateOrder = query("UPDATE shopsystem_orders 
					SET or_basket = '".escape($OrderDetailsSerialized)."'
					WHERE or_id = {$this->ATTRIBUTES['or_id']}
					");

				// remove the Automatic Stock orders associated with this order
				query( "delete from shopsystem_stock_orders where sto_sos_id is NULL 
							and sto_or_id = {$this->ATTRIBUTES['or_id']}" );

				// fallthrough

			case 'CardDenied':

				if( $property == 'CardDenied' )
				{
					// send off email
					$Q_Order = getRow( "select * from shopsystem_orders where or_id = {$this->ATTRIBUTES['or_id']}" );
					$to = array($Q_Order['or_purchaser_email']);
					$user = getRow( "select * from users where us_id = {$Q_Order['or_us_id']}" );
					if( !in_array($user['us_email'], $to ) )
						$to[] = $user['us_email'];

					$allTags = array();	

					// get details from purchaser and shipping
					// put into the tag table with value
					$shippingDetails = unserialize($Q_Order['or_shipping_details']);
					if (!array_key_exists('first_name',$shippingDetails['ShippingDetails'])) {
						if (array_key_exists('Name',$shippingDetails['ShippingDetails'])) {
							$aValue = $shippingDetails['ShippingDetails']['Name'];
							$shippingDetails['ShippingDetails']['first_name'] = ListFirst($aValue,' ');
							$shippingDetails['ShippingDetails']['last_name'] = ListLast($aValue,' ');
						}
					}
					
					foreach($shippingDetails['ShippingDetails'] as $key => $aValue) {
						$allTags["S.".$key] = $aValue; 				
					}		
					
					if (!array_key_exists('first_name',$shippingDetails['PurchaserDetails'])) {
						$aValue = $shippingDetails['PurchaserDetails']['Name'];
						$shippingDetails['PurchaserDetails']['first_name'] = ListFirst($aValue,' ');
						$shippingDetails['PurchaserDetails']['last_name'] = ListLast($aValue,' ');
					}			
					foreach($shippingDetails['PurchaserDetails'] as $key => $aValue) {
						$allTags["P.".$key] = $aValue; 					
					}
					
					$details = unserialize($Q_Order['or_details']);
					$allTags['OrderDetails'] = $details['BasketHTML'];
					$allTags['OrderNumber'] = ss_getTrasacationRef($Q_Order['or_tr_id']);
					
					$webpaySetting = ss_getWebPaymentConfiguration();
					if ($webpaySetting['UseCheque']) {
						$allTags['PayableTo'] = $webpaySetting['ChequeSetting']['PayableTo'];			
						$allTags['Address'] = $webpaySetting['ChequeSetting']['ToAddress'];			
					}
					
					if ($webpaySetting['UseDirect']) {
						$allTags['AccountNumber'] = $webpaySetting['DirectSetting']['AccountNumber'];			
						$allTags['AccountName'] = $webpaySetting['DirectSetting']['AccountName'];			
						$allTags['AccountNote'] = $webpaySetting['DirectSetting']['AccountNote'];			
					}

					$asset = getRow("
						SELECT * FROM assets
						WHERE as_id = ".safe($Q_Order['or_as_id'])."
					");

					$ShopCereal = unserialize($asset['as_serialized']);
					$emailText = $ShopCereal['AST_SHOPSYSTEM_CLIENT_CARDDENIED_EMAIL'];

					// replace all tags
					foreach($allTags as $tag => $value) {
						$emailText = stri_replace("[{$tag}]",$value,$emailText);
					}
					$styleSheet = 'System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css';
					if (file_exists(expandPath("Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}sty_shop.css")))
						$styleSheet = "Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}sty_shop.css";

					//$configContactDetails = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true);					
					//$emailText = "<html><head><STYLE type=\"text/css\">{$stylesheet}</STYLE></head><body>".$emailText."<p>$configContactDetails<p></body></html>";		


					// html manipulation from Email.Send
					$ExtraStyleSheets = '<link rel="stylesheet" href="'.$styleSheet.'" type="text/css">';
					$data = array(
						'ExtraStyleSheets'	=>	$ExtraStyleSheets,
						'Content'	=>	$emailText,
					);

					//$htmlMessage = processTemplate("System/Classes/Tools/Email/Templates/Email.html",$data);
					$htmlMessage = processTemplate("Custom/ContentStore/Templates/acmerockets/Email/Email.html",$data);

					// So we dont want to embed the images? We'll hard link them to the
					// website then.... Just hope your newsletter recipients are always online eh...
					foreach (array('/<img[^>]* src="([^"]+)"[^>]*>/is','/background="([^"]+)"/is','/<link[^>]* href="([^"]+\.css)"[^>]*>/is','/<a[^>]* href="([^"]+)"[^>]*>/is') as $regex)
					{
						preg_match_all($regex,$htmlMessage,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
						for ($i=count($matches[0])-1; $i>=0; $i--) {
							// matches[0] : array(0=>'<imgsomestuffsrc="Images/imagename"somestuff>',1=>offset);
							// matches[1] : Images/imagename
							// matches[2] : imagename
				
							$imagePath = $matches[1][$i][0];
							if (substr($imagePath,0,5) != "http:" and substr($imagePath,0,6) != "https:" and substr($imagePath,0,7) != "mailto:" and substr($imagePath,0,4) != "ftp:") {
								$imagePath = $GLOBALS['cfg']['plaintext_server'] . $imagePath;
							}
							
							$htmlMessage = substr_replace($htmlMessage,$imagePath,$matches[1][$i][1],strlen($matches[1][$i][0]));	
						}
					}	

					include_once( "System/Libraries/Rmail/Rmail.php" );
					$mailer = new Rmail();
					$mailer->setFrom($ShopCereal['AST_SHOPSYSTEM_ADMINEMAIL']);
					$mailer->setSubject("Order number ".$Q_Order['or_tr_id']." at {$GLOBALS['cfg']['website_name']}");				
					$mailer->setHTML($htmlMessage);				
					$mailer->setSMTPParams("localhost", 587);
					//$mailer->setSMTPParams("smtp.admin.com", 25);
					$result = $mailer->send($to, 'smtp');				
					ss_log_message( "Card Denied Email" );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );

				}

				// fallthrough:
			case 'Actioned':			
				if (ss_optionExists('Shop Advanced Ordering'))
				{
					// Remove from charge list
					$Q_UpdateOrder = query("
						UPDATE shopsystem_orders 
						SET or_charge_list = NULL
						WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
						");

					query( "delete from shopsystem_order_items where oi_eos_id is NULL and oi_or_id = {$this->ATTRIBUTES['or_id']}" );
				}
				// fallthru

			case 'Standby':
				// check if on a packing list....
				//if( ($property == 'Standby') && GetField( "select count(orsi_ors_id) from shopsystem_order_sheets_items join shopsystem_order_sheets on ors_id = orsi_ors_id where orsi_or_id = ".safe($this->ATTRIBUTES['or_id']) ) > 0 )
				if( 0 )
				{
					// no point in doing this.
					echo "This order is on a packing list, there is no point putting it on standby";
					die;
				}
				
				// fallthru
			case 'Reshipment':
			case 'TrackedAndTraced':
				$Q_UpdateOrder = query("UPDATE shopsystem_orders 
							SET Or$property = Now() 
							WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
							");

				if (ss_optionExists('Shop Acme Rockets')) 
				{
					$result = new Request('ShopSystem.AcmeCalculateOrderProfit',
								array('or_id'=>$this->ATTRIBUTES['or_id'])
								  );
				}

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


		doOrderSheetSync( $this->ATTRIBUTES['or_id'] );
	}

	if( strlen( $this->ATTRIBUTES['BackURL'] ) )
		locationRelative($this->ATTRIBUTES['BackURL']);
?>
