<?php 
/* this file is mostly gutted now 20120207.  Function are moved to more appropriate places.
	Rex
*/
//	$local_tax = $_SESSION['ForceCountry'][ 'cn_tax_x100' ];

	$this->param("tr_id");
	$this->param("us_id");
	$this->param("tr_token");

	$us_id = (int) ($this->ATTRIBUTES['us_id']);
	$tr_id = (int) ($this->ATTRIBUTES['tr_id']);
	$tr_token = safe( $this->ATTRIBUTES['tr_token'] );

	$Q_Transaction = getRow("SELECT * FROM transactions WHERE tr_id = $tr_id AND tr_token LIKE '$tr_token' AND tr_completed = 1");

	if ($Q_Transaction['tr_id'] == $tr_id and $Q_Transaction['tr_status_link'] < 3 )
	{
		$emailText = "";
		$emailTextPriority = 0;
		$subject = "Order Receipt from {$GLOBALS['cfg']['website_name']}";
		
		// now update the order record as paid. eh????
		$Q_Order = getRow("SELECT * FROM shopsystem_orders join users on us_id = or_us_id WHERE or_tr_id = $tr_id");
		$fraudScore = $Q_Transaction['tr_fraud_score'];

		ss_log_message( "user {$Q_Order['or_us_id']} hit thankyou page" );

		ss_audit( 'other', 'users', ss_getUserID(), "User hit thankyou page" );

		$cancelled = $Q_Order['or_cancelled'];
		if( strlen( $cancelled ) )
			die;

		$basket = unserialize($Q_Order['or_details']);
		$real_basket = unserialize($Q_Order['or_basket']);

		// check if the customer used a points discount and if they're allowed to use it
		/*
		if (ss_optionExists('Shop Acme Rockets')) {
			if (strpos($Q_Order['or_details'],'Frequent Buyer Program Points Discount') !== false) {
				$canUsePoints = false;
				$usID = $Q_Order['or_us_id'];
				$CheckPoints = getRow("
					SELECT SUM(up_points) AS TotalPoints FROM shopsystem_user_points
					WHERE UsPouug_us_id = $usID
						AND up_used IS NULL
						AND up_expires > CURDATE()
				");		
				if ($CheckPoints['TotalPoints'] >= 4000) {
					$canUsePoints = true;	
				}		
				if ($canUsePoints) {
					// success - mark the points as used
					$Q_UsePoints = query("
						UPDATE shopsystem_user_points
						SET up_used = {$Q_Order['or_id']}
						WHERE UsPouug_us_id = $usID
							AND up_used IS NULL
					");		
						
				} else {
					// failed	
					$Q_FailTransaction = query("
						UPDATE transactions
						SET tr_completed = 0,
							tr_status_link = 3
						WHERE tr_id = {$this->ATTRIBUTES['tr_id']} AND tr_token LIKE '{$this->ATTRIBUTES['tr_token']}'
					");
					locationRelative("$assetPath/Service/ThankYou");
				}
			}
		}
		*/

		ss_paramKey($asset->cereal, $this->fieldPrefix.'CUSTOMER_USERGROUPS', array());				
		if (is_array($asset->cereal[$this->fieldPrefix.'CUSTOMER_USERGROUPS'])) {
			foreach ($asset->cereal[$this->fieldPrefix.'CUSTOMER_USERGROUPS'] as $aGroup) {
				$Q_UserGroups = query("
					SELECT * FROM user_user_groups 
					WHERE uug_us_id = {$Q_Order['or_us_id']} AND uug_ug_id = {$aGroup}
				");
				//if the user doenst have the group, then add one
				if (!$Q_UserGroups->numRows()) {
					$Q_UpdateGroup = query("
						INSERT INTO user_user_groups 
							(uug_us_id, uug_ug_id) 
						VALUES 
							({$Q_Order['or_us_id']},  {$aGroup})
					");
				}
			}
		}

		$orderRow = getRow("
			SELECT or_id AS ID, or_site_folder  FROM shopsystem_orders
			WHERE or_tr_id = $tr_id");

		ss_log_message( "user {$Q_Order['or_us_id']} Payment Method is ".$Q_Transaction['tr_payment_method'] );

		if ($Q_Transaction['tr_payment_method'] != 'WebPay_CreditCard_Manual' 
			and $Q_Transaction['tr_payment_method'] != 'Cheque'
			and $Q_Transaction['tr_payment_method'] != 'Direct'
			and $Q_Transaction['tr_payment_method'] != 'Invoice'
			and $Q_Transaction['tr_payment_method'] != 'Collection' )
		{
			die;
			/*
			$Q_UpdateOrder = query("
					UPDATE shopsystem_orders 
					SET 
						or_paid = Now()
					WHERE
						or_tr_id = $tr_id
						AND 
						or_us_id = {$Q_Order['or_us_id']}
			");	
			
		
			//ss_DumpVarDie($basket['OrderProducts']['Products']);
			// add order products into the db.
			
			foreach($basket['OrderProducts'] as $aProduct) {
				$name = escape("{$aProduct['Product']['pr_name']} ({$aProduct['Product']['Options']})");
				$price = $aProduct['Qty'] * $aProduct['Product']['Price'];
				$price = escape($this->formatPrice('display', $price));
			
				
				// Update the product's stock availability since this product
				// option has been sold.
				/*$ProductOption = getRow("
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
				}
			
				$Q_Insert = query("
						INSERT INTO shopsystem_order_products 
							(orpr_or_id, orpr_pr_id, orpr_pr_name, orpr_price, orpr_qty, orpr_timestamp, orpr_site_folder) 
						VALUES
							({$Q_Order['or_id']}, {$aProduct['Product']['pr_id']}, '$name', '$price', {$aProduct['Qty']}, Now(), '{$GLOBALS['cfg']['folder_name']}')		
				");
			}			
			
		*/
		}

		$gw = getRow( "select * from payment_gateways where pg_id = {$Q_Transaction['tr_bank']}" );

		ss_log_message( "user {$Q_Order['or_us_id']} gateway is ".$Q_Transaction['tr_bank'] );

		if( $gw['pg_script'] == 'bank_manual.php' )
		{
			// Update the product's stock availability since this product
			// option has been sold.
			// We do this always.. to prevent people over-ordering products, instead of
			// when the product has been paid for
			if( $gw['pg_reserve_stock'] )
			{
				foreach($basket['OrderProducts'] as $aProduct) 
				{
					$ProductOption = getRow("
						SELECT * FROM shopsystem_products, shopsystem_product_extended_options
						WHERE pro_pr_id = pr_id and pro_pr_id = '{$aProduct['Product']['pr_id']}' ");

					if ($ProductOption['pro_stock_available'] !== null)
					{
						// If the product option is using the stock level management..
						$Q_UpdateProductOption = query("
							UPDATE shopsystem_product_extended_options
							SET pro_stock_available = ".($ProductOption['pro_stock_available']-$aProduct['Qty'])."
							WHERE pro_id = {$ProductOption['pro_id']}
						");

						ss_audit( 'update', 'Products', $aProduct['Product']['pr_id'], "Order $tr_id reducing available stock by ".$aProduct['Qty'] );

						// Removed by Rex 20081123 at Patricks request
						if( false && ($ProductOption['pr_stock_warning_level'] !== null)
						 && ($ProductOption['pr_stock_warning_level'] <= $ProductOption['pro_stock_available']-$aProduct['Qty'] ) )
							{
							// send off an email to the stock order
							$result = new Request('Email.Send',array(
								'to'	=>	$asset->cereal[$this->fieldPrefix.'ADMINEMAIL'],
								'from'	=>	$asset->cereal[$this->fieldPrefix.'ADMINEMAIL'],
								'subject'	=>	"Product number ".$aProduct['Product']['pr_id']." ".$aProduct['Product']['pr_name']." has reached your minimum stock level",
								'html'	=>	''
							));
							}
					}
				}			
			}			

			// accumulate totals to this pg_id
			query( "update payment_gateways set pg_accumulation = pg_accumulation + {$Q_Transaction['tr_total']} where pg_id = {$Q_Transaction['tr_bank']}" );

			// calculate profit for the order immediately
			$res = new Request("ShopSystem.AcmeCalculateOrderProfit",array(
				'or_id'	=>	$Q_Order['or_id'],
			));	
		}

		
		/*
		<CFMAIL FROM="#ATTRIBUTES.AdminEmail#" TO="#Basket.Purchaser[Basket.Purchaser.Email].Display#"
		SUBJECT="Order Receipt" TYPE="HTML"><HTML>#StyleSheet#<BODY>#Email#</BODY></HTML></CFMAIL>
		*/
		
		// get user fields to have the field names
/*
		$fieldsArray = array();	 // user fields			
		$fieldNamesArray = array();	 			
		$Q_UserAsset = getRow("SELECT * FROM assets WHERE as_type LIKE 'users'");
		ss_paramKey($Q_UserAsset,'as_serialized',''); 
		
		if (strlen($Q_UserAsset['as_id']) AND strlen($Q_UserAsset['as_serialized'])) {
			$cereal = unserialize($Q_UserAsset['as_serialized']);			
			ss_paramKey($cereal,'AST_USER_FIELDS','');
			if (strlen($cereal['AST_USER_FIELDS'])) {
				$fieldsArray = unserialize($cereal['AST_USER_FIELDS']);
			} else {
				$fieldsArray = array();	
			}
		} else {
			$fieldsArray = array();	
		}		
		
		foreach($fieldsArray as $fieldDef) {	
			ss_paramKey($fieldDef, 'uuid','');
			ss_paramKey($fieldDef, 'name','');
			
			$fieldNamesArray[$fieldDef['uuid']] = $fieldDef['name'];			
		}

		*/


		if( $fraudScore < 100 )		// no exact match in blacklist
		{

			// now we need to check the order against a ruleset to determine order status and which email to fire off to them...

			$previousOrders = getRow( "select count(*) as count from shopsystem_orders
											JOIN transactions ON tr_id = or_tr_id 
										where or_us_id = {$Q_Order['or_us_id']}
											AND tr_completed = 1
											and (or_paid IS NOT NULL OR or_paid_not_shipped IS NOT NULL)" );

			ss_paramKey($asset->cereal, $this->fieldPrefix.'CLIENT_CREDITCARDEMAIL','');

			if( $emailTextPriority < 100 )
			{
				$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_CREDITCARDEMAIL'];
				$emailTextPriority = 100;
			}

			ss_log_message( "Order from ".$Q_Order['or_purchaser_email']." who has ".$previousOrders['count']." previous orders" );

			if( $previousOrders['count'] == 0 )		// no paid for orders yet
			{
				// new customer
				
				// Is order over Euro 400?

				if( $Q_Transaction['tr_total'] >= 400.0 )
				{
					// cancel this order
					query( "Update shopsystem_orders set or_cancelled = NOW(), or_profit = 0 where or_tr_id = $tr_id" );
					query( "Update transactions set tr_profit = 0 where tr_id = $tr_id" );

					// return stock TODO
					foreach($basket['OrderProducts'] as $aProduct)
					{
						$ProductOption = getRow("
							SELECT * FROM shopsystem_products, shopsystem_product_extended_options
							WHERE pro_pr_id = pr_id and pro_pr_id = '{$aProduct['Product']['pr_id']}'
						");
						if ($ProductOption['pro_stock_available'] !== null)
						{
							ss_audit( 'update', 'Products', $aProduct['Product']['pr_id'], "Order $tr_id returning stock of ".$aProduct['Qty'] );

							// If the product option is using the stock level management..
							$Q_UpdateProductOption = query("
								UPDATE shopsystem_product_extended_options
								SET pro_stock_available = ".($ProductOption['pro_stock_available']+$aProduct['Qty'])."
								WHERE pro_id = {$ProductOption['pro_id']}
							");
						}
					}

					if( $emailTextPriority < 300 )
					{
						$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_NEW_OVERSPEND_EMAIL'];
						$subject = "Your order at {$GLOBALS['cfg']['website_name']} was CANCELLED";

						$emailTextPriority = 300;
					}
					query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('Overspend for new customer', NOW(), {$orderRow['ID']} )" );

				}

				// check if tracking country by user TODO....
				/*
				if( array_key_exists( 'ForceCountry', $_SESSION ) && strlen( $_SESSION['ForceCountry'] ) )
				{
					ss_log_message( "No previous order for or_id ".$Q_Order['or_id'].", country is ".safe( $_SESSION['ForceCountry'] )." indicator is ".$_SESSION['ForceCountry'][ 'cn_shipping_tracking' ] );
					if( $_SESSION['ForceCountry'][ 'cn_shipping_tracking' ] == 'By User' )
					{
						ss_log_message( "Setting tracking as first order on country ".$_SESSION['ForceCountry'] );

						if( $Q_Transaction['tr_total'] >= 50.0 )
						{
							$noteStr .= "Tracking enabled as total > 50 (".$Q_Transaction['tr_total']." and country ".$_SESSION['ForceCountry']." has Tracking flag set to ".$_SESSION['ForceCountry'][ 'cn_shipping_tracking' ];
							$Q_UpdateOrder = query(" UPDATE shopsystem_orders
														SET or_tracked_and_traced = now()
														WHERE or_tr_id = {$this->ATTRIBUTES['tr_id']}");
						}
					}
				}
				*/

				$sdetails = unserialize($Q_Order['or_shipping_details']);
				
				ss_paramKey($sdetails['PurchaserDetails'],'0_50A1','');
				ss_paramKey($sdetails['ShippingDetails'],'0_50A1','');
				
				$billingName = escape(rtrim(ltrim($sdetails['PurchaserDetails']['Name'])));
				$billingAddress = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A1'])));
				
				$shippingName = escape(rtrim(ltrim($sdetails['ShippingDetails']['Name'])));
				$shippingAddress = escape(rtrim(ltrim($sdetails['ShippingDetails']['0_50A1'])));

				$billing_shipping_same = ( $billingAddress == $shippingAddress );

				// is shipping the same as billing?
				if( !$billing_shipping_same )
				{
					// put on standby
					query( "Update shopsystem_orders set or_standby = NOW() where or_tr_id = $tr_id" );
					if( $emailTextPriority < 150 )
					{
						$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_NEW_SHIPBILLDIFF_EMAIL'];
						$subject = "Your order at {$GLOBALS['cfg']['website_name']} needs your attention";

						$emailTextPriority = 150;
					}
					query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('Shipping billing different', NOW(), {$orderRow['ID']} )" );
				}
			}

/*
			if( $previousOrders['count'] <= 2 )
			{
				// how old is the last order (not cancelled or on standby)...
				$previousOrder = getRow( "select UNIX_TIMESTAMP(or_recorded) as last, or_id from shopsystem_orders
						JOIN transactions ON tr_id = or_tr_id 
						 where or_us_id = {$Q_Order['or_us_id']} 
						 	AND tr_completed = 1
							and or_card_denied IS NULL
						 	and or_tr_id != $tr_id
							and (or_cancelled IS NULL and or_standby IS NULL) 
							order by or_id desc limit 1" );

				ss_log_message( "previous order at time ".time(NULL)." vs ".$previousOrder['last'] );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $previousOrder );
				if( array_key_exists('last', $previousOrder)
					&& ($previousOrder['last'] > 0) 
					&& ( time(NULL) - $previousOrder['last'] < (21 * 7 / 5 * 24 * 60 * 60) ) )	// 21 working days?
				{
					ss_log_message( "too soon triggered" );

					$ns = getRow( "select count(*) as count from shopsystem_order_sheets_items where orsi_or_id = {$previousOrder['or_id']}" );
					$nr = getRow( "select count(*) as count from shopsystem_order_sheets_items where orsi_or_id = {$previousOrder['or_id']} and orsi_received IS NULL" );
					if( ( $ns['count'] == 0 ) || ( $nr['count'] > 0 ) )
					{
						ss_log_message( " and not mll arked as received" );
						query( "Update shopsystem_orders set or_standby = NOW() where or_tr_id = $tr_id" );
						if( $emailTextPriority < 210 )
						{
							$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_NEW_CLOSE_ORDER_EMAIL'];
							$subject = "Your order at {$GLOBALS['cfg']['website_name']} needs your attention";

							$emailTextPriority = 210;
						}
						query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('Ordered again too soon', NOW(), {$orderRow['ID']} )" );
					}
				}
			}


			if( $personHold )
			{
				ss_log_message( "person hold" );

				if( $emailTextPriority < 250 )
				{
					$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_NEW_CLOSE_ORDER_EMAIL'];
					$subject = "Your order at {$GLOBALS['cfg']['website_name']} needs your attention";

					$emailTextPriority = 250;
				}
				query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('Person automatically put on hold', NOW(), {$orderRow['ID']} )" );
			}
			*/

			// $_SESSION['Shop']['Basket']['Discounts']['Account Credit']
			if( array_key_exists( 'Discounts', $_SESSION['Shop']['Basket'] )
			 && is_array( $_SESSION['Shop']['Basket']['Discounts'] ) )
			{
				if( array_key_exists( 'Account Credit', $_SESSION['Shop']['Basket']['Discounts'] ) )
				{
					if( $Q_Transaction['tr_total'] <= 0 )	// all up
					{
/*						shifted 20171105 to model_checkout due to timing/http transport errors
						$Q_UpdateOrder = query("
								UPDATE shopsystem_orders 
								SET or_paid = Now(),
									or_paid_not_shipped = now()
								WHERE
									or_tr_id = $tr_id
									AND or_us_id = {$Q_Order['or_us_id']}
						");	
						doOrderSheetSync($Q_Order['or_id']);
*/
						// email off confirmation...
						
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
							if (array_key_exists($key, $fieldNamesArray)) 
								$allTags["S.".$fieldNamesArray[$key]] = $aValue; 				
							else 
								$allTags["S.".$key] = $aValue; 				
						}		
						
						if (!array_key_exists('first_name',$shippingDetails['PurchaserDetails'])) {
							$aValue = $shippingDetails['PurchaserDetails']['Name'];
							$shippingDetails['PurchaserDetails']['first_name'] = ListFirst($aValue,' ');
							$shippingDetails['PurchaserDetails']['last_name'] = ListLast($aValue,' ');
						}			
						foreach($shippingDetails['PurchaserDetails'] as $key => $aValue) {
							if (array_key_exists($key, $fieldNamesArray)) 
								$allTags["P.".$fieldNamesArray[$key]] = $aValue; 				
							else 
								$allTags["P.".$key] = $aValue; 					
						}
						
						$details = unserialize($Q_Order['or_details']);
						$allTags['OrderDetails'] = $details['BasketHTML'];
						$allTags['TotalCharge'] = $Q_Transaction['tr_charge_total'];
						$allTags['OrderNumber'] = ss_getTrasacationRef($Q_Transaction['tr_id']);
						
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

						if ($webpaySetting['UseInvoice']) {				
							$allTags['InvoiceNote'] = $webpaySetting['DirectSetting']['InvoiceNote'];			
						}
						if ($webpaySetting['UseCollection']) {				
							$allTags['CollectionNote'] = $webpaySetting['DirectSetting']['CollectionNote'];			
						}

						// replace all tags
						foreach($allTags as $tag => $value) {
							$emailText = stri_replace("[{$tag}]",$value,$emailText);
						}

						$styleSheet = 'System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css';
						if (file_exists(expandPath("Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}sty_shop.css")))
							$styleSheet = "Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}sty_shop.css";

						//$configContactDetails = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true);					
						//$emailText = "<html><head><STYLE type=\"text/css\">{$styleSheet}</STYLE></head><body>".$emailText."<p>$configContactDetails<p></body></html>";		


						// html manipulation from Email.Send
						$ExtraStyleSheets = '<link rel="stylesheet" href="'.$styleSheet.'" type="text/css">';
						$data = array(
							'ExtraStyleSheets'	=>	$ExtraStyleSheets,
							'Content'	=>	$emailText,
						);

						//$htmlMessage = processTemplate("System/Classes/Tools/Email/Templates/Email.html",$data);
						$htmlMessage = processTemplate("Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}/Email/Email.html",$data);

						// So we dont want to embed the images? We'll hard link them to the
						// website then.... Just hope your newsletter recipients are always online eh...
						foreach (array('/<img[^>]* src="([^"]+)"[^>]*>/is','/background="([^"]+)"/is','/<link[^>]* href="([^"]+\.css)"[^>]*>/is','/<a[^>]* href="([^"]+)"[^>]*>/is') as $regex) {
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

						// new Html Mailer that actually works (tm).
						include_once( "System/Libraries/Rmail/Rmail.php" );
						$mailer = new Rmail();
						$mailer->setFrom($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']);
						$mailer->setSubject($subject);
						$mailer->setHTML($htmlMessage);				
						//$mailer->setSMTPParams("smtp.admin.com", 25);
						$mailer->setSMTPParams("localhost", 587);
						$result = $mailer->send(array($Q_Order['or_purchaser_email']), 'smtp');

						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );
					}
				}
			}

			if (is_array($_SESSION['Shop']['DiscountCode']))
			{
				query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('Discount code used is {$_SESSION['Shop']['DiscountCode']['di_code']}', NOW(), {$orderRow['ID']} )" );
				query( "update discounts set di_left = di_left - 1 where di_id = {$_SESSION['Shop']['DiscountCode']['di_id']}");
			}

			$_SESSION['Shop']['Basket'] = array();
			$_SESSION['DefaultCurrency'] = 'EUR';
			locationRelative("$assetPath/Service/ThankYou/Reference/{$Q_Transaction['tr_reference']}");;		
		}
		else		// just leave them hanging.
		{
			if( $fraudScore == 100 )
			{
				// cancel this order
				query( "Update shopsystem_orders set or_cancelled = NOW(), or_profit = 0 where or_tr_id = $tr_id" );
				query( "Update transactions set tr_profit = 0 where tr_id = $tr_id" );

				// return stock
				foreach($basket['OrderProducts'] as $aProduct)
				{
					$ProductOption = getRow("
						SELECT * FROM shopsystem_products, shopsystem_product_extended_options
						WHERE pro_pr_id = pr_id and pro_pr_id = '{$aProduct['Product']['pr_id']}'
					");
					if ($ProductOption['pro_stock_available'] !== null)
					{
						// If the product option is using the stock level management..
						ss_audit( 'update', 'Products', $aProduct['Product']['pr_id'], "Order $tr_id returning stock of ".$aProduct['Qty'] );

						$Q_UpdateProductOption = query("
							UPDATE shopsystem_product_extended_options
							SET pro_stock_available = ".($ProductOption['pro_stock_available']+$aProduct['Qty'])."
							WHERE pro_id = {$ProductOption['pro_id']}
						");
					}
				}
			}
			die;
		}
		
		/*$mailer = new htmlMimeMail();		
		$mailer->setFrom($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']);
		$mailer->setSubject("Order Receipt from {$GLOBALS['cfg']['website_name']}");				
		$mailer->setHTML($emailText);				
		$mailer->send(array($Q_Order['or_purchaser_email']));	*/
		
		// send email to shop admin
		/* or not
		$emaildata = array(
				'tr_reference'	=> ss_getTrasacationRef($Q_Transaction['tr_id']),
				'or_purchaser_firstname'	=> $Q_Order['or_purchaser_firstname'],
				'or_purchaser_lastname'	=> $Q_Order['or_purchaser_lastname'],
				'or_purchaser_email'	=> $Q_Order['or_purchaser_email'],
				'Order'	=> $Q_Order,
				'as_id'	=> 	$assetID,
				
		);
        $Q_User = false;
        if ($Q_Order['or_us_id']) {
            $Q_User = getRow("Select * from users where us_id =".safe($Q_Order['or_us_id']));  
        }
        $emaildata['User'] = $Q_User;

		require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');	
		$mailer = new htmlMimeMail();		
		$mailer->setFrom($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']);
		$mailer->setSubject("Order Received - {$GLOBALS['cfg']['website_name']}");		
		$textMessage = $this->processTemplate('Email_OrderReceived', $emaildata);
		$mailer->setHTML($textMessage);				
		
		$sendTo = array($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']);
		$mailer->send($sendTo);
		if (strlen($GLOBALS['cfg']['BCCAddress'])) {
			$sendTo = array(trim($GLOBALS['cfg']['BCCAddress']));
			$mailer->send($sendTo);
		}		
		
        
        $emailOthers = ss_optionExists('Shop Send Order Received Notification');
	    if ($emailOthers) {
 	        $sendTo = ListToArray($emailOthers,',');
			$mailer->send($sendTo);
	    }
		*/

		$_SESSION['Shop']['Basket'] = array();
		$_SESSION['DefaultCurrency'] = 'EUR';
		locationRelative("$assetPath/Service/ThankYou/Reference/{$Q_Transaction['tr_reference']}");
		
	}
	locationRelative("$assetPath/Service/ThankYou");
	
?>
