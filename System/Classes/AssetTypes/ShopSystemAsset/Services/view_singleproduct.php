<?php
    $asset->display->layout = "None";
	if ($Q_Product->numRows() != 0) {

	$data = array(
		'Q_Product'			=>	$Q_Product,
		'LastSearch'		=>	array_key_exists('LastSearch',$_SESSION['Shop'])?$_SESSION['Shop']['LastSearch']:null,
		'AssetPath'			=>	$assetPath,
		'AssetStore'		=>	$assetStore,
		'CurrentServer'		=>	$GLOBALS['cfg']['currentServer'],
		'CategoryBreadCrumbs'	=>	$categoryBreadCrumbs,
		'AttributesHTML'	=>	$this->processTemplate('Attributes', $attributes),
		'OptionsHTML'		=>	$this->getOptions($product,$fieldsArray),
		'ExtraInfo'			=>	'',
		'TaxCountryNoteHTML'	=>	$this->getTaxCountryNote(),
		'CurrencyConverterHTML'	=>	$this->currencyConverter(),
        'Asset'             => $asset,
	);
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate($this->ATTRIBUTES['Template'],$data);
    } else
        echo '&nbsp;';
?>
