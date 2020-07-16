<?php




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
		$shippingDetails = unserialize($Invoice['or_shipping_details']);
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
		
		$details = unserialize($Invoice['or_details']);
		$allTags['OrderDetails'] = $details['BasketHTML'];
		$allTags['TotalCharge'] = $Invoice['tr_charge_total'];
		$allTags['OrderNumber'] = ss_getTrasacationRef($Invoice['tr_id']);

	$data = $allTags;
	$data['Q_Invoice'] = $Q_Invoice;
	$data['Invoice'] = $Invoice;

	// $this->useTemplate('ExcelInvoice',$data);
	$this->useTemplate('AcmeInvoice',$data);

?>
