<?

	$this->param('CountryThreeCode','');
	$this->param('CurrencyThreeCode','');
	$this->param('BackURL','');

	if (strlen($this->ATTRIBUTES['CurrencyThreeCode'])) {
		$this->setCurrencyCountry($this->ATTRIBUTES['CurrencyThreeCode'],true);
	} else {
		$this->setCurrencyCountry($this->ATTRIBUTES['CountryThreeCode']);
	}
	if (!strlen($this->ATTRIBUTES['BackURL'])) {
		locationRelative($assetPath);	
	}
	
	location($this->ATTRIBUTES['BackURL']);

?>
