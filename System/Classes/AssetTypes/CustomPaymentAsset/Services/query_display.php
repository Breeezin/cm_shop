<?php
	foreach($productDetails as $id => $detail) {
		$this->param('Product_'.$id		, $detail['price']);
		$this->param('Quantity_'.$id	, '0');
		$this->param('Total_'.$id 		, '0');
				
	}	
	$this->param('Total', '0');
	
	requireOnceClass('FieldSet');

	ss_paramKey($asset->cereal,$this->fieldPrefix.'FIELDS','');
	
	// Load the field set
	if (strlen($asset->cereal[$this->fieldPrefix.'FIELDS'])) {
		$fieldsArray = unserialize($asset->cereal[$this->fieldPrefix.'FIELDS']);
	} else {
		$fieldsArray = array();	
	}
	
	$fieldSet = new FieldSet();
	$fieldSet->addCustomizedFields($fieldsArray);
	
	
?>