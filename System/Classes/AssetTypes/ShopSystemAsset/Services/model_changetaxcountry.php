<?

	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		$this->param('CountryThreeCode');
		$this->setTaxCountry($this->ATTRIBUTES['CountryThreeCode']);

		unset($_SESSION['Shop']['Basket']['Freight']);
		$this->setDefaultFreight();

        // Modify for complete change is specific country shopping : Clean basket, et al
        if (ss_optionExists("Shop Products Limited countries")) {
		    $multiCountries = ss_optionExists('Shop Product Multi-Country Prices');
            foreach (ListToArray($multiCountries,':') as $currencyDef) {
				$code = ListFirst($currencyDef);
				if ($code == $this->ATTRIBUTES['CountryThreeCode']) {
                    $oldCode = $_SESSION['Shop']['MultiCurrencyCountryDef']['CountryCode'];
					$def = ListToArray($currencyDef);
					$_SESSION['Shop']['MultiCurrencyCountryDef'] = array(
						'CountryCode'	=>	$def[0],
						'CurrencyCode'	=>	$def[1],
						'Symbol'		=>	$def[2],
						'Appears'		=>	$def[3],
						'MinimumOrder'	=>	$def[4],
					);

                    if ($code != $oldCode){
                        // changing country, clearing basket, as other combinations are unpredicatable
            			$result = new Request("Asset.Display",array(
            				'as_id'	=>	$asset->getID(),
            				'Service'	=>	'UpdateBasket',
            				'Mode'	=>	'Empty',
            				'AsService'	=>	true,
            				'NoHusk'	=>	1,
            			));
                    }


					break;
				}
			}
        }

		location($this->ATTRIBUTES['BackURL']);
	}	

?>
