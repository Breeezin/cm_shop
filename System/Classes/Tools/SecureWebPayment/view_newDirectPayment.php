<?php 
	//{tmpl_eval $data['AssetTypeObject']->edit($data['this']);}
	$data = array();
	$this->display->title = "Payment";
	$data['AccountName'] = $directConfig['AccountName'];
	$data['AccountNumber'] = $directConfig['AccountNumber'];
	$data['AccountNote'] = $directConfig['AccountNote'];
			
	$data['errors'] = $errors;
	
	$data['Fields'] = $this->payment;
	$data['FormAction'] = $_SERVER['SCRIPT_NAME'].'?act='.$this->ATTRIBUTES['act'].'&DoAction=1';
	$data['tr_id'] = $this->ATTRIBUTES['tr_id'];
	$data['tr_token'] = $this->ATTRIBUTES['tr_token'];
	$data['BackURL'] = $this->ATTRIBUTES['BackURL'];
	$data['Type'] = $this->ATTRIBUTES['Type'];		
	$data['ChargedIn'] = $chargedIn;
	
	print $this->processTemplate("PaymentDirect",$data);
	
	
?>