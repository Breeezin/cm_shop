<?php 
	ss_paramKey($webpay->payment, 'tr_result');
	$results = unserialize($webpay->payment['tr_result']);
	$data['Results'] = $results;
	
	return $this->processTemplate('TransactionResult', $data);
?>