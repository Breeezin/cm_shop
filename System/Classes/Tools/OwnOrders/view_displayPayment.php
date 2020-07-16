<?php 
	//{tmpl_eval $data['AssetTypeObject']->edit($data['this']);}
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'];
	$data = array();
	
	if ($processorType != null) {
		$data['ProcessorForm'] = $processorType->display(&$this);
	} else {
		$data['ProcessorForm'] = '&nbsp;';
	}
	
	$data['errors'] = $errors;
	
	$data['Fields'] = $this->payment;
	$data['tr_id'] = $this->ATTRIBUTES['tr_id'];
	$data['ChargedIn'] = $this->payment['tr_charge_total'];
	
	print $this->processTemplate("PaymentDisplay",$data);
	
	
?>