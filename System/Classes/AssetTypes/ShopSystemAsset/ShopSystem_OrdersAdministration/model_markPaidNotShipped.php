<?php 

	require_once( "model_autoInvoice.php" );

	$this->param('or_id');	
	$this->param('BackURL', '');
	$this->param('SendEmail', false);

	if( ss_adminCapability( ADMIN_ORDER_STATUS ) )
	{
		ss_audit( 'update', 'Orders', $this->ATTRIBUTES['or_id'], 'setting paid not shipped' );

		$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_id = {$this->ATTRIBUTES['or_id']}");

		// send email if done from the charge list
		if( $this->ATTRIBUTES['SendEmail'] )
		{
			$to = array($Q_Order['or_purchaser_email']);
			$user = getRow( "select * from users where us_id = {$Q_Order['or_us_id']}" );
			if( !in_array($user['us_email'], $to ) )
				$to[] = $user['us_email'];

			$allTags = array();	

			if( strlen( $Q_Order['or_reshipment'] ) )
			{
				ss_log_message( "Reshipment, skipping email" );
			}
			else
			{
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
				$emailText = $ShopCereal['AST_SHOPSYSTEM_CLIENT_CARDCHARGED_EMAIL'];

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
				ss_log_message( "Card Charged Email" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
			}
		}

		// we need to update the Availabilities in the or_basket, so they new show up
		if (ss_optionExists('Shop Auto Order'))
		{
			$OrderDetails = unserialize($Q_Order['or_basket']);

			foreach ($OrderDetails['Basket']['Products'] as $id => $entry) 
			{
				for ($qty=0; $qty < $entry['Qty']; $qty++) 
				{
					ss_paramKey($OrderDetails['Basket']['Products'][$id],'Availabilities',array());
					$OrderDetails['Basket']['Products'][$id]['Availabilities'][$qty] = 'buy';
				}
			}
		}

		$Q_Custom = getRow("SELECT * FROM user_groups WHERE ug_name LIKE 'Customers'");

		// check the customer has the 'Customers' user group 
		$Q_UserGroups = query("
				SELECT * FROM user_user_groups 
				WHERE uug_us_id = {$Q_Order['or_us_id']} AND uug_ug_id = {$Q_Custom['ug_id']}
				");

		//if the user doenst have the group, then add one
		if (!$Q_UserGroups->numRows()) 
		{
			$Q_UpdateGroup = query("
				INSERT INTO user_user_groups 
					(uug_us_id, uug_ug_id) 
				VALUES 
					({$Q_Order['or_us_id']},  {$Q_Custom['ug_id']})
			");
		}

		// now update the order record as paid.
		$Q_UpdateOrder = query(" UPDATE shopsystem_orders 
					 SET 
						or_paid_not_shipped = Now(),
						or_paid = Now(),
						or_charge_list = NULL,
						or_card_denied = NULL, 
						or_cancelled = NULL,
						or_standby = NULL,
						or_shipped = NULL
						WHERE or_id = {$this->ATTRIBUTES['or_id']}
					 ");

		$previousStatus = getField( "select tr_completed from transactions where tr_id = {$Q_Order['or_tr_id']}" );

		query("UPDATE transactions set tr_completed = 1 WHERE tr_id = {$Q_Order['or_tr_id']} AND tr_completed = 0");	
		$Q_Transaction = getRow("SELECT * FROM transactions WHERE tr_id = {$Q_Order['or_tr_id']} AND tr_completed = 1");	


	//	ss_DumpVarDie( $Q_Transaction );
	//	if ($Q_Transaction['tr_payment_method'] == 'WebPay_CreditCard_Manual' 
	//	 or $Q_Transaction['tr_payment_method'] == 'Cheque' 
	//	 or $Q_Transaction['tr_payment_method'] == 'Direct') 
		if( 1 )
		{
			if (is_array($Q_Order['or_details']))
			{
				$basket = $Q_Order['or_details'];
			}
			else
			{			
				$basket = unserialize($Q_Order['or_details']);
			}
			//ss_DumpVarDie($basket['OrderProducts']['Products']);
			// add order products into the db.

			foreach($basket['OrderProducts'] as $aProduct)
			{
				$name = escape("{$aProduct['Product']['pr_name']} ({$aProduct['Product']['Options']})");
				$price = $aProduct['Qty'] * $aProduct['Product']['Price'];
				//$price = escape($this->formatPrice('display', $price));

				if( !$previousStatus )
				{
					// Update the product's stock availability since this product
					// option has been sold.
					$ProductOption = getRow("
						SELECT * FROM shopsystem_product_extended_options
						WHERE pro_stock_code LIKE '{$aProduct['Product']['pro_stock_code']}'
					");
					if ($ProductOption['pro_stock_available'] !== null) {
						// If the product option is using the stock level management..
						$Q_UpdateProductOption = query("
							UPDATE shopsystem_product_extended_options
							SET pro_stock_available = ".($ProductOption['pro_stock_available']-$aProduct['Qty'])."
							WHERE pro_id = {$ProductOption['pro_id']}
						");

						ss_audit( 'update', 'Products', $aProduct['Product']['pr_id'], "Order {$Q_Order['or_tr_id']} MarkPaidNotShipped, stock less ".$aProduct['Qty'] );
					}
				}

				$Q_Insert = query("
						INSERT INTO shopsystem_order_products 
							(orpr_or_id, orpr_pr_id, orpr_pr_name, orpr_price,
							orpr_qty, orpr_timestamp, orpr_site_folder) 
						VALUES
							({$Q_Order['or_id']}, {$aProduct['Product']['pr_id']}, '$name', '$price',
							{$aProduct['Qty']}, Now(), '{$Q_Order['or_site_folder']}')		
						");
			}

			if (ss_optionExists('Shop Auto Order'))
				doOrderSheetSync( $Q_Order['or_id'], $basket['OrderProducts'] );
		}

		if (ss_optionExists('Shop Auto Order'))
		{
			// Serialize back into the order
			$OrderDetailsSerialized = serialize($OrderDetails);

			$Q_UpdateOrder = query("UPDATE shopsystem_orders 
						SET
							or_paid_not_shipped = Now(),
							or_paid = Now(),
							or_shipped = NULL,
							or_card_denied = NULL,
							or_cancelled = NULL,
							or_standby = NULL,
							or_basket = '".escape($OrderDetailsSerialized)."'
						WHERE or_id = {$this->ATTRIBUTES['or_id']}
						");
		}
		else
		{
			$Q_UpdateOrder = query("UPDATE shopsystem_orders 
						SET
							or_paid_not_shipped = Now(),
							or_paid = Now(),
							or_shipped = NULL,
							or_card_denied = NULL,
							or_cancelled = NULL,
							or_standby = NULL
						WHERE or_id = {$this->ATTRIBUTES['or_id']}
						");
		}

		if (ss_optionExists('Shop Acme Rockets')) 
		{
			require("inc_addPoints.php");	
			$result = new Request('ShopSystem.AcmeCalculateOrderProfit',array('or_id'=>$this->ATTRIBUTES['or_id']));
		}

	/*
		if (ss_optionExists('Shop Keep Credit Card Details') === false) 
		{
			$res = new Request("WebPay.MarkPaid",array( 'tr_id'	=>	$this->ATTRIBUTES['tr_id'],));
		}
	*/

		if (ss_optionExists('Shop Auto Invoice'))
			autoInvoice( $this->ATTRIBUTES['or_id'] );

	}

	//echo($this->ATTRIBUTES['BackURL']);
	if( strlen( $this->ATTRIBUTES['BackURL'] ) )
		locationRelative($this->ATTRIBUTES['BackURL']);

?>
