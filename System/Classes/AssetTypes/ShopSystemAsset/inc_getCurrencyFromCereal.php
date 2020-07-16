<?php
//	function getEnterCurrencyFromCereal($cereal,$type) {

	if (!ss_optionExists('Shop Non-NZD Currencies')) {
		// Defaulting to NZD
		return array(
			'CurrencyCode'	=>	'NZD',
			'Symbol'		=>	'$',
			'Appears'		=>	'before',
		);	
	} else {
		
		ss_paramKey($cereal,'AST_SHOPSYSTEM_'.$type.'_CURRENCY',554);
		ss_paramKey($cereal,'AST_SHOPSYSTEM_'.$type.'_CURRENCY_SYMBOL','$');
		ss_paramKey($cereal,'AST_SHOPSYSTEM_'.$type.'_CURRENCY_SYMBOL_POS','before');
		$country = getRow("
			SELECT * FROM countries WHERE cn_id = ".$cereal['AST_SHOPSYSTEM_'.$type.'_CURRENCY']."
		");
		return array(
			'CurrencyCode'	=>	$country['cn_currency_code'],
			'Symbol'	=>	$cereal['AST_SHOPSYSTEM_'.$type.'_CURRENCY_SYMBOL'],
			'Appears'	=>	$cereal['AST_SHOPSYSTEM_'.$type.'_CURRENCY_SYMBOL_POS'],
		);
	}

?>