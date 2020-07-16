<?php		
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
		
		$this->fieldSet->addField(new EmailField(array(
			'name'		=>	$this->fieldPrefix.'FROM_EMAIL',
			'required'	=>	false,
		)));
?>