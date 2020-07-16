<?

	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		$this->param('CountryThreeCode');
		$this->setTaxCountry($this->ATTRIBUTES['CountryThreeCode']);

		unset($_SESSION['Shop']['Basket']['Freight']);
		$this->setDefaultFreight();
		
		location($this->ATTRIBUTES['BackURL']);
	}	

?>