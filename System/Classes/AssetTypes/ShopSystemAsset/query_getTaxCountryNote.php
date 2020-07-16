<?php

	$tax = null;
	$taxZone = null;
	$taxCountry = null;
	$taxRate = null;
	$currentTaxCountry = null;
	
	// Get the tax country
	if (array_key_exists('Shop',$_SESSION) and array_key_exists('TaxCountry',$_SESSION['Shop']) and $_SESSION['Shop']['TaxCountry'] !== false) {
		$taxCountry = $_SESSION['Shop']['TaxCountry']['cn_name'];
		$currentTaxCountry = $_SESSION['Shop']['TaxCountry']['cn_three_code'];	
	}

	// Get other tax details
	if (array_key_exists('TaxRate',$_SESSION['Shop']) and $_SESSION['Shop']['TaxRate'] !== false) {
		$tax = $_SESSION['Shop']['TaxRate']['txc_name'];
		$taxZone = $_SESSION['Shop']['TaxZone']['TaZoName'];
		$taxRate = $_SESSION['Shop']['TaxRate']['Rate'];
	}
	
	$Q_Countries = query("
		SELECT * FROM countries
		WHERE (cn_disabled IS NULL OR cn_disabled = 0)
		ORDER BY cn_name
	");	
	
	$data = array(
		'TaxCountry'	=>	$taxCountry,
		'Tax'	=>	$tax,
		'TaxZone'	=>	$taxZone,
		'TaxRate'	=>	$taxRate,
		'AssetPath'	=>	ss_withoutPreceedingSlash($this->asset->getPath()),
		'BackURL'		=>	getBackURL(),
		'Type'	=>	$type,
		'Q_Countries'	=>	$Q_Countries,
		'CurrentTaxCountry'	=>	$currentTaxCountry,
		'TaxIncluded'	=>	(ss_optionExists('Shop Tax Excluded')?'':' (included)'),
	);
	
	return $this->processTemplate('TaxNote',$data);
	
?>