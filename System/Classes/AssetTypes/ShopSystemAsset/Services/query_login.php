<?php
	// Check for products in the basket or not
	if( ( count($_SESSION['Shop']['Basket']['Products']) == 0 ) && !( $_SESSION['User']['us_account_credit'] < 0 ) ) {
		locationRelative($assetPath."/Service/Basket");
	}	

	ss_log_message( session_id()." hitting login page" );

	if( array_key_exists( 'ShippingCountry', $_POST ) )
	{
		$sc = safe( $_POST['ShippingCountry']);

		$Cn = getRow( "select * from countries where cn_two_code = '$sc'" );
		// swapping vendors ?....
		if( $Cn )
		{
			if( ss_getUserID() > 0 )
				ss_audit( 'other', 'users', ss_getUserID(), "Chosen shipping country is ".$Cn['cn_name'] );

			if( $_SESSION['ForceCountry']['cn_sales_zones'] != $Cn['cn_sales_zones'] )
			{
				$_SESSION['Shop']['Basket'] = array();
				if( ss_getUserID() > 0 )
					ss_audit( 'other', 'users', ss_getUserID(), "Basket cleared as vendor string different" );
			}

			$_SESSION['ForceCountry'] = $Cn;
			ss_log_message( "Setting Country to {$Cn['cn_name']}" );

//			foreach( $GLOBALS['cfg']['ChargeCurrency'] as $index=>$curr )
//				if( $curr['CurrencyCode'] == $_SESSION['ForceCountry']['cn_currency_code'] )
//					$_SESSION['DefaultCurrency'] = $index;
		}
		else
			ss_log_message( "ERROR: no country 2 code '$sc'" );

		if( strlen( $_POST['GatewayOption'] ) )
		{
			$_SESSION['GatewayOption'] = abs( (int)$_POST['GatewayOption'] );
			ss_log_message( "Choosing Gateway {$_SESSION['GatewayOption']}" );

			if( ss_getUserID() > 0 )
			{
				$rw = GetRow( "select po_currency_name, cct_name from payment_gateway_options join credit_card_types on po_card_type = cct_id  where po_id = ".((int)$_SESSION['GatewayOption']) );
				ss_audit( 'other', 'users', ss_getUserID(), "Chosen gateway is ".$rw['po_currency_name']."/".$rw['cct_name'] );
			}

			if( $Cn && $_SESSION['GatewayOption'] > 0 )
				location( '/Shop_System/Service/Checkout/GetDetail/Yes' );
		}
	}

	// Check for minimum order limit
	$displayCurrency = $this->getEnterCurrency();
	if (array_key_exists('MinimumOrder',$displayCurrency)) {
		if ($_SESSION['Shop']['Basket']['SubTotal'] < $displayCurrency['MinimumOrder']) {
			locationRelative('');
		}
	}

	// check for upsell...

	if( array_key_exists( 'doneUpsell', $_SESSION ) )
		ss_log_message( "\$_SESSION['doneUpsell'] == ".$_SESSION['doneUpsell'] );
	else
		ss_log_message( "No doneUpsell element in _SESSION" );

	if( !array_key_exists( 'doneUpsell', $_SESSION )
	  ||  ($_SESSION['doneUpsell'] == 0 ) )
	{
		$vendorList = array();

		$_SESSION['doneUpsell'] = 1;
		for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
		{
			$entry = $_SESSION['Shop']['Basket']['Products'][$index];

			if ($entry['Qty'] > 0)
				if( array_key_exists( 'pr_ve_id', $entry['Product'] ) )
					if( !in_array( $entry['Product']['pr_ve_id'], $vendorList ) )
						$vendorList[] = $entry['Product']['pr_ve_id'];
		}

		if( count( $vendorList ) )
		{
			$vendors = implode(', ', $vendorList );
			ss_log_message( "checking for stock in vendors $vendors" );

			$r = getRow( "select sum(pro_stock_available) as count from shopsystem_products join shopsystem_product_extended_options on pro_pr_id = pr_id where pr_upsell > 0 and pr_deleted IS NULL and pr_ve_id in ($vendors)"  );
			if( $r['count'] > 0 )
			{
				// we have something to offer
				ss_log_message( session_id()." upsell items available, redirecting to offers page" );
				locationRelative($assetPath."/Service/Engine/Offers/Yes");
			}
			else
				ss_log_message( session_id()." no upsell stock available" );
		}
	}

	//added by Briar 13.10.05..
	//set up like 'Shop Minimum Order=Quantity:4,Cost:20'
	$hasOrderMinimum = ss_optionExists('Shop Minimum Order');

	if ($hasOrderMinimum !== false) {
	    $products = $_SESSION['Shop']['Basket']['Products'];
	    $count = count($products);
	    $quantity = 0;
	    for	($index=0;$index<$count;$index++) {
		$quantity += $products[$index]['Qty'];
	    }
	    $orderMinimums = array();
		    foreach(ListToArray($hasOrderMinimum) as $min) {
			    $orderMinimums[ListFirst($min,":")] = ListLast($min,":");
		    }

	    if (array_key_exists('Quantity',$orderMinimums)){
		if ($quantity < $orderMinimums['Quantity'])
		    locationRelative ($assetPath."/Service/Basket/MinQuantity/" . $orderMinimums['Quantity']);
	    }
	    if (array_key_exists('Cost',$orderMinimums)){
		if ($_SESSION['Shop']['Basket']['SubTotal'] < $orderMinimums['Cost'])
		    locationRelative ($assetPath."/Service/Basket/MinCost/" .$orderMinimums['Cost']);
	    }
	}

	$this->param('tr_id', '');
	$this->param('tr_token', '');
	$this->param('JoinNewsletter', '');
	$this->param('GiftMessage','');
		
	$errors = array();
	/*if (!strlen($this->ATTRIBUTES['tr_id'])) {				
		//$asset->cereal['AST_SHOPSYSTEM_DISPLAY_CURRENCY']
		//ss_paramKey($asset->cereal,'AST_SHOPSYSTEM_DISPLAY_CURRENCY',554);
			// asset->cereal['AST_SHOPSYSTEM_DISPLAY_CURRENCY']
		$displayCurrency = $this->getDisplayCurrency();
		$currency = getRow("SELECT * FROM countries WHERE cn_currency_code LIKE '".$displayCurrency['CurrencyCode']."'");
		$prepareTransaction = new Request("WebPay.PreparePayment", 
			array(	'tr_currency_link' => $currency['cn_id'], 
					'tr_client_name' => '',)
		);
		
		$this->ATTRIBUTES['tr_id'] = $prepareTransaction->value['tr_id'];
		$this->ATTRIBUTES['tr_token'] = $prepareTransaction->value['tr_token'];
	}*/
	
	// User isn't a member...	
	$login = new Request('Security.Login',array(
		'BackURL'	=>	$assetPath."/Service/Login/GetDetail/Yes",
		'NoHusk'	=>	1,
		'ShowKeepMeLoggedIn'	=>	0,
		'LoginType'	=>	"Shop",
	));
	
	ss_log_message( session_id()." logged in as ".ss_getUserID() );

	if (array_key_exists('ClearDetails', $this->ATTRIBUTES)) {	
		$logout = new Request('Security.Logout',array(
			'BackURL'	=>	$assetPath."/Service/Login",
			'NoHusk'	=>	1,			
		));
	}

	ss_paramKey($asset->cereal, $this->fieldPrefix.'NEWSLETTER_USERGROUPS', array());		
	ss_paramKey($asset->cereal, $this->fieldPrefix.'NEWSLETTER_QUESTION', '');	

	if( array_key_exists( 'Do_Service', $this->ATTRIBUTES ) && $this->ATTRIBUTES['Do_Service'] == 'Reload' )	// if they have selected a different shipping country....
	{
		if( array_key_exists( 'ShDe0_50A4_Parent', $_POST ) )
		{
			$newCountry = getRow( "select * from countries where cn_id = '".safe($this->ATTRIBUTES['ShDe0_50A4_Parent'])."'" );

			if( $_SESSION['ForceCountry']['cn_sales_zones'] != $newCountry['cn_sales_zones'] )
			{
				$_SESSION['Shop']['Basket'] = array();
				$_SESSION['ForceCountry'] = $newCountry;
				locationRelative($assetPath."/Service/Basket");
			}

			$_SESSION['ForceCountry'] = $newCountry;
			ss_log_message( "from reload, ForceCountry now ".$_SESSION['ForceCountry']['cn_id'] );
		}

		/* check products in basket for validity */
		$products = $_SESSION['Shop']['Basket']['Products'];
		$count = count($products);
		$altered = false;
		for	($index=0;$index<$count;$index++)
		{
			$numInBox = getField( "select pro_weight from shopsystem_product_extended_options where pro_pr_id = {$products[$index]['Product']['pr_id']}" );
			ss_log_message( "Weight on {$products[$index]['Product']['pr_name']} is $numInBox" );
			if( $_SESSION['ForceCountry'][ 'cn_generic_limit'] > 0 )
			{
				if( ($numInBox == 0) || ($_SESSION['ForceCountry']['cn_generic_limit'] < $numInBox) )
				{
					ss_log_message( "Removing {$products[$index]['Product']['pr_name']} from basket as limit is {$_SESSION['ForceCountry']['cn_generic_limit']}" );
					$altered = true;
					$_SESSION['Shop']['Basket']['Products'][$index]['Qty'] = 0;
				}
			}
		}
	}

	requireOnceClass("UsersAdministration");	
	
	$loggedIn = -1;
	//if (array_key_exists('GetDetail', $this->ATTRIBUTES))
	$loggedIn = ss_getUserID();
	
	// refresh the basket, recalc freight
	$argh = new Request('Asset.Display',array(
		'as_id'	=>	$asset->getID(),
		'Service'	=>	'UpdateBasket',
		'AsService'	=>	true,
		'Mode'		=>	'Refresh',
	));
	
	$userAdmin = new UsersAdministration(false,true);		//	isn't admin and yes hide password (optionally)

	//briar added 17.11.05
	//means not all user fields are pulled thru to the checkout
	if (ss_OptionExists('Shop Customer Select Fields')){
	    // Check which fields should be shown for customer details
	    ss_paramKey($asset->cereal, $this->fieldPrefix.'CUSTOMER_FIELDS', array());
	    // add the "Us" prefix which is missing
	    for ($i=0;$i<count($asset->cereal[$this->fieldPrefix.'CUSTOMER_FIELDS']);$i++) {
		    $asset->cereal[$this->fieldPrefix.'CUSTOMER_FIELDS'][$i] = 'Us'.$asset->cereal[$this->fieldPrefix.'CUSTOMER_FIELDS'][$i];
	    }
	    $newFieldsArray = array();
	    //only have the one's we've selected..
	    foreach ($userAdmin->fields as $userField) {
		foreach ($asset->cereal[$this->fieldPrefix.'CUSTOMER_FIELDS'] as $desiredField) {
		    if ($userField->name == $desiredField) {
			$newFieldsArray[$userField->name] = $userField;
				    }
			    }
		    }
	    $userAdmin->fields = $newFieldsArray;
	}

	// Check which fields should be force as required for customer details
	ss_paramKey($asset->cereal, $this->fieldPrefix.'REQUIREDFIELDS', array());	
	// add the "Us" prefix which is missing
	for ($i=0;$i<count($asset->cereal[$this->fieldPrefix.'REQUIREDFIELDS']);$i++) {
		$asset->cereal[$this->fieldPrefix.'REQUIREDFIELDS'][$i] = 'Us'.$asset->cereal[$this->fieldPrefix.'REQUIREDFIELDS'][$i];
	}
	// Force them as required
	//ss_DumpVar($asset->cereal[$this->fieldPrefix.'REQUIREDFIELDS'], '', true);
	if (is_array($asset->cereal[$this->fieldPrefix.'REQUIREDFIELDS'])) {
		$userAdmin->forceRequired($asset->cereal[$this->fieldPrefix.'REQUIREDFIELDS']);
	}
	
	requireClass('ShopSystem_ShippingDetails');
	$shipping = new ShopSystem_ShippingDetails();
	$shipping->defineFields($this);	

	
	// Dont let the user change the shipping country
	$shippingCountryFieldName = null;
	if (!ss_optionExists('Shop Checkout Unlock Shipping Country')) {
		foreach($shipping->fieldSet->fields as $fieldName => $fieldDef) {
			if (get_class($fieldDef) == 'countryfield') {
				$shipping->fieldSet->fields[$fieldName]->value = $_SESSION['Shop']['TaxCountry']['cn_three_code'];
				$shipping->fieldSet->fields[$fieldName]->displayType = 'output';	
				$shippingCountryFieldName = $fieldName;
				break;
			}
		}
	}
	
	//ss_log_message_r($shipping);
	ss_log_message( session_id()." end of query_login" );
?>
