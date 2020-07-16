<?PHP
		
	$data = array();
	$data['FieldSet'] = $this->fieldSet;	
	$this->classDirectory = dirname(__FILE__);
	$data['Processor'] = $this->fieldSet->getFieldValue("Processor");
	$data['UseCurrency'] = $this->fieldSet->getFieldValue("UseCurrency");
	
	return $this->processTemplate("Display",$data);
	
?>
