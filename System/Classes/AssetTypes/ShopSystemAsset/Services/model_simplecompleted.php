<?php 
	$this->param("tr_id");
	$this->param("us_id");
	$this->param("tr_token");
	
	$Q_Transaction = getRow("SELECT * FROM transactions WHERE tr_id = {$this->ATTRIBUTES['tr_id']} AND tr_token LIKE '{$this->ATTRIBUTES['tr_token']}' AND tr_completed = 1");
	
	if ($Q_Transaction['tr_id'] == $this->ATTRIBUTES['tr_id'] and $Q_Transaction['tr_status_link'] < 3 ) {
		
		
		
		// now update the order record as paid.
		$Q_Order = getRow("SELECT * FROM shopsystem_orders WHERE or_tr_id = {$this->ATTRIBUTES['tr_id']}");
			
		ss_paramKey($asset->cereal, $this->fieldPrefix.'CUSTOMER_USERGROUPS', array());				
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
		// check the customer has the 'Customers' user group 
		
		
		
//		$basket = unserialize(str_replace(chr(10),'',$Q_Order['or_details']));
		$basket = unserialize($Q_Order['or_details']);
		
		if ($Q_Transaction['tr_payment_method'] != 'WebPay_CreditCard_Manual' and $Q_Transaction['tr_payment_method'] != 'Cheque'and $Q_Transaction['tr_payment_method'] != 'Direct') {			
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
			}
		}			
		
		
		
//		require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');	
		
		/*
		<CFMAIL FROM="#ATTRIBUTES.AdminEmail#" TO="#Basket.Purchaser[Basket.Purchaser.Email].Display#"
		SUBJECT="Order Receipt" TYPE="HTML"><HTML>#StyleSheet#<BODY>#Email#</BODY></HTML></CFMAIL>
		*/
		
		// get user fields to have the field names
/*		$fieldsArray = array();	 // user fields			
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
			$aValue = $shippingDetails['ShippingDetails']['Name'];
			$shippingDetails['ShippingDetails']['first_name'] = ListFirst($aValue,' ');
			$shippingDetails['ShippingDetails']['last_name'] = ListLast($aValue,' ');
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
		$allTags['OrderNumber'] = $Q_Transaction['tr_id'];
		
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
		
		// get client email content
		$emailText = "";
		if ($Q_Transaction['tr_payment_method'] == 'Cheque') {
			ss_paramKey($asset->cereal, $this->fieldPrefix.'CLIENT_CHEQUEEMAIL','');
			$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_CHEQUEEMAIL'];
		} else if ($Q_Transaction['tr_payment_method'] == 'Direct') {
			ss_paramKey($asset->cereal, $this->fieldPrefix.'CLIENT_DIRECTEMAIL','');
			$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_DIRECTEMAIL'];
		} else {
			ss_paramKey($asset->cereal, $this->fieldPrefix.'CLIENT_CREDITCARDEMAIL','');
			$emailText = $asset->cereal[$this->fieldPrefix.'CLIENT_CREDITCARDEMAIL'];
		}
		
		// replace all tags
		foreach($allTags as $tag => $value) {
			$emailText = stri_replace("[{$tag}]",$value,$emailText);
		}
		if (file_exists(expandPath('Custom/ContentStore/Layouts/sty_shop.css'))) {
			$stylesheet = file_get_contents(expandPath('Custom/ContentStore/Layouts/sty_shop.css'));
		} else {
			$stylesheet = file_get_contents(expandPath('System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css'));
		}
		
		$configContactDetails = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true);					
		$emailText = "<html><head><STYLE type=\"text/css\">{$stylesheet}</STYLE></head><body>".$emailText."<p>$configContactDetails<p></body></html>";		
				
		$mailer = new htmlMimeMail();		
		$mailer->setFrom($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']);
		$mailer->setSubject("Order Receipt from {$GLOBALS['cfg']['website_name']}");				
		$mailer->setHTML($emailText);				
		$mailer->send(array($Q_Order['or_purchaser_email']));				
		
		// send email to shop admin
		$emaildata = array(
				'tr_reference'	=> $Q_Transaction['tr_id'],
				'or_purchaser_firstname'	=> $Q_Order['or_purchaser_firstname'],
				'or_purchaser_lastname'	=> $Q_Order['or_purchaser_lastname'],
				'or_purchaser_email'	=> $Q_Order['or_purchaser_email'],
				
		);
		$mailer = new htmlMimeMail();		
		$mailer->setFrom($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']);
		$mailer->setSubject("Order Received - {$GLOBALS['cfg']['website_name']}");		
		$textMessage = $this->processTemplate('Email_OrderReceived', $emaildata);
		$mailer->setHTML($textMessage);				
		$mailer->send(array($asset->cereal[$this->fieldPrefix.'ADMINEMAIL']));
		
		$_SESSION['Shop']['Basket'] = array();
		
		
		
		locationRelative("$assetPath/Service/ThankYou/Reference/{$Q_Transaction['tr_reference']}");*/

		$secureSite = ss_withTrailingSlash($GLOBALS['cfg']['secure_server']);
			
		$accessCode = '';
		if (array_key_exists('AccessCode', $_REQUEST))
			$accessCode = $_REQUEST['AccessCode'];
		else if (array_key_exists('AccessCode', $_SESSION)) 
			$accessCode = $_SESSION['AccessCode'];

		location($secureSite."index.php?act=ShopSystem.ViewOrder&AccessCode=$accessCode&or_id={$Q_Order['or_id']}&tr_id={$this->ATTRIBUTES['tr_id']}&as_id=".$asset->getID());	
		
		// location($secureSite."index.php?act=WebPay.{$this->ATTRIBUTES['PaymentOption']}&AccessCode=$accessCode&tr_id={$this->ATTRIBUTES['tr_id']}&tr_token={$this->ATTRIBUTES['tr_token']}&us_id=$usID&BackURL={$backURL}&Type=Shop&as_id={$assetID}");
		
		
	}
	
	die('Unexpected Error Occured');
	//locationRelative("$assetPath/Service/ThankYou");
	
?>
