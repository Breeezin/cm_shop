<?php 

	$this->param('tr_id', '-1');
	$Q_Configuration = query("SELECT * FROM web_pay_configuration, countries WHERE wpc_id = 1 AND WePaCoCountryLink=cn_id");
	$aConfiguration = $Q_Configuration->fetchRow();
	$this->ATTRIBUTES['RetainTransaction'] = $aConfiguration['WePaCoRetainTransaction'];
	
	$Q_Transaction = query("SELECT * FROM transactions WHERE tr_id ={$this->ATTRIBUTES['tr_id']}"); 		
	$aTransaction = $Q_Transaction->fetchRow();
?>