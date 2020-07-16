<?php
	$this->param('Style','WithInputs');
	
	$asset->display->layout = 'nolink';
	
//	ss_RestrictPermission('CanAdministerAsset',$asset->getID());
	
	$errors = array();

	requireClass('FieldSet');
	requireClass('ShopSystem_ShippingDetails');
	$shipping = new ShopSystem_ShippingDetails();
	$shipping->defineFields($this);	
	
	// Dont let the user change the shipping country
	$shippingCountryFieldName = null;
	foreach($shipping->fieldSet->fields as $fieldName => $fieldDef) {
		if (get_class($fieldDef) == 'countryfield') {
			$shipping->fieldSet->fields[$fieldName]->value = $_SESSION['Shop']['TaxCountry']['cn_three_code'];
			$shipping->fieldSet->fields[$fieldName]->displayType = 'output';	
			$shippingCountryFieldName = $fieldName;
			break;
		}
	}	

	$_SESSION['Shop']['ShippingDetails'] = array();
?>
