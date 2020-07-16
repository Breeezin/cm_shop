<?php 
	//{tmpl_eval $data['AssetTypeObject']->edit($data['this']);}
	$data = array();
	$this->display->title = "Web Pay configuration";
			
	$data['errors'] = $errors;
	$data['BackURL'] = $this->ATTRIBUTES['BackURL'];
	$data['BreadCrumbs'] = $this->ATTRIBUTES['BreadCrumbs'];

	$data['FieldSet'] = $this;
	
	$data['FormAction'] = $_SERVER['SCRIPT_NAME'].'?act='.$this->ATTRIBUTES['act'].'&DoAction=1';	

	
	$data['DefaultCurrencySettings'] = $currencySettings->display(&$this);
	
	//ss_DumpVarDie($this);
	print $this->processTemplate("Edit",$data);
	
	
?>