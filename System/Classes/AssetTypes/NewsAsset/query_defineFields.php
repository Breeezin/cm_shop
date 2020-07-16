<?php		
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
		
		$this->fieldSet->addField(new IntegerField(array(
			'name'		=>	$this->fieldPrefix.'PANELITEMS',
			'required'	=>	false,
		)));
		
		$this->fieldSet->addField(new IntegerField(array(
			'name'		=>	$this->fieldPrefix.'ITEMSPERDISPLAY',
			'required'	=>	false,
		)));
?>