<?php		
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'ConfigurationForm',
		));
				
	
		$this->fieldSet->addField( new SelectField (array(
			'name'			=>	'DefaultCurrency',
			'displayName'	=>	'Currency',			
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'multi'			=>	FALSE,
			'size'	=>	'30',	'maxLength'	=>	'25',
			'rows'	=>	'6',	'cols'		=>	'40',
			'linkQueryAction'	=>	'CountryAdministration.Query',
			'linkQueryValueField'	=>	'cn_id',
			'linkQueryDisplayField'	=>	'cn_name',
			'linkQueryParameters'	=>	array('FilterSQL'	=>	'AND cn_currency_code IS NOT NULL AND cn_currency_disabled IS NULL'),
		)));
		
		
		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'DefaultCurrencySymbol',
				'displayName' 	=>	'Default Currency Symbol',						
				'note'			=>	NULL,
				'required'		=>	true,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'4',	'maxLength'	=>	'25',				
		)));
		
		$this->fieldSet->addField( new SelectFromArrayField (array(
			'name'			=>	'DefaultCurrencySymPos',
			'displayName'	=>	'Currency',			
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'multi'			=>	FALSE,			
			'options'	=>	array('before'	=>	'before', 'after'	=>	'after'),
		)));
			

?>