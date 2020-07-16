<?php
	global $cfg;

	$tracking_options = $_SESSION['ForceCountry'][ 'cn_shipping_tracking' ];
	$tracking_box_cost = $_SESSION['ForceCountry'][ 'cn_box_tracking_cost_x100' ];
	$countryWarning = $_SESSION['ForceCountry']['cn_note'];
	if( array_key_exists( 'cn_shipping_label', $_SESSION['ForceCountry'] ) )
	{
		$countryShippingLabelText = $_SESSION['ForceCountry']['cn_shipping_label'];
		$shippingLabel = getField( "select us_shipping_label from users where us_id = ".(int)ss_getUserID() );
	}
	else
	{
		$countryShippingLabelText = '';
		$shippingLabel = '';
	}

	$userAdmin->formName = 'CheckoutForm';
	if (ss_optionExists('Shop Checkout Password Note')) {
		//ss_DumpVar($userAdmin->fields);
		$userAdmin->fields['us_password']->note = 'When you return to us in the future, you can use your e-mail address and the password you chose here to access your account.';		
	} 

	if (ss_optionExists('Shop Checkout Zip Note')) {
		//ss_DumpVar($userAdmin->fields);
		$field = ListFirst(ss_optionExists('Shop Checkout Zip Note'));
		$value = ListLast(ss_optionExists('Shop Checkout Zip Note'));
		$userAdmin->fields['us_'.$field]->note = $value;
//		$shipping->fieldSet->fields['ShDe'.$field]->note = $value;		
	} 
	
//	ss_DumpVar( $userAdmin );
	if( array_key_exists( 'Do_Service',  $this->ATTRIBUTES ) &&  ( $this->ATTRIBUTES['Do_Service'] == 'Reload' ) )
	{

		unset( $this->ATTRIBUTES['DoAction'] );
		$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
		$userAdmin->primaryKey = $this->ATTRIBUTES['us_id'];		

//		foreach( $userAdmin->fields as $field )
//			$userAdmin->fields[$field['name']]->verify = 1;

		$purchaserDetails = $userAdmin->formDisplay($errors, false,true);
		ss_log_message_r( "purchaserDetails : ", $purchaserDetails );
		ss_log_message_r( "$userAdmin->fields : ", $userAdmin->fields );
	}
	else
		if ($loggedIn >= 0 and !array_key_exists('ClearDetails', $this->ATTRIBUTES))
		{	
			$this->param("us_id",$loggedIn);
			$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
			
			$userAdmin->primaryKey = $this->ATTRIBUTES['us_id'];		
			//$userDetail = ss_getUser();
			
			//$userAdmin->loadFieldValuesFromDB($tempuser);
			//ss_DumpVarDie($userAdmin->fields);
			
			if (ss_optionExists('Shop Checkout Password Note')) {
				$purchaserDetails = $userAdmin->form($errors, false,true,'FormNoteOnRight');
			} else if (ss_optionExists('Shop Checkout Custom Purchaser Details')) {
				$purchaserDetails = $userAdmin->form($errors, false,true,'PurchaserDetails');
			} else {
				$purchaserDetails = $userAdmin->form($errors, false);
			}
		}
		else
		{			
			$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
			$temp = new Request("Security.Sudo",array('Action'=>'start'));			
			if (ss_optionExists('Shop Checkout Password Note')) {
				$purchaserDetails = $userAdmin->form($errors, false,true,'FormNoteOnRight');
			} else if (ss_optionExists('Shop Checkout Custom Purchaser Details')) {
				$purchaserDetails = $userAdmin->form($errors, false,true,'PurchaserDetails');
			} else {
				$purchaserDetails = $userAdmin->form($errors, false);
			}
			$temp = new Request("Security.Sudo",array('Action'=>'stop'));	      	
		}	
//	ss_DumpVarDie( $purchaserDetails );
	
	$shippingForm = $shipping->display($this);
		
	
	require( "checkout_discount.php" );

	$data = array();
	
	$data['This'] = $this;
	$data['Basket'] = $_SESSION['Shop']['Basket'];

	$cn_id = (int)$userAdmin->fields['us_0_50A4']->value;
	if( !$cn_id )
		$cn_id = $_SESSION['ForceCountry']['cn_id'];

	$us_id = (int)ss_getUserID();
	$settled = false;

	$sql = '';
	$settled = false;

	$Q_CardTypes = query( "select * from credit_card_types where cct_id in ( select po_card_type from payment_gateways join payment_gateway_options on po_pg_id = pg_id where po_active = true )" );
	// $Q_CardTypes = query( "select * from credit_card_types where cct_id in ( select po_card_type from payment_gateways join payment_gateway_options on po_pg_id = pg_id where po_active = true and ( pg_limit IS NULL OR (pg_limit IS NOT NULL and pg_limit > pg_accumulation+{$_SESSION['Shop']['Basket']['Total']}) ) )" );
	$discounts = getField( "select count(*) from discounts where di_active = 'true' and di_starting <= now() and di_ending >= now()" );

	$data['NeedsPayment'] = !( array_key_exists( 'Account Credit', $_SESSION['Shop']['Basket']['Discounts'] ) && ($_SESSION['Shop']['Basket']['Total'] < 0) );
	$data['CurrencyCode'] = getDefaultCurrencyCode( );
	$data['CardTypes'] = $Q_CardTypes;
	$data['Style'] = 'WithInputs';
	$data['LoginHTML'] = $login->display;
	if( array_key_exists( 'Do_Service', $this->ATTRIBUTES) && ( $this->ATTRIBUTES['Do_Service'] == 'Reload' ) )
		$errors = array();
	$data['Errors'] = $errors;
	$data['ActiveDiscounts'] = $discounts;
	$data['LoggedIn'] = $loggedIn;
	$data['AssetPath'] = $assetPath;
	$data['Service'] = $this->ATTRIBUTES['Service'];
	$data['PurchaserDetails'] = $purchaserDetails;
	$data['ShippingFields'] = $shipping->fieldSet->fields;
	//ss_DumpVarDie($shipping->fieldSet->fields);
	$data['ShippingDetails'] = $shippingForm;
	$data['ShippingCountryFieldName'] = $shippingCountryFieldName;
//	$data['PaymentOptions'] = $Q_PaymentOptions;
	$data['tr_id'] = $this->ATTRIBUTES['tr_id'];
	$data['tr_token'] = $this->ATTRIBUTES['tr_token'];
	$data['Chosen'] = $this->ATTRIBUTES['Chosen'];
	$data['CountryWarning'] = $countryWarning;
	$data['PaymentOptions'] = getUserPaymentOptions();
	$data['PaymentOptionsOtherSite'] = getUserPaymentOptionsOtherSite();
	$data['OtherSiteName'] = getOtherSiteName();
	$data['PreviousOrders'] = $previousOrders;
	$data['LastGatewayName'] = $lastGatewayName;

	if ($_SESSION['Shop']['Basket']['Total'] <= 0) {
		$data['ConfirmOrder'] = true;
	} else {
		$data['ConfirmOrder'] = false;	
	}

	
	// Check for custom layout
	$checkLayout = ss_optionExists('Shop Checkout Layout');
	if ($checkLayout !== false) $asset->display->layout = $checkLayout;
		
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('Login',$data);
?>
