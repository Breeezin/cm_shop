<?php 
	
	//$source = $originCurrency;
	
	$defaultCurrencySettings = unserialize($this->webPayConfig['wpc_default_currency_details']);
	$Q_Currency = getRow("SELECT * FROM countries WHERE cn_id = {$defaultCurrencySettings['DefaultCurrency']}");
	$dest = $Q_Currency['cn_currency_code'];
	$exchageRate = ss_getExchangeRate($originCurrency, $dest);
	$chargePrice = sprintf("%01.2f", $exchageRate*$originPrice);
	if ($returnWithSymbol) {
		if ($defaultCurrencySettings['DefaultCurrencySymPos'] == 'after') {
			$chargePrice .= $defaultCurrencySettings['DefaultCurrencySymbol'];
		} else {
			$chargePrice = $defaultCurrencySettings['DefaultCurrencySymbol'].$chargePrice;
		}
	}
	
	return $chargePrice.' '.$dest;
?>