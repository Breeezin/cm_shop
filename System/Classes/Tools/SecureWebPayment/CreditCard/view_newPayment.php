<?PHP
	
	
	$data = array();
	$data['FieldSet'] = $this->fieldSet;	
	$this->classDirectory = dirname(__FILE__);
	
	$creditConfig = unserialize($webpay->webPayConfig['wpc_card_details']);
	$data['Processor'] = $creditConfig['Processor'];	
	return $this->processTemplate("CreditCardForm",$data);
	
?>
