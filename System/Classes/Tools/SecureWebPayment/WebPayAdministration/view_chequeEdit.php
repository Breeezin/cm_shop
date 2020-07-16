<?php 
	//{tmpl_eval $data['AssetTypeObject']->edit($data['this']);}
	$data = array();
		
	$data['errors'] = $errors;
	
	
	$data['FormAction'] = $_SERVER['SCRIPT_NAME'].'?act='.$this->ATTRIBUTES['act'].'&DoAction=1';	
	
	$data['Settings'] = $chequeSettings->display(&$this);
	
	//ss_DumpVarDie($this);
	if (array_key_exists('DoAction', $this->ATTRIBUTES)) {
		if (!count($errors)) 
		print("<script language='javascript'>window.close()</script>");
	} else {
		print $this->processTemplate("Config",$data);	
	}
	
	
?>