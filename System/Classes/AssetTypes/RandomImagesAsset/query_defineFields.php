<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	
	$this->fieldSet->addField(new HiddenField (array(
		'name'			=>	$this->fieldPrefix.'FORM',
		'displayName'	=>	'Images',
	)));
	
?>