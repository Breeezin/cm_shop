<?php		
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'PaymentForm',
		));

			
		$shippingFields = array();
		$fieldsArray = array();				
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
		ss_paramKey($shop->asset->cereal,'AST_SHOPSYSTEM_ADDRESSFIELDS', array());	
		ss_log_message_r( "user asset fields are ", $fieldsArray );
		ss_log_message_r( "AST_SHOPSYSTEM_ADDRESSFIELDS ", $shop->asset->cereal['AST_SHOPSYSTEM_ADDRESSFIELDS'] );
		ss_log_message_r( "asset is ", $shop->asset );

		// Check which fields should be force as required for customer details
		ss_paramKey($shop->asset->cereal, 'AST_SHOPSYSTEM_SHIPPING_REQUIREDFIELDS', array());	
		// add the "Us" prefix which is missing
		for ($i=0;$i<count($shop->asset->cereal['AST_SHOPSYSTEM_SHIPPING_REQUIREDFIELDS']);$i++) {
			$shop->asset->cereal['AST_SHOPSYSTEM_SHIPPING_REQUIREDFIELDS'][$i] = 'ShDe'.$shop->asset->cereal['AST_SHOPSYSTEM_SHIPPING_REQUIREDFIELDS'][$i];
		}
		
//		ss_log_message( "DEBUG" );
//		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $shop->asset );

		if (count($shop->asset->cereal['AST_SHOPSYSTEM_ADDRESSFIELDS'])) {
			foreach($fieldsArray as $fieldDef) {
				// Param all the settings we might have
				ss_paramKey($fieldDef,'uuid','');			
				$found = false;
				foreach ($shop->asset->cereal['AST_SHOPSYSTEM_ADDRESSFIELDS'] as $aField) {
					if ($aField == $fieldDef['uuid']) {
						// All shipping fields are required
						// $fieldDef['required'] = true;
						
						array_push($shippingFields, $fieldDef);		
						$found = true;
						break;								
					}
				}	
				if (!$found) {
					array_push($this->notSelectedFieldNames, 'us_'.$fieldDef['uuid']);
				}					
			}
			//ss_DumpVarDie($shop->asset->cereal['AST_SHOPSYSTEM_ADDRESSFIELDS']);
			$this->fieldSet->addCustomizedFields($shippingFields,'ShDe', 'Shipping ');		 		
		}
		
		ss_log_message_r( "shippingFields is now ", $this );

		// Force them as required
		if (is_array($shop->asset->cereal['AST_SHOPSYSTEM_SHIPPING_REQUIREDFIELDS'])) {
			$this->fieldSet->forceRequired($shop->asset->cereal['AST_SHOPSYSTEM_SHIPPING_REQUIREDFIELDS']);
		}

?>
