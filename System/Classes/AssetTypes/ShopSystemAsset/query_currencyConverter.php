<?php
	global $cfg;

	$currentTaxCountry = null;
	$currentTaxCurrency = null;
	$site_id = 1;

	if( $rw = getRow( "select * from configured_sites where si_name = '".$cfg['multiSites'][$cfg['currentServer']]."'" ) )
		$site_id = $rw['si_id'];

	// Get the tax country
	if (array_key_exists('Shop',$_SESSION) and array_key_exists('CurrencyCountry',$_SESSION['Shop']) and $_SESSION['Shop']['CurrencyCountry'] !== false) {
		$currentTaxCountry = $_SESSION['Shop']['CurrencyCountry']['cn_three_code'];	
		$currentTaxCurrency = $_SESSION['Shop']['CurrencyCountry']['cn_currency_code'];	
	}

	$Q_Countries = query("
		SELECT DISTINCT cn_currency, cn_three_code FROM countries
		WHERE (cn_disabled IS NULL OR cn_disabled = 0)
			AND cn_currency_code IS NOT NULL
		GROUP BY cn_currency, cn_three_code
		ORDER BY cn_name
	");	
	
	$Q_Currencies = query("
		SELECT DISTINCT cn_currency, cn_currency_code FROM countries
		WHERE (cn_disabled IS NULL OR cn_disabled = 0)
			AND cn_currency_disabled IS NULL
			AND cn_currency_code IS NOT NULL
			AND cn_currency_code in (select po_currency from payment_gateway_options where po_active = 1 and po_site = $site_id)
		GROUP BY cn_currency, cn_currency_code
		ORDER BY cn_currency
	");		
	
	$data = array(
		'AssetPath'	=>	ss_withoutPreceedingSlash($this->asset->getPath()),
		'BackURL'		=>	getBackURL(),
		'Q_Countries'	=>	$Q_Countries,
		'Q_Currencies'	=>	$Q_Currencies,
		'CurrentTaxCountry'	=>	$currentTaxCountry,
		'CurrentTaxCurrency'	=>	$currentTaxCurrency,
	);
	
	return $this->processTemplate('CurrencyConverter',$data);
	
?>
