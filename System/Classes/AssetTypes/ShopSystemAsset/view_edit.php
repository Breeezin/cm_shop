<?php
	$secureSite = ss_withTrailingSlash($GLOBALS['cfg']['secure_server']);
	$webpaySetting = ss_getWebPaymentConfiguration();
	$webpaySetting = ss_getWebPaymentConfiguration();
	$data = array(
		'imagesDirectory'	=>	$this->classDirectory.'/AssetTypes/'.$this->getClassName().'/Templates/Images/',
		'as_id'			=>	$asset->getID(),		
		'FieldSet'			=>	$this->fieldSet,
		'FieldSet'			=>	$this->fieldSet,
		'SecureSite'		=>	$secureSite,		
	);
	
	$data['DefaultEmailCheque'] = '';
	$temp = array();
	if ($webpaySetting['UseCheque']) {
		$data['DefaultEmailCheque'] = $this->processTemplate('tmp_DefaultEmailCheque', $temp);
	}
	if ($webpaySetting['UseDirect']) {
		$data['DefaultEmailDirect'] = $this->processTemplate('tmp_DefaultEmailDirect', $temp);
	}
	if ($webpaySetting['UseInvoice']) {
		$data['DefaultEmailInvoice'] = $this->processTemplate('tmp_DefaultEmailInvoice', $temp);
	}
	$data['DefaultEmailCredit'] = '';
	if ($webpaySetting['UseCreditCard']) {				
		
		if ($webpaySetting['CreditCardSetting']['Processor'] == 'WebPay_CreditCard_Manual') {
			$data['DefaultEmailCredit'] = $this->processTemplate('tmp_DefaultEmailManualCredit', $temp);			
		} else {
			$data['DefaultEmailCredit'] = $this->processTemplate('tmp_DefaultEmailCredit', $temp);					
		}
	}
	
	
	$this->useTemplate('Edit',$data);
?>
