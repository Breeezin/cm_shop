<?php 
	$this->param('tr_id');
	
	$transaction = getRow("SELECT * FROM transactions WHERE tr_id = {$this->ATTRIBUTES['tr_id']}");	
	
	$updateSql = '';
	if ($transaction['tr_payment_method'] == 'WebPay_CreditCard_Manual') {
		$updateSql = ",tr_status_link = 2";
	}
	
	$Q_UpdateTrascation = query("
		UPDATE transactions 
		SET 
			tr_payment_details_szln = null
			$updateSql
		WHERE tr_id = {$this->ATTRIBUTES['tr_id']}
	");	
	
?>