<?php 
	//{tmpl_eval $data['AssetTypeObject']->edit($data['this']);}
	$this->display->title = $this->ATTRIBUTES['BreadCrumbs'];
	$data = array();
	
	if ($processorType != null) {
		print $processorType->displayTransactionResult(&$this);
	} else {
		print 'No Transaction Response codes for the transction.';
	}	
?>