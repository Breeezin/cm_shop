<?php
	checkProductVendors();

	// Check for products in the basket or not
	if( (count($_SESSION['Shop']['Basket']['Products']) == 0) && !( $_SESSION['User']['us_account_credit'] < 0 )) {
		locationRelative($assetPath."/Service/Basket");
	}	

	ss_log_message( session_id()." checking out" );

	if( array_key_exists( 'doneUpsell', $_SESSION ) )
		ss_log_message( "\$_SESSION['doneUpsell'] == ".$_SESSION['doneUpsell'] );
	else
		ss_log_message( "No doneUpsell element in _SESSION" );

//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_POST );

	if( array_key_exists( 'ShippingCountry', $_POST ) )
	{
		$sc = safe( $_POST['ShippingCountry']);

		$Cn = getRow( "select * from countries where cn_two_code = '$sc'" );
		// swapping vendors....
		if( $Cn )
		{
			if( $_SESSION['ForceCountry']['cn_sales_zones'] != $Cn['cn_sales_zones'] )
				$_SESSION['Shop']['Basket'] = array();

			$_SESSION['ForceCountry'] = $Cn;
//
//			foreach( $GLOBALS['cfg']['ChargeCurrency'] as $index=>$curr )
//				if( $curr['CurrencyCode'] == $_SESSION['ForceCountry']['cn_currency_code'] )
//					$_SESSION['DefaultCurrency'] = $index;
		}
		else
			ss_log_message( "ERROR: no country 2 code '$sc'" );
	}

	if( array_key_exists( 'Email', $_POST )
		&& array_key_exists( 'Password', $_POST ) )
	{
		$email = safe( $_POST['Email'] );
		$password = safe( $_POST['Password'] );

		if( $result = GetRow( "select * from users where us_email = '$email' and us_password = '$password'" ) )
		{
			if( $result['us_email'] == $email )
				ss_login( $result['us_id'] );
		}
		else
		{
			$_SESSION['User'] = array(
				'us_id'			=>	-1,
				'user_groups'	=>	array(0),
				'us_first_name'	=>	'Guest',
				'us_last_name'	=>	'User',
				'us_email'		=>	null,
			);
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
		'BackURL'	=>	$assetPath."/Service/Checkout/GetDetail/Yes",
		'NoHusk'	=>	1,
		'ShowKeepMeLoggedIn'	=>	0,
		'LoginType'	=>	"Shop",
	));
	
	ss_log_message( session_id()." logged in as ".ss_getUserID() );

	if (array_key_exists('ClearDetails', $this->ATTRIBUTES)) {	
		$logout = new Request('Security.Logout',array(
			'BackURL'	=>	$assetPath."/Service/Checkout",
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
	$userAdmin = new UsersAdministration(false,true);		//	isn't admin and yes hide password (optionally)
	
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

	// what a mess.

	$go = NULL;
	if( !array_key_exists( 'GatewayOption', $_POST ) )		// not choosing
	{
		ss_log_message( "no GatewayOption POST" );

		if( !array_key_exists( 'GatewayOption', $_SESSION ) )	// not chosen yet
			location( "/Shop_System/Service/Login" );			// make them
		else
		{
			ss_log_message( "GatewayOption SESSION" );
			$go = abs( (int)$_SESSION['GatewayOption'] );				// previous choice
			ss_log_message( "using gateway option $go" );
		}
	}
	else
	{
		ss_log_message( "GatewayOption POST" );

		if( abs( (int) $_POST['GatewayOption'] ) > 0 )		// chosen this, valid
		{
			$go = abs( (int) $_POST['GatewayOption'] );
			ss_log_message( "indicated wishes to use gateway option $go" );
			$_SESSION['GatewayOption'] = $go;
		}
		else
		{
			ss_log_message( "GatewayOption POST invalid" );
			$go = abs( (int)$_SESSION['GatewayOption'] );				// previous choice
			ss_log_message( "using gateway option $go" );
		}
	}


	$amount = $_SESSION['Shop']['Basket']['Total'];
	$pg = array();
	if( $go )
	{
//		ss_log_message( "gateway option = $go" );
		$pg['Gateway'] = GetRow( "select * from payment_gateways join payment_gateway_options on pg_id = po_pg_id 
			where po_active = true 
			and po_id = ".abs($go) );

		// backup..., gateway has been retired, try another...
		if( !$pg['Gateway'] )
			if( $row = GetRow( "select * from payment_gateways join payment_gateway_options on pg_id = po_pg_id where po_id = ".abs($go) ) )
				$pg['Gateway'] = GetRow( "select * from payment_gateways join payment_gateway_options on pg_id = po_pg_id 
												where po_active = true 
													and po_currency = '{$row['po_currency']}'
													and po_card_type = {$row['po_card_type']}
													and po_restrict_to_person = false
													order by po_preference limit 1" );
	}
	else
	{
		$user = ss_getUser();

		if( is_array($user) 
			&& array_key_exists( 'us_account_credit', $user ) &&  ( $user['us_account_credit'] > 0 )
			&& array_key_exists( 'us_credit_from_gateway_option', $user ) &&  strlen( $user['us_credit_from_gateway_option'] )
			)
		{
//			ss_log_message( "user has us_credit_from_gateway_option of ".$user['us_credit_from_gateway_option'] );
			if( !array_key_exists('GatewayOption', $_SESSION ) )
			{
				$_SESSION['GatewayOption'] = $user['us_credit_from_gateway_option'];
				$pg['Gateway'] = GetRow( "select * from payment_gateways join payment_gateway_options on pg_id = po_pg_id 
				where po_id = ".$user['us_credit_from_gateway_option'] );
					// po_active = true and 
			}
		}
		else
		{
			// what then?  default
			$pg['Gateway'] = GetRow( "select po_id from payment_gateway_options where po_card_type = 2 and po_active = 1 and po_site = ".getSiteID( )." order by po_preference, po_currency limit 1" );
		}
	}

	if( $pg['Gateway'] )
	{
		// make sure that all the products in the basket have pr_restrict_product_to_gateway either NULL or this chosen gateway.
		// fix up special prices, pr_restrict_special_to_gateway, at the same time.

		$gid = $pg['Gateway']['pg_id'];
		for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
		{
			$entry = $_SESSION['Shop']['Basket']['Products'][$index];

			ss_log_message( "checking product ".$entry['Product']['pr_id']." for gateway restrictions ($gid)" );

			if ($entry['Qty'] > 0)
			{
				if( array_key_exists( 'pr_restrict_product_to_gateway', $entry['Product'] ) 
				 && strlen( $entry['Product']['pr_restrict_product_to_gateway'] )
				 && ($entry['Product']['pr_restrict_product_to_gateway'] != $gid ) )
				{
					// remove this product from the basket
					ss_log_message( "Removing {$entry['Product']['pr_name']} from basket" );
					$_SESSION['Shop']['Basket']['Products'][$index]['Qty'] = 0;
				}
			}
		}

		// grab the currency from the gateway, refresh the basket
		$this->setCurrencyCountry( $pg['Gateway']['po_currency'], true );
		ss_log_message( "setting currency to ".$pg['Gateway']['po_currency']." updating basket now");

		$result = new Request('Asset.Display',array(
			'NoHusk'	=>	true,
			'as_id'	=>	$this->asset->getID(),
			'Service'	=>	'UpdateBasket',
			'Mode'		=>	'Refresh',
			'AsService'	=>	true,
			'Gateway'   =>  $gid,
			));

		$bd = array();
		foreach ( $userAdmin->fields as $name => $val )
			$bd[$name] = $val->value;

		$bd['us_0_50A4'] = $_SESSION['ForceCountry']['cn_id'];

		if( array_key_exists('User', $_SESSION )
			 and array_key_exists('us_account_credit', $_SESSION['User']) )
		{
			ss_log_message( "Account Credit ".$_SESSION['Shop']['Basket']['Discounts']['Account Credit'] );
			ss_log_message( "Total ". $_SESSION['Shop']['Basket']['Total']);
			$creditCurrency = 'EUR';
			if( $foo = getCurrencyEntry( $_SESSION['User']['us_credit_from_gateway_option'] ) )
				$creditCurrency = $foo['po_currency'];
//			if( $_SESSION['Shop']['Basket']['Discounts']['Account Credit'] > 0 )
//				$this->setCurrencyCountry( $creditCurrency, true );

			if( (-$_SESSION['Shop']['Basket']['Discounts']['Account Credit']) < $_SESSION['Shop']['Basket']['Total'] )
				$pg = getUserPaymentGateway( ss_getUserID(), $bd, $_SESSION['Shop']['Basket']['Total'] + $_SESSION['Shop']['Basket']['Discounts']['Account Credit'] );
		}
		else
			$pg = getUserPaymentGateway( ss_getUserID(), $bd, $_SESSION['Shop']['Basket']['Total'] );
	}
	else
	{
		ss_log_message( "gateway option missing" );
		echo "payment gateway option missing";
		die;
	}

	
	//ss_log_message_r($shipping);
	ss_log_message( session_id()." end of query_checkout" );
?>
