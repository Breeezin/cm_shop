<?php
	
	/*
	print_r( $this );
	die;
    [ATTRIBUTES] => Array
        (
            [AccessCode] => 
            [HashMeIn] => 1_66569976efa7d17fd19e1d7f687fb445
            [tokenCheck] => 53f044923a3b39c0fdde8a3bc54ff42a
            [statsUser] => daf291c4fd8ae2f87de65c0201af8fb4
            [keepMeLoggedInCookie] => a:2:{s:6:"UserID";s:1:"1";s:4:"Auth";s:32:"8a21a889b3d5364224c14f46c1454876";}
            [PHPSESSID] => 000c75fa35ce76f9b783afd92add6399
            [REQUEST_URI] => /Shop_System/Service/EditOrder/Address/209562?AccessCode=&HashMeIn=1_66569976efa7d17fd19e1d7f687fb445
            [act] => Asset.Display
            [Service] => EditOrder
            [Address] => 209562
        )
	*/

	$this->param('Style','WithInputs');
	$this->param('Address','');
	$this->param('Basket','');

	$asset->display->layout = 'nolink';
	
	ss_RestrictPermission('CanAdministerAsset',$asset->getID());
	
	ss_paramKey($_SESSION['Shop'],'OrderingFor',-1);
	ss_paramKey($_SESSION['Shop'],'EditingOrder',null);
	
	$errors = array();
	//////////////////
	// Basket stuff //
	//////////////////
	
	$result = new Request("Security.Sudo",array('Action'=>'Start'));
	$allCategoriesResult = new Request("shopsystem_categories.QueryAll",array('as_id'	=>	$asset->getID()));
	$Q_Categories = $allCategoriesResult->value;
	$result = new Request("Security.Sudo",array('Action'=>'Finish'));	
	
	
	////////////////////
	// Checkout stuff //
	////////////////////
	
	requireOnceClass("UsersAdministration");	
	
	$userAdmin = new UsersAdministration(false,true);		//	isn't admin and yes hide password (optionally)

	// Check which fields should be force as required for customer details
	ss_paramKey($asset->cereal, $this->fieldPrefix.'REQUIREDFIELDS', array());	
	// add the "Us" prefix which is missing
	for ($i=0;$i<count($asset->cereal[$this->fieldPrefix.'REQUIREDFIELDS']);$i++) {
		$asset->cereal[$this->fieldPrefix.'REQUIREDFIELDS'][$i] = 'Us'.$asset->cereal[$this->fieldPrefix.'REQUIREDFIELDS'][$i];
	}
	// Force them as required
	//ss_DumpVar($asset->cereal[$this->fieldPrefix.'REQUIREDFIELDS'], '', true);
	$userAdmin->forceRequired($asset->cereal[$this->fieldPrefix.'REQUIREDFIELDS']);

	if (strlen($this->ATTRIBUTES['Address']))
	{
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

		$_SESSION['Shop']['Mode'] = 'Edit';
		$Order = getRow(" SELECT * FROM shopsystem_orders WHERE or_id = ".(int)($this->ATTRIBUTES['Address']));	

		if ($Order !== null)
		{
			$_SESSION['Shop']['EditingOrder'] = (int)$this->ATTRIBUTES['Address'];
			if( $Order['or_country'] > 0 )
				$_SESSION['ForceCountry'] = getRow( "select * from countries where cn_id = {$Order['or_country']}" );

			// load the shipping values into our fields
			$shippingValues = unserialize($Order['or_shipping_values']);
			$_SESSION['Shop']['ShippingDetails'] = $shippingValues;
/*			foreach ($shipping->fieldSet->fields as $fieldName => $fieldDef) {
				if (array_key_exists($fieldName,$shippingValues)) {
					$shipping->fieldSet->fields[$fieldName]->value = $shippingValues[$fieldName];
				}
			}*/
			
			// load the purchaser details
			$this->param("us_id",$Order['or_us_id']);
			$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
			$userAdmin->primaryKey = $this->ATTRIBUTES['us_id'];		
			$errors = array();
			$userAdmin->loadFieldValues($userAdmin->ATTRIBUTES,NULL,NULL,$errors);
			$_SESSION['Shop']['OrderingFor'] = $this->ATTRIBUTES['us_id'];
			ss_paramKey($_SESSION['Shop'],'PurchaserDetails',array());
			//if (ss_isItUs()) die('hmm..');
			foreach($userAdmin->fields as $fieldName => $field) {
				$_SESSION['Shop']['PurchaserDetails'][$fieldName] = $userAdmin->fields[$fieldName]->value;
			}
			
		}

		// Load the shipping fields with our current values
		ss_paramKey($_SESSION['Shop'],'ShippingDetails',array());
		foreach($shipping->fieldSet->fields as $fieldName => $field) {
			if (array_key_exists($fieldName,$_SESSION['Shop']['ShippingDetails'])) {
				$shipping->fieldSet->fields[$fieldName]->value = $_SESSION['Shop']['ShippingDetails'][$fieldName];
			}
		}
		
		// Load the purchaser fields with our current values
		ss_paramKey($_SESSION['Shop'],'PurchaserDetails',array());
		foreach($userAdmin->fields as $fieldName => $field) {
			if (array_key_exists($fieldName,$_SESSION['Shop']['PurchaserDetails'])) {
				$userAdmin->fields[$fieldName]->value = $_SESSION['Shop']['PurchaserDetails'][$fieldName];
			}
		}

	}
	
	if (strlen($this->ATTRIBUTES['Basket']))
	{
	
		$Q_Products = query("
			SELECT * FROM shopsystem_products INNER JOIN shopsystem_product_extended_options ON shopsystem_products.pr_id = shopsystem_product_extended_options.pro_pr_id
			WHERE ((pr_deleted IS NULL) OR (pr_deleted = 1))
				AND (pro_stock_available IS NULL OR pro_stock_available > 0)
			ORDER BY pr_name
		");

		$_SESSION['Shop']['Mode'] = 'Edit';
		$Order = getRow(" SELECT * FROM shopsystem_orders WHERE or_id = ".(int)($this->ATTRIBUTES['Basket']));	

		if ($Order !== null)
		{
			$_SESSION['Shop']['EditingOrder'] = (int)$this->ATTRIBUTES['Basket'];
			if( $Order['or_country'] > 0 )
				$_SESSION['ForceCountry'] = getRow( "select * from countries where cn_id = {$Order['or_country']}" );

			// load the contents of the basket
			$details = unserialize($Order['or_basket']);
			$_SESSION['Shop']['Basket'] = $details['Basket'];
			$currency = $this->getDisplayCurrency();
			foreach( $_SESSION['Shop']['Basket']['Products'] as $key=>$product )
				if( array_key_exists( 'pro_source_currency', $product['Product'] ) )
					if( $product['Product']['pro_source_currency'] != $this->getDisplayCurrency() )
						$_SESSION['Shop']['Basket']['Products'][$key]['Product']['Price'] = ss_roundMoney($_SESSION['Shop']['Basket']['Products'][$key]['Product']['Price'] * ss_getExchangeRate($product['Product']['pro_source_currency'],$currency['CurrencyCode']));

			// load the purchaser details
			$this->param("us_id",$Order['or_us_id']);
			$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
			$userAdmin->primaryKey = $this->ATTRIBUTES['us_id'];		
			$errors = array();
			$userAdmin->loadFieldValues($userAdmin->ATTRIBUTES,NULL,NULL,$errors);
			$_SESSION['Shop']['OrderingFor'] = $this->ATTRIBUTES['us_id'];
		}
	}
	else
		ss_log_message( "Missing basket in attributes" );
	
?>
