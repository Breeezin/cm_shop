<?php 
	$this->param("FormName");
	$data = array();
	$data['Cheque'] =  $this->webPayConfig['wpc_use_cheque']==1?true:false;
	$data['Invoice'] = (ss_optionExists('Invoice Payment Option') and $this->webPayConfig['wpc_can_invoice']==1)?true:false;
	$data['Collection'] =  (ss_optionExists('Pay On Collection Payment Option') and $this->webPayConfig['wpc_use_collection']==1)?true:false;
//	$data['Direct'] =  $this->webPayConfig['wpc_direct_payment']==1?true:false;
//	Rex - Hack to be false so we can set it in another site using the same database, different code base
	$data['Direct'] =  false;
	$data['CreditCard'] =  $this->webPayConfig['wpc_use_credit_card']==1?true:false;
	$data['OneOption'] = 'Checked';
	$data['FormName'] = $this->ATTRIBUTES['FormName'];
	if ($data['Cheque'] and $data['CreditCard']) {
		if (ss_optionExists('Pay On Collection Payment Option') and ss_optionExists('Invoice Payment Option')) {
			if ($data['Invoice'] and $data['Collection']) {
				$data['OneOption'] = '';
			}		
		} else if (ss_optionExists('Pay On Collection Payment Option') or ss_optionExists('Invoice Payment Option')) {
			if (ss_optionExists('Invoice Payment Option') and $data['Invoice']) {
				$data['OneOption'] = '';			
			}
			if (ss_optionExists('Pay On Collection Payment Option') and $data['Collection']) {
				$data['OneOption'] = '';			
			}
		} else {
			$data['OneOption'] = '';		
		}
	}
	
	return $this->processTemplate("PaymentOptions",$data);
?>
