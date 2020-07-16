<?php
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'ConfigurationForm',
			'primaryKey'=>	1,
		));
				
			
		$this->fieldSet->addField( new HiddenField(array(
				'name'			=>	'Processor',
				'displayName' 	=>	'Processor',						
				'note'			=>	NULL,
				'required'		=>	true,				
		)));
		$this->fieldSet->addField( new HiddenField(array(
				'name'			=>	'UseCurrency',
				'displayName' 	=>	'Use Currency',
				'note'			=>	NULL,
				'required'		=>	true,
		)));

		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'PayProMarchantKey',
				'displayName' 	=>	'Merchant Key',
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'50',	'maxLength'	=>	'255',
		)));

		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'EGateMarchantID',
				'displayName' 	=>	'Marchant ID',
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'40',	'maxLength'	=>	'16',
		)));
		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'EGateAccessCode',
				'displayName' 	=>	'Access Code',
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'40',	'maxLength'	=>	'8',
		)));
//**new HPP
		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'HPPUserID',
				'displayName' 	=>	'User ID',
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'70',	'maxLength'	=>	'255',
		)));

		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'HPPAccessKey',
				'displayName' 	=>	'Access Key',
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'70',	'maxLength'	=>	'100',
		)));
		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'HPPMacKey',
				'displayName' 	=>	'Mac Key',
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'70',	'maxLength'	=>	'50',
		)));
//**

		$this->fieldSet->addField( new PasswordField (array(
				'name'			=>	'EGateHashSecret',
				'displayName' 	=>	'Secure Hash Secret 1',						
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	true,
				'unique'		=>	FALSE,
				'size'	=>	'40',	'maxLength'	=>	'50',				
		)));
		
		$this->fieldSet->addField( new RadioFromArrayField (array(
				'name'			=>	'PayProMode',
				'displayName' 	=>	'Mode',						
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'options'		=>	array('Test'=>'T', 'Production'=>'P'),
				'size'	=>	'50',	'maxLength'	=>	'255',				
		)));
		
		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'DPSAccount',
				'displayName' 	=>	'DPS Account',						
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'30',	'maxLength'	=>	'255',				
		)));
		
		
		$this->fieldSet->addField( new PasswordField (array(
				'name'			=>	'DPSPassword',
				'displayName' 	=>	'DPS Password',						
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'30',	'maxLength'	=>	'255',				
		)));
		
		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'PaystationMarchantKey',
				'displayName' 	=>	'Merchant Key',
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'50',	'maxLength'	=>	'255',
		)));

 		$this->fieldSet->addField( new RadioFromArrayField (array(
				'name'			=>	'PaystationMode',
				'displayName' 	=>	'Mode',
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'options'		=>	array('Test'=>'T', 'Production'=>'P'),
				'size'	=>	'50',	'maxLength'	=>	'255',
		)));

		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'ZipZapUsername',
				'displayName' 	=>	'ZipZap Username',						
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'30',	'maxLength'	=>	'255',				
		)));
		
		$this->fieldSet->addField( new TextField (array(
				'name'			=>	'ZipZapUsernameNZD',
				'displayName' 	=>	'ZipZap Username NZD',						
				'note'			=>	NULL,
				'required'		=>	false,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'30',	'maxLength'	=>	'255',
		)));
		
		$this->fieldSet->addField(new MultiCheckField(array(
			'name'			=>	'credit_card_types',
			'displayName'	=>	'CreditCard Types',
			'note'			=>	NULL,
			'required'		=>	TRUE,			
			'linkQueryAction'	=>	'CreditCardTypeAdministration.Query',
			'linkQueryValueField'	=>	'cct_id',
			'linkQueryDisplayField'	=>	'cct_name',
			'linkTableName'		=>	'web_pay_configuration_credit_card_types',
			'linkTableOurKey'	=>	'wpcf_wpc_id',
			'linkTableTheirKey'	=>	'wpcf_cct_id',			
		)));
		
		
		
		
?>