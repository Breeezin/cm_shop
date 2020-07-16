<?

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
	$purchaserDetails = $userAdmin->form($errors, false);
	$shippingForm = $shipping->display($this);
	$paymentOptions = new Request("WebPay.Options", array('FormName'=>'CheckoutForm'));
	$temp = new Request("Security.Sudo",array('Action'=>'finish'));	

//	$Details = unserialize( $Order['or_details'] );
//	$GiftMessage = $Details['GiftMessage'];

	if( array_key_exists( 'tr_currency_code', $Transaction ) )
		setDefaultCurrency( $Transaction['tr_currency_code'] );
	else
		setDefaultCurrency( );

	$data = array(
		'Q_Products'	=>	$Q_Products,
		'Basket'	=>	$_SESSION['Shop']['Basket'],
		'GiftMessage' => '',
		'AssetPath'	=>	ss_withoutPreceedingSlash($asset->getPath()),
		'This'		=>	$this,
		'TaxCountryNoteHTML'	=>	$this->getTaxCountryNote($taxStyle),
		'Style'		=>	$this->ATTRIBUTES['Style'],
		'BackURL'	=>	getBackURL(),
		'CurrencyCountry'	=>	$_SESSION['Shop']['CurrencyCountry'],
		'DisplayCurrency'	=>	$this->getDisplayCurrency(),
		'ChargeCurrency'	=>	$this->getChargeCurrency(),
		'OrderCurrency'		=>  getDefaultCurrencyCode(),
		'Q_Categories'		=>	$Q_Categories,
		'DiscountCode'       => '',
		'Errors'	=>	$errors,
	);

	// Checkout stuff
	$data['PurchaserDetails'] = $purchaserDetails;
	$data['ShippingFields'] = $shipping->fieldSet->fields;
	$data['ShippingDetails'] = $shippingForm;
	$data['ShippingCountryFieldName'] = $shippingCountryFieldName;
	$data['PaymentOptions'] = $paymentOptions->value;
//	$data['tr_id'] = $this->ATTRIBUTES['tr_id'];
//	$data['tr_token'] = $this->ATTRIBUTES['tr_token'];
	
	// Check for custom layout
	$checkLayout = ss_optionExists('Shop Basket Layout');
	if ($checkLayout !== false) $asset->display->layout = $checkLayout;
	
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('OrderForClient',$data);
	
?>
