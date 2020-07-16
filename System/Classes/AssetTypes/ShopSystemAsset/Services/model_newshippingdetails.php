<?php

	if (array_key_exists("Do_Service", $this->ATTRIBUTES)) {

		$shipping->fieldSet->loadFieldValuesFromForm($this->ATTRIBUTES);
		// record our new shipping values into the session
		ss_paramKey($_SESSION['Shop'],'ShippingDetails',array());
		foreach($shipping->fieldSet->fields as $fieldName => $field) {
			$_SESSION['Shop']['ShippingDetails'][$fieldName] = $field->value;
		}

		$usID = ss_getUserID();
		
		$errors = array_merge($errors,$shipping->fieldSet->validate());	
		
		if (!count($errors)) {
			
			// store the shipping detail and purchaser details
			// because the purchaser details can be changed later.. 
			// so the order store the current values.												
			$shippingDetails = array();
			$shippingValues = array();
			
			foreach($shipping->fieldSet->fields as $field) {
				$shippingValues[$field->name] = $field->value;
				$fieldName = substr($field->name,4);				
				if ($fieldName == 'Name') {
					$shippingDetails[$fieldName] = $shipping->fieldSet->getFieldDisplayValue($field->name);
					$shippingDetails['first_name'] = $shipping->fieldSet->fields[$field->name]->displayFirstName($field->value);
					$shippingDetails['last_name'] = $shipping->fieldSet->fields[$field->name]->displayLastName($field->value);					
				} else {
					$shippingDetails[$fieldName] = $shipping->fieldSet->getFieldDisplayValue($field->name);			
				}
			}
			$countryID = (int)$shippingValues['ShDe0_50A4'];

			$shippingDetailsSerialized = escape(serialize(array('ShippingDetails' => $shippingDetails)));
			$shippingValuesSerialized = escape(serialize($shippingValues));
			
			$Q_InsertShippingDetails = query( "insert into user_addresses (ua_us_id, ua_shipping_details, ua_country) values ("
											." $usID, '$shippingDetailsSerialized', $countryID )" );

			location($backURL);
				
		}
	}

	// Load the shipping fields with our current values
	ss_paramKey($_SESSION['Shop'],'ShippingDetails',array());
	foreach($shipping->fieldSet->fields as $fieldName => $field) {
		if (array_key_exists($fieldName,$_SESSION['Shop']['ShippingDetails'])) {
			$shipping->fieldSet->fields[$fieldName]->value = $_SESSION['Shop']['ShippingDetails'][$fieldName];
		}
	}
	
?>
