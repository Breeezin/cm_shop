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


	$boxes = 0.0;
	$vendors = array();
	for	($index=0;$index<count($_SESSION['Shop']['Basket']['Products']);$index++)
	{
		$entry = $_SESSION['Shop']['Basket']['Products'][$index];

		if ($entry['Qty'] > 0)
		{
			if( !in_array( $entry['Product']['pr_ve_id'], $vendors ) )
				$vendors[] = $entry['Product']['pr_ve_id'];

			$num_in_box = getField( "select pr0_883_f from shopsystem_products where pr_id = ".$entry['Product']['pr_id'] );

// 			if( ( $num_in_box > 0 ) && ( ($entry['Product']['pr_ve_id'] == 2) || ($entry['Product']['pr_ve_id'] >= 4) ) )		// llamas and swiss, marb or ravi
			if( ( $num_in_box > 0 ) && ( ($entry['Product']['pr_ve_id'] == 2) ) )
			{
				if( $num_in_box >= 25 )
					$boxes += $entry['Qty'];
				else
					$boxes += $entry['Qty'] / (25 / $num_in_box);
			}
		}
	}

	$boxes = round( $boxes + 0.499 );

	if( array_key_exists( 'tracking', $_GET ) )
		if( $_GET['tracking'] == 'true' )
			$_SESSION['TrackingChoice'] = true;
		else
			$_SESSION['TrackingChoice'] = false;

	$VendorCustomerNotes = '';
	if( count( $vendors ) )
	{
		$VCN = query( "select ve_customer_notes from vendor where ve_id in (".implode( ',', $vendors ).")" );
		while( $rw = $VCN->fetchRow())
			$VendorCustomerNotes .= '<br />'.$rw['ve_customer_notes'];
	}

	$tracking_choice = false;
	if( array_key_exists( 'TrackingChoice', $_SESSION ) )
		$tracking_choice = $_SESSION['TrackingChoice'];

	$userAdmin->formName = 'CheckoutForm';
	if (ss_optionExists('Shop Checkout Password Note')) {
		//ss_DumpVar($userAdmin->fields);
		$userAdmin->fields['us_password']->note = 'When you return to us in the future, you can use your e-mail address and the password you chose here to access your account.';		
	} 
	if (ss_optionExists('Shop Checkout Zip Note')) {
		//ss_DumpVar($userAdmin->fields);
		$field = ListFirst(ss_optionExists('Shop Checkout Zip Note'));
		$value = ListLast(ss_optionExists('Shop Checkout Zip Note'));
//		$userAdmin->fields['us_'.$field]->note = $value;		
//		$shipping->fieldSet->fields['ShDe'.$field]->note = $value;		
	} 
	
//	ss_DumpVar( $userAdmin );
//	ss_log_message( "view_checkout:65/ATTRIBUTES" );
//	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this->ATTRIBUTES );
	if( array_key_exists( 'Do_Service',  $this->ATTRIBUTES ) &&  ( $this->ATTRIBUTES['Do_Service'] == 'Reload' ) )
	{
		$debug = false;

		unset( $this->ATTRIBUTES['DoAction'] );
		$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
		$userAdmin->primaryKey = (int)ss_getUserID();
//		foreach( $userAdmin->fields as $field )
//			$userAdmin->fields[$field['name']]->verify = 1;
		if( $debug )
		{
			echo "\$userAdmin->fields =>";
			ss_DumpVar( $userAdmin->fields );
		}
		$purchaserDetails = $userAdmin->formDisplay($errors, false,true);
		if( $debug )
		{
			echo "\$purchaserDetails => ";
			ss_DumpVar( $purchaserDetails );
			echo "\$userAdmin->fields =>";
			ss_DumpVar( $userAdmin->fields );
			die;
		}
	}
	else
		if ($loggedIn >= 0 and !array_key_exists('ClearDetails', $this->ATTRIBUTES)) {	
			$this->param("us_id",$loggedIn);
			$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
			
			$userAdmin->primaryKey = (int)ss_getUserID();
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
		} else {			
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
		
	
	$result = new Request('Asset.Display',array(
		'Service'	=>	'Basket',
		'as_id'	=>	$this->asset->getID(),
		'Style'		=>	'NoInputs',
		'NoHusk'	=>	true,
	));
	$basket = $result->display;

	require( "checkout_discount.php" );

	$data = array();
	
	$data['This'] = $this;
	$data['Basket'] = $_SESSION['Shop']['Basket'];

	$cn_id = (int)$userAdmin->fields['us_0_50A4']->value;
	if( !$cn_id )
		$cn_id = $_SESSION['ForceCountry']['cn_id'];

	$Q_OtherShippingDetails = query( "select * from user_addresses where ua_us_id = ".ss_getUserID()." and ua_country = ".$_SESSION['ForceCountry']['cn_id'] );

	$us_id = (int)ss_getUserID();

	$Q_PaymentOptions = $pg['Gateway'];

	$discounts = getField( "select count(*) from discounts where di_active = 'true' and di_starting <= now() and di_ending >= now()" );

	$data['NeedsPayment'] = !( array_key_exists( 'Account Credit', $_SESSION['Shop']['Basket']['Discounts'] ) && ($_SESSION['Shop']['Basket']['Total'] < 0) );
	$data['Style'] = 'WithInputs';
	$data['JoinNewsletter'] = $this->ATTRIBUTES['JoinNewsletter'];
	$data['NewsletterField'] = $asset->cereal[$this->fieldPrefix.'NEWSLETTER_QUESTION'];
	$data['LoginHTML'] = $login->display;
	if( array_key_exists( 'Do_Service', $this->ATTRIBUTES) && ( $this->ATTRIBUTES['Do_Service'] == 'Reload' ) )
		$errors = array();
	$data['Errors'] = $errors;
	$data['ActiveDiscounts'] = $discounts;
	$data['LoggedIn'] = $loggedIn;
	$data['AssetPath'] = $assetPath;
	$data['Service'] = $this->ATTRIBUTES['Service'];
	$data['PurchaserDetails'] = $purchaserDetails;
	$data['BasketHTML'] = $basket;
	$data['ShippingFields'] = $shipping->fieldSet->fields;
	//ss_DumpVarDie($shipping->fieldSet->fields);
	$data['ShippingDetails'] = $shippingForm;
	$data['ShippingCountryFieldName'] = $shippingCountryFieldName;
	$data['PaymentOptions'] = $Q_PaymentOptions;
	$data['OtherShippingDetails'] = $Q_OtherShippingDetails;
	$data['tr_id'] = $this->ATTRIBUTES['tr_id'];
	$data['tr_token'] = $this->ATTRIBUTES['tr_token'];
	$data['GiftMessage'] = $this->ATTRIBUTES['GiftMessage'];
	$data['TrackingOptions'] = $tracking_options;
	$data['TrackingBoxCost'] = $tracking_box_cost;
	$data['TrackingChoice'] = $tracking_choice;
	$data['CountryWarning'] = $countryWarning;
	$data['VendorCustomerNotes'] = $VendorCustomerNotes;
	$data['CountryShippingLabelText'] = $countryShippingLabelText;
	$data['ShippingLabel'] = $shippingLabel;
	$data['PreviousOrders'] = $previousOrders;
	$data['NoChargeBlurb'] = $pg['NoChargeBlurb'];

/*	print_r( $data ); die;	*/

	if ($_SESSION['Shop']['Basket']['Total'] <= 0) {
		$data['ConfirmOrder'] = true;
	} else {
		$data['ConfirmOrder'] = false;	
	}
	
	// Check for custom layout
	$checkLayout = ss_optionExists('Shop Checkout Layout');
	if ($checkLayout !== false) $asset->display->layout = $checkLayout;
		
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('Checkout',$data);
?>
