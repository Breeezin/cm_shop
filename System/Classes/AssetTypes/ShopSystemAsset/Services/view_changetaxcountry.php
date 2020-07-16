<?php
	$currentTaxCountry = null;
	if (array_key_exists('Shop',$_SESSION) and array_key_exists('TaxCountry',$_SESSION['Shop']) and $_SESSION['Shop']['TaxCountry'] !== false) {
		$currentTaxCountry = $_SESSION['Shop']['TaxCountry']['cn_three_code'];
	}
	$data = array(
		'BackURL'		=>	$this->ATTRIBUTES['BackURL'],
		'AssetPath'		=>	ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath())),
		'Q_Countries'	=>	$Q_Countries,
		'CurrentTaxCountry'	=>	$currentTaxCountry,
	);
	
	$this->useTemplate('ChangeTaxCountry',$data);

?>