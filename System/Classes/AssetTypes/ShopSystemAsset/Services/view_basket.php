<?
	$taxStyle = 'basketNoInputs';
	if ($this->ATTRIBUTES['Style'] == 'WithInputs') {
		$taxStyle = 'basketWithInputs';	
	}
	
	$this->param('ExtraInfo', '');
	
	$data = array(		
		'Basket'	=>	$_SESSION['Shop']['Basket'],
		'ExtraInfo'	=>	$this->ATTRIBUTES['ExtraInfo'],
		'AssetPath'	=>	ss_withoutPreceedingSlash($asset->getPath()),
		'LastSearch'		=>	array_key_exists('LastSearch',$_SESSION['Shop'])?$_SESSION['Shop']['LastSearch']:null,
		'This'		=>	$this,
		'TaxCountryNoteHTML'	=>	$this->getTaxCountryNote($taxStyle),
		'Style'		=>	$this->ATTRIBUTES['Style'],
		'BackURL'	=>	getBackURL(),
		'CurrencyConverterHTML'	=>	$this->currencyConverter(),
		'CurrencyCountry'	=>	$_SESSION['Shop']['CurrencyCountry'],
		'DisplayCurrency'	=>	$this->getDisplayCurrency(),
		'ChargeCurrency'   =>	$this->getChargeCurrency(),
		'DiscountCode'	   =>	$_SESSION['Shop']['DiscountCode'],
        'MinQuantity'      =>  array_key_exists('MinQuantity',$this->ATTRIBUTES)?$this->ATTRIBUTES['MinQuantity']:null,
        'MinCost'          =>  array_key_exists('MinCost',$this->ATTRIBUTES)?$this->ATTRIBUTES['MinCost']:null,
	);
	if (ss_optionExists('Duty Free Loyal Member')) {
		if (array_key_exists('DiscountCodeMember',$_SESSION['Shop']) and strlen($_SESSION['Shop']['DiscountCodeMember'])) {
			$data['DiscountCode'] = $_SESSION['Shop']['DiscountCodeMember'];
		}
	}
	//ss_DumpVarDie($_SESSION, '', true);
	// Check for custom layout
	$checkLayout = ss_optionExists('Shop Basket Layout');
	if ($checkLayout !== false) $asset->display->layout = $checkLayout;
	
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('Basket',$data);

?>
