<?php

	$errors = array();

	$close = false;

	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {

		// Validate the data for each field
		// Set up the error array
		//ss_DumpVarDie($this);
		/*if (array_key_exists($this->fieldSet->tablePrimaryKey,$this->ATTRIBUTES)) {
			$this->fieldSet->primaryKey = $this->ATTRIBUTES[$this->fieldSet->tablePrimaryKey];
		}
		
		// Validate each field and record any errors reported
		$errors = array_merge($errors,$this->fieldSet->validate());
		*/
		// Update if no errors validating data
		if (count($errors) == 0) {
		
			/*// Construct the SQL
			$insertFields = '';
			foreach ($this->fieldSet->fields as $field) {
				$insertFields .= $field->updateSQL();
			}
			
			// Update the fields
			$result = query("
				UPDATE {$this->fieldSet->tableName}
				SET $insertFields
				WHERE {$this->fieldSet->tablePrimaryKey} = {$this->fieldSet->primaryKey}
			");
	
			// Now handle the special fields.. e.g MultiSelectField
			foreach ($this->fieldSet->fields as $field) {
				$field->specialUpdate();
			}*/
			
	//		locationRelative($this->ATTRIBUTES['BackURL']);
			$close = true;
		}		
		
		if ($close) {
		
			// grab the order
			$Q_Order = getRow("
				SELECT * FROM shopsystem_orders
				WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
			");
			/*$Q_Transaction = getRow("
				SELECT * FROM transactions
				WHERE tr_id = ".safe($Q_Order['or_tr_id'])."
			");*/
			$asset = getRow("
				SELECT * FROM assets
				WHERE as_id = ".safe($Q_Order['or_as_id'])."
			");
			$ShopCereal = unserialize($asset['as_serialized']);
			//ss_DumpVar($ShopCereal);
			
			require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
			
			/*
			<CFMAIL FROM="#ATTRIBUTES.AdminEmail#" TO="#Basket.Purchaser[Basket.Purchaser.Email].Display#"
			SUBJECT="Order Receipt" TYPE="HTML"><HTML>#StyleSheet#<BODY>#Email#</BODY></HTML></CFMAIL>
			*/
			
			// get user fields to have the field names
/*			$fieldsArray = array();	 // user fields			
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
			*/
	/*		foreach($shippingDetails['ShippingDetails'] as $key => $aValue) {
				$allTags["S.".$fieldNamesArray[$key]] = $aValue; 	
				if ($key == 'Name') {
					$allTags["S.First".$fieldNamesArray[$key]] = ListFirst($aValue,chr(9)); 	
					$allTags["S.Last".$fieldNamesArray[$key]] = ListLast($aValue,chr(9)); 	
				}
			}		*/
			
			/*foreach($shippingDetails['PurchaserDetails'] as $key => $aValue) {
				$allTags["P.".$fieldNamesArray[$key]] = $aValue; 	
				if ($key == 'Name') {
					$allTags["P.First".$fieldNamesArray[$key]] = ListFirst($aValue,chr(9)); 	
					$allTags["P.Last".$fieldNamesArray[$key]] = ListLast($aValue,chr(9)); 	
				}
			}*/
			
/*			$details = unserialize($Q_Order['or_details']);
			$allTags['OrderNumber'] = $Q_Transaction['tr_id'];*/
			$allTags['SpecialNote'] = ss_HTMLEditFormatWithBreaks($this->ATTRIBUTES['SpecialNote']);
			/*$allTags['PickupDocketNumber'] = ss_HTMLEditFormat($Q_Order['OrDocketNumber']);
			$allTags['TransactionReference'] = $Q_Transaction['tr_reference'];
			
			$webpaySetting = ss_getWebPaymentConfiguration();
			if ($webpaySetting['UseCheque']) {
				$allTags['PayableTo'] = $webpaySetting['ChequeSetting']['PayableTo'];			
				$allTags['Address'] = $webpaySetting['ChequeSetting']['ToAddress'];			
			}*/
			
			
			// get client email content
			
			$emailText = $allTags['SpecialNote'];
		/*	ss_paramKey($ShopCereal, 'AST_SHOPSYSTEM_CLIENT_CONFIRMATION_EMAIL','');
			$emailText = $ShopCereal['AST_SHOPSYSTEM_CLIENT_CONFIRMATION_EMAIL'];
			// replace all tags
			*/
			/*foreach($allTags as $tag => $value) {
				$emailText = stri_replace("[{$tag}]",$value,$emailText);
			}*/
			if (file_exists(expandPath("Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".'ShopSystemAsset/sty_invoice.css'))) {
				$stylesheet = file_get_contents(expandPath("Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".'ShopSystemAsset/sty_invoice.css'));
			} else {
				$stylesheet = file_get_contents(expandPath('System/Classes/AssetTypes/ShopSystemAsset/ShopSystem_OrdersAdministration/Templates/sty_invoice.css'));
			}
			
			$configContactDetails = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true);
		
			
			$emailText = "<html><head><STYLE type=\"text/css\">{$stylesheet}</STYLE></head><body>".$emailText."<p>$configContactDetails<p></body></html>";		

			
			
			
			$mailer = new htmlMimeMail();		
			$mailer->setFrom($ShopCereal['AST_SHOPSYSTEM_ADMINEMAIL']);
			$mailer->setSubject("Re: Your order at {$GLOBALS['cfg']['website_name']}");				
			$mailer->setHTML($emailText);				
			$mailer->send(array($Q_Order['or_purchaser_email']));				
			
/*			ss_paramKey($ShopCereal,'AST_SHOPSYSTEM_SEND_CONFIRMATION_CC');
			if ($ShopCereal['AST_SHOPSYSTEM_SEND_CONFIRMATION_CC']) {
				$mailer = new htmlMimeMail();		
				$mailer->setFrom($ShopCereal['AST_SHOPSYSTEM_ADMINEMAIL']);
				$mailer->setSubject("Order at {$GLOBALS['cfg']['website_name']}");				
				$mailer->setHTML($emailText);				
				$mailer->send(array($ShopCereal['AST_SHOPSYSTEM_ADMINEMAIL']));
			}*/
			
			
			/*$q = query("
				UPDATE shopsystem_orders
				SET OrNotified = 1
				WHERE or_id = {$this->ATTRIBUTES['or_id']}
			");*/
			
			locationRelative($this->ATTRIBUTES['BackURL']);
		}
			
	}

?>