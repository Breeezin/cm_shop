<?php
	

	$this->param('Style','WithInputs');
	$this->param('LoadOrder','');
	$this->param('NewOrder','');
	$this->param('ExistingClient','');
	$this->param('TransferOrder','');
	
	$asset->display->layout = 'nolink';
	
	ss_RestrictPermission('CanAdministerAsset',$asset->getID());
	
	ss_paramKey($_SESSION['Shop'],'OrderingFor',-1);
	ss_paramKey($_SESSION['Shop'],'EditingOrder',null);
	
	$errors = array();
	//////////////////
	// Basket stuff //
	//////////////////
	
	$Q_Products = query("
		SELECT * FROM shopsystem_products INNER JOIN shopsystem_product_extended_options ON shopsystem_products.pr_id = shopsystem_product_extended_options.pro_pr_id
		WHERE ((pr_deleted IS NULL) OR (pr_deleted = 1))
			AND (pro_stock_available IS NULL OR pro_stock_available > 0)
		ORDER BY pr_name
	");

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
	
	if (strlen($this->ATTRIBUTES['LoadOrder'])) {
		$_SESSION['Shop']['Mode'] = 'Edit';
		$Order = getRow("
			SELECT * FROM shopsystem_orders
			WHERE or_id = ".safe($this->ATTRIBUTES['LoadOrder'])."
		");	

		if ($Order !== null) {
			$Transaction = getRow("select * from transactions where tr_id = {$Order['or_tr_id']}" );
			$_SESSION['GatewayOption'] = $Transaction['tr_gateway_option'];
			if( strlen( $Transaction['tr_currency_link'] ) )
			{
				$cn  = getRow( "select * from countries where cn_id = {$Transaction['tr_currency_link']}" );

				if( strlen( $Order['or_country'] ) == 0 )
					$Order['or_country'] = 840;

/*
				if( $cn )
					foreach( $GLOBALS['cfg']['ChargeCurrency'] as $index=>$curr )
						if( $curr['CurrencyCode'] == $cn['cn_currency_code'] )
							$_SESSION['DefaultCurrency'] = $index;
*/
			}
		
			$_SESSION['Shop']['EditingOrder'] = $this->ATTRIBUTES['LoadOrder'];
			$_SESSION['ForceCountry'] = getRow( "select * from countries where cn_id = {$Order['or_country']}" );

			// load the contents of the basket
			$details = unserialize($Order['or_basket']);
			$_SESSION['Shop']['Basket'] = $details['Basket'];
			$currency = $this->getDisplayCurrency();
			ss_log_message( "orderforclient: displaycurrency" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $currency );
			/*
			foreach( $_SESSION['Shop']['Basket']['Products'] as $key=>$product )
				if( array_key_exists( 'pro_source_currency', $product['Product'] ) )
					if( $product['Product']['pro_source_currency'] != $this->getDisplayCurrency() )
						$_SESSION['Shop']['Basket']['Products'][$key]['Product']['Price'] = ss_roundMoney($_SESSION['Shop']['Basket']['Products'][$key]['Product']['Price'] * ss_getExchangeRate($product['Product']['pro_source_currency'],$currency['CurrencyCode']));
			*/

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
	} else if (strlen($this->ATTRIBUTES['NewOrder'])) {
		$_SESSION['Shop']['Mode'] = 'New';
		$_SESSION['Shop']['OrderingFor'] = -1;
		$_SESSION['Shop']['EditingOrder'] = null;
		$_SESSION['Shop']['Basket'] = array();
		$_SESSION['Shop']['ShippingDetails'] = array();
	} else if (strlen($this->ATTRIBUTES['ExistingClient'])) {
		$_SESSION['Shop']['Mode'] = 'New';
		$_SESSION['Shop']['OrderingFor'] = $this->ATTRIBUTES['ExistingClient'];
		$_SESSION['Shop']['EditingOrder'] = null;
		if( !strlen($this->ATTRIBUTES['TransferOrder']))
			$_SESSION['Shop']['Basket'] = array();
		$_SESSION['Shop']['ShippingDetails'] = array();
		$this->param("us_id",$this->ATTRIBUTES['ExistingClient']);
		$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
		$userAdmin->primaryKey = $this->ATTRIBUTES['us_id'];		
		$errors = array();
		$userAdmin->loadFieldValues($userAdmin->ATTRIBUTES,NULL,NULL,$errors);
		// load user details...
	}
	
	
?>
