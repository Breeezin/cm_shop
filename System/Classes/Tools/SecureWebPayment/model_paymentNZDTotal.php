<?php 
	//ss_DumpVarDie($this);
	if ($this->payment['cn_currency_code'] == 'NZD') 
		return $this->payment['tr_total'];
	else {				
		$exchageRate = ss_getExchangeRate($this->payment['cn_currency_code'], 'NZD');
		$chargePrice = sprintf("%01.2f", $exchageRate*$this->payment['tr_total']);
		
		return $chargePrice;	
	}
		
?>