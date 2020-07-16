<?PHP
	
	
	$data = array();
	$data['FieldSet'] = $this->fieldSet;	
	$this->classDirectory = dirname(__FILE__);
	
	return $this->processTemplate("CreditCardFormDisplay",$data);
	
?>
