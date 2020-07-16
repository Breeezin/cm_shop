<?

	$temp = new Request("Security.Sudo",array('Action'=>'start'));	
	$shippingForm = $shipping->display($this);
	$temp = new Request("Security.Sudo",array('Action'=>'finish'));	

	$data = array(
		'AssetPath'	=>	ss_withoutPreceedingSlash($asset->getPath()),
		'This'		=>	$this,
		'Style'		=>	$this->ATTRIBUTES['Style'],
		'BackURL'	=>	getBackURL(),
		'Errors'	=>	$errors,
	);

	// Checkout stuff
	$data['ShippingFields'] = $shipping->fieldSet->fields;
	$data['ShippingDetails'] = $shippingForm;
	$data['ShippingCountryFieldName'] = $shippingCountryFieldName;

	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('NewShippingDetails',$data);
	
?>
