<?php		
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'ConfigurationForm',
		));
				
			
		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'AccountName',
				'displayName' 	=>	'Account Name',						
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'50',	'maxLength'	=>	'255',				
		)));
		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'AccountNumber',
				'displayName' 	=>	'Account Number',						
				'note'			=>	NULL,
				'required'		=>	true,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'50',	'maxLength'	=>	'255',				
		)));
		
		$this->fieldSet->addField( new MemoField (array(
			'name'			=>	'AccountNote',
			'displayName'	=>	'Note',			
			'required'		=>	False,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,			
			'cols'	=>	'40',	'rows'	=>	'5',				
		)));
			

?>