<?php		
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'ConfigurationForm',
		));
				
			
		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'PayableTo',
				'displayName' 	=>	'Payable To',						
				'note'			=>	NULL,
				'required'		=>	true,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'50',	'maxLength'	=>	'255',				
		)));
		
		$this->fieldSet->addField( new MemoField (array(
			'name'			=>	'ToAddress',
			'displayName'	=>	'To Address',			
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,			
			'cols'	=>	'40',	'rows'	=>	'5',				
		)));
			

?>