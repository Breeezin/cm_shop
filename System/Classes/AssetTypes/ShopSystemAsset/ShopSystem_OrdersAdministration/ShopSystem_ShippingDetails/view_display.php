<?PHP
		
	$data = array();	
	$data['fields'] = $this->fieldSet->fields;	
	$this->classDirectory = dirname(__FILE__);	
	$data['errors'] = array();
	$data['IsForm'] = $isForm;
	
	return $this->processTemplate("Display",$data);
	
?>
