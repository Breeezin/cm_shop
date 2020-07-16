<?

	$taxStyle = 'basketNoInputs';
	if ($this->ATTRIBUTES['Style'] == 'WithInputs') {
		$taxStyle = 'basketWithInputs';	
	}

	$temp = new Request("Security.Sudo",array('Action'=>'start'));	
	if ($_SESSION['Shop']['OrderingFor'] > 0) 
	{
		$this->param("us_id",$_SESSION['Shop']['OrderingFor']);
		$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
		$userAdmin->primaryKey = $this->ATTRIBUTES['us_id'];		
	}

	if (strlen($this->ATTRIBUTES['Address']))
	{
		$purchaserDetails = $userAdmin->form($errors, false);
		$shippingForm = $shipping->display($this);
		$paymentOptions = new Request("WebPay.Options", array('FormName'=>'CheckoutForm'));
		$temp = new Request("Security.Sudo",array('Action'=>'finish'));	

		$Details = unserialize( $Order['or_details'] );
		$GiftMessage = $Details['GiftMessage'];

		$data = array(
			'AssetPath'	=>	ss_withoutPreceedingSlash($asset->getPath()),
			'This'		=>	$this,
			'Address'		=>  (int)($this->ATTRIBUTES['Address']),
			'Style'		=>	$this->ATTRIBUTES['Style'],
			'BackURL'	=>	getBackURL(),
			'CurrencyCountry'	=>	$_SESSION['Shop']['CurrencyCountry'],
			'Errors'	=>	$errors,
		);

		$data['PurchaserDetails'] = $purchaserDetails;
		$data['ShippingFields'] = $shipping->fieldSet->fields;
		$data['ShippingDetails'] = $shippingForm;
		$data['ShippingCountryFieldName'] = $shippingCountryFieldName;

		$checkLayout = ss_optionExists('Shop Basket Layout');
		if ($checkLayout !== false) $asset->display->layout = $checkLayout;
		
		ss_customStyleSheet($this->styleSheet);
		$this->useTemplate('EditOrderAddress',$data);
	}
	else
//	if( IsSet( $details ) )
	{
		$taxStyle = 'basketNoInputs';
		if ($this->ATTRIBUTES['Style'] == 'WithInputs') {
			$taxStyle = 'basketWithInputs';	
		}

		$temp = new Request("Security.Sudo",array('Action'=>'start'));	
		if ($_SESSION['Shop']['OrderingFor'] > 0) {
			$this->param("us_id",$_SESSION['Shop']['OrderingFor']);
			$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
			$userAdmin->primaryKey = $this->ATTRIBUTES['us_id'];		
		}
		$temp = new Request("Security.Sudo",array('Action'=>'finish'));	

		$Details = unserialize( $Order['or_details'] );
		$GiftMessage = $Details['GiftMessage'];

		$data = array(
			'Ident'			=>  $Order['or_tr_id'].' '.$Order['or_purchaser_firstname'].' '.$Order['or_purchaser_lastname'],
			'Q_Products'	=>	$Q_Products,
			'Basket'	=>	$_SESSION['Shop']['Basket'],
			'GiftMessage' => $GiftMessage,
			'AssetPath'	=>	ss_withoutPreceedingSlash($asset->getPath()),
			'This'		=>	$this,
			'TaxCountryNoteHTML'	=>	$this->getTaxCountryNote($taxStyle),
			'Style'		=>	$this->ATTRIBUTES['Style'],
			'BackURL'	=>	getBackURL(),
			'CurrencyCountry'	=>	$_SESSION['Shop']['CurrencyCountry'],
			'DisplayCurrency'	=>	$this->getDisplayCurrency(),
			'ChargeCurrency'	=>	$this->getChargeCurrency(),
			'Q_Categories'		=>	$Q_Categories,
			'Errors'	=>	$errors,
		);

		// Check for custom layout
		$checkLayout = ss_optionExists('Shop Basket Layout');
		if ($checkLayout !== false) $asset->display->layout = $checkLayout;
		
		ss_customStyleSheet($this->styleSheet);
		$this->useTemplate('EditOrderBasket',$data);
	}
//	else
//		ss_log_message( "Edit order, no details" );

?>
