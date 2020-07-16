<?php		
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'ConfigurationForm',
		));
										
		$this->fieldSet->addField( new MemoField (array(
			'name'			=>	'CollectionNote',
			'displayName'	=>	'Note',			
			'required'		=>	False,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,			
			'cols'	=>	'40',	'rows'	=>	'5',				
		)));
			

?>s