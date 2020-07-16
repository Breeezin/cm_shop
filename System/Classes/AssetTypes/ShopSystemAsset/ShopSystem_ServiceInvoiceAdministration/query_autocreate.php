<?php 

	// work out total profit since we last ran...

	$Q_sum = query( "select ds_company, sic_name, sic_email_address, sum( ds_amount) as weeksum from daily_skim, shopsystem_service_company where sic_id = ds_company and ds_timestamp > NOW() - INTERVAL 156 HOUR group by ds_company, sic_name, sic_email_address" );

	$row = GetRow( "Select count(*) as nr from shopsystem_service_invoice where siv_created_date > NOW() - INTERVAL 1 WEEK" );
	if( $row['nr'] > 0 )
	{
		echo "Already created invoices for this week<br/>";
		die;
	}


	while ($row = $Q_sum->fetchRow()) 
	{
		echo "Total invoice for {$row['sic_name']} is $".number_format($row['weeksum'], 2)."<br/>";
		query( "insert into shopsystem_service_invoice
			(sic_id, siv_to_sic_id, siv_created_date, siv_notes, siv_1_created_date,
				siv_1_text, siv_1_hours, siv_1_amount, siv_1_tax)
			values
			( {$row['ds_company']}, 3, NOW(), 'Created Automatically', NOW(),
				'Internet Services', 40, {$row['weeksum']}, 0 )");

	}


	die;






	if ($Q_Transaction['tr_id'] == $this->ATTRIBUTES['tr_id'] and $Q_Transaction['tr_status_link'] < 3 ) {
		
		// now update the order record as paid.
		$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_tr_id = {$this->ATTRIBUTES['tr_id']}");

		$basket = unserialize($Q_Order['or_details']);

		
		// check if the customer used a points discount and if they're allowed to use it
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
		// check the customer has the 'Customers' user group 
		
		
		
//		$basket = unserialize(str_replace(chr(10),'',$Q_Order['or_details']));
		
		if (ss_optionExists('Shop Acme Rockets') or ss_optionExists('Shop Combo Products')) {
			// we need to loop through the product and split out any combo products
			$newBasket = array();
			foreach ($basket['OrderProducts'] as $index => $aProduct) {
				if ($aProduct['Product']['pr_combo']) {
					// Need to find all the products in the combo and then add to the basket
					$Q_ComboProducts = query("
						SELECT * FROM shopsystem_combo_products, shopsystem_products, shopsystem_product_extended_options
						WHERE cpr_element_pr_id = {$aProduct['Product']['pr_id']}
							AND cpr_pr_id = pr_id
							AND pro_pr_id = pr_id
					");
					while ($cp = $Q_ComboProducts->fetchRow()) {
						// Grab each product and make a new entry in the basket
						$key = $cp['pr_id'].'_'.$cp['pro_id'];
						$found = false;
						foreach($newBasket as $nbIndex => $nbProduct) {
							if ($nbProduct['Key'] == $key) {
								$newBasket[$nbIndex]['Qty'] += $cp['cpr_qty']*$aProduct['Qty'];
								$found = true;
								break;	
							}	
						}
						if (!$found) {
							$product = getRow("
								SELECT pr_id,pr_short,pr_name,PrFreightTypeLink,pro_uuids,
									pro_stock_code,pro_price,pro_special_price,pro_member_price,
									PrExOpFreightValue,PrExOpFreightCodeLink,pr_ve_id
								FROM shopsystem_products,shopsystem_product_extended_options
								WHERE pr_id = ".safe($cp['pr_id'])."
									AND pr_id = pro_pr_id
									AND pro_id = ".safe($cp['pro_id'])."
							");
							if ($product !== null) {
								// Figure out the description of the options.. there arent any anyway.. :S
								$options = '';
								foreach(ListToArray($product['pro_uuids'],',') as $option) {
									$parent = ListFirst($option,'=');	
									$uuid = ListLast($option,'=');	
									$option = getRow("
										SELECT * FROM select_field_options
										WHERE sfo_uuid = '".escape($uuid)."'
									");
									if ($option !== null) {
										$options .= ss_comma($options,', ').$option['sfo_value'];
									}
								}
								$product['Options'] = $options;
								
								$newProduct = array(
									'Key'	=>	$key,
									'Qty'	=>	$cp['cpr_qty']*$aProduct['Qty'],
									'Product'	=>	$product,
								);
								array_push($newBasket,$newProduct);
							}
						}
					}
				} else {
					$found = false;
					foreach($newBasket as $nbIndex => $nbProduct) {
						if ($nbProduct['Key'] == $aProduct['Key']) {
							$newBasket[$nbIndex]['Qty'] += $aProduct['Qty'];
							$found = true;
							break;	
						}	
					}				
					if (!$found) {	
						array_push($newBasket,$aProduct);	
					}
				}
			}
			

			$newerBasket = array();
			
			for	($index=0;$index<count($newBasket);$index++) {
				$entry = $newBasket[$index];
				if ($entry['Qty'] > 0) {
					
					// Figure out the price to charge
					$price = $entry['Product']['pro_price'];
					if ($entry['Product']['pro_special_price'] !== null) $price = $entry['Product']['pro_special_price'];
					
					// add freight
					if ($entry['Product']['PrExOpFreightCodeLink'] !== null) {
						// find out the freight charge
						$freight = getRow("
							SELECT Rate FROM ShopSystem_FreightRates
							WHERE {$entry['Product']['PrExOpFreightCodeLink']} = FreightCodeLink
						");
								
						if ($freight !== null) {
							//$includedFreight += $freight['Rate'];
							$price += $freight['Rate'];
						}					
					}					
					
					
					// Round the amount
					if (ss_optionExists('Shop Round Prices')) {
						$price = ss_roundMoney($price);
					}
					
					$entry['Product']['Price'] = $price;
					
					array_push($newerBasket,$entry);
					//$total += $entry['Qty']*$entry['Product']['Price'];
					//$totalUnits += $entry['Qty'];
				};
			}
			
			
			
			// Update and return it back into the order
			$basket['OrderProducts'] = $newerBasket;
			$newOrDetails = escape(serialize($basket));
			
			$theBasket = unserialize($Q_Order['or_basket']);
			$theBasket['Basket']['Products'] = $newerBasket;
			$theBasket = escape(serialize($theBasket));

			$Q_UpdateOrder = query("
				UPDATE shopsystem_orders
				SET or_details = '{$newOrDetails}',
					or_basket = '{$theBasket}'
				WHERE or_tr_id = {$this->ATTRIBUTES['tr_id']}			
			");

			// find the order id.. -_-
			$orderRow = getRow("
				SELECT or_id AS ID, or_site_folder  FROM shopsystem_orders
				WHERE or_tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
			");
			
			$Q_Clean = query("
				DELETE FROM ShopSystem_AcmeOrderProducts WHERE OrderLink ={$orderRow['ID']}
			");
			
			// Record the order into separate fields...
			foreach($newerBasket as $product) {
				$Q_InsertOrderProduct = query("
					INSERT INTO ShopSystem_AcmeOrderProducts
						(OrderLink, ProductLink, StockCode, Quantity, Price, Name, SiteFolder)
					VALUES 
						({$orderRow['ID']}, {$product['Product']['pr_id']}, '".escape($product['Product']['pro_stock_code'])."',
						{$product['Qty']}, {$product['Product']['Price']}, '".escape($product['Product']['pr_name'])."',
						'".escape($orderRow['or_site_folder'])."'
					)
				");
			}
			
			
		}
		
		
		
		
		if ($Q_Transaction['tr_payment_method'] != 'WebPay_CreditCard_Manual' 
			and $Q_Transaction['tr_payment_method'] != 'Cheque'
			and $Q_Transaction['tr_payment_method'] != 'Direct'
			and $Q_Transaction['tr_payment_method'] != 'Invoice'
			and $Q_Transaction['tr_payment_method'] != 'Collection'
			) {			
			$Q_UpdateOrder = query("
					UPDATE shopsystem_orders 
					SET 
						or_paid = Now()
					WHERE
						or_tr_id = {$this->ATTRIBUTES['tr_id']}
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
				}*/
					
				$Q_Insert = query("
						INSERT INTO shopsystem_order_products 
							(orpr_or_id, orpr_pr_id, orpr_pr_name, orpr_price, orpr_qty, orpr_timestamp, orpr_site_folder) 
						VALUES
							({$Q_Order['or_id']}, {$aProduct['Product']['pr_id']}, '$name', '$price', {$aProduct['Qty']}, Now(), '{$GLOBALS['cfg']['folder_name']}')		
				");
			}			
			
		}

		// Update the product's stock availability since this product
		// option has been sold.
		// We do this always.. to prevent people over-ordering products, instead of
		// when the product has been paid for
		foreach($basket['OrderProducts'] as $aProduct) {
//			$ProductOption = getRow("
//				SELECT * FROM shopsystem_product_extended_options
//				WHERE pro_stock_code LIKE '{$aProduct['Product']['pro_stock_code']}'
//			");
			$ProductOption = getRow("
				SELECT * FROM shopsystem_products, shopsystem_product_extended_options
				WHERE pro_pr_id = pr_id and pro_pr_id = '{$aProduct['Product']['pr_id']}'
			");
			if ($ProductOption['pro_stock_available'] !== null) {
				// If the product option is using the stock level management..
				$Q_UpdateProductOption = query("
					UPDATE shopsystem_product_extended_options
					SET pro_stock_available = ".($ProductOption['pro_stock_available']-$aProduct['Qty'])."
					WHERE pro_id = {$ProductOption['pro_id']}
				");

				if( ($ProductOption['pr_stock_warning_level'] !== null)
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
		
		if (ss_optionExists('Shop Acme Rockets')) {
			
			// calculate profit for the order immediately
			$res = new Request("ShopSystem.AcmeCalculateOrderProfit",array(
				'or_id'	=>	$Q_Order['or_id'],
			));	
			//die('huh?');
		}
		
		
		/*
		<CFMAIL FROM="#ATTRIBUTES.AdminEmail#" TO="#Basket.Purchaser[Basket.Purchaser.Email].Display#"
		SUBJECT="Order Receipt" TYPE="HTML"><HTML>#StyleSheet#<BODY>#Email#</BODY></HTML></CFMAIL>
		*/
		
		// get user fields to have the field names
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
		/*
		if ($webpaySetting['UseInvoice']) {				
			$allTags['InvoiceNote'] = $webpaySetting['DirectSetting']['InvoiceNote'];			
		}
		if ($webpaySetting['UseCollection']) {				
			$allTags['CollectionNote'] = $webpaySetting['DirectSetting']['CollectionNote'];			
		}
		*/
		
		// get client email content
		$emailText = "";
		if ($Q_Transaction['tr_payment_method'] == 'Cheque') {
			ss_paramKey($asset->cereal, $this->fieldPrefix.'CLIENT_CHEQUEEMAIL','');
			$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_CHEQUEEMAIL'];
		} else if ($Q_Transaction['tr_payment_method'] == 'Direct') {
			ss_paramKey($asset->cereal, $this->fieldPrefix.'CLIENT_DIRECTEMAIL','');
			$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_DIRECTEMAIL'];
		} else if ($Q_Transaction['tr_payment_method'] == 'Invoice') {
			ss_paramKey($asset->cereal, $this->fieldPrefix.'CLIENT_INVOICEEMAIL','');
			$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_INVOICEEMAIL'];
		} else if ($Q_Transaction['tr_payment_method'] == 'Collection') {			
			ss_paramKey($asset->cereal, $this->fieldPrefix.'CLIENT_COLLECTIONEMAIL','');
			$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_COLLECTIONEMAIL'];
		} else {
			ss_paramKey($asset->cereal, $this->fieldPrefix.'CLIENT_CREDITCARDEMAIL','');
			$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_CREDITCARDEMAIL'];
		}
		
		// replace all tags
		foreach($allTags as $tag => $value) {
			$emailText = stri_replace("[{$tag}]",$value,$emailText);
		}
		if (file_exists(expandPath("Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}sty_shop.css"))) {
			$stylesheet = "Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}sty_shop.css";
		} else {
			$stylesheet = 'System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css';
		}
		
		//$configContactDetails = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true);					
		//$emailText = "<html><head><STYLE type=\"text/css\">{$stylesheet}</STYLE></head><body>".$emailText."<p>$configContactDetails<p></body></html>";		

		// send da email
		$result = new Request('Email.Send',array(
			'to'	=>	$Q_Order['or_purchaser_email'],
			'from'	=>	$asset->cereal[$this->fieldPrefix.'ADMINEMAIL'],
			'subject'	=>	"Order Receipt from {$GLOBALS['cfg']['website_name']}",
			'html'	=>	$emailText,
			'css'	=>	$stylesheet,
		));		
		
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
		locationRelative("$assetPath/Service/ThankYou/Reference/{$Q_Transaction['tr_reference']}");
		
	}
	locationRelative("$assetPath/Service/ThankYou");
	
?>
