<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	/*$this->fieldSet->addField(new MoneyField (array(
		'name'		=>	$this->fieldPrefix.'FB_FEE',
		'displayName'	=>	"Frequent Buyer Club Membership Fee",	
		'note'			=>	null,
		'required'		=>	true,
		'verify'		=>	FALSE,
		'unique'		=>	false,
		'size'	=>	'5',	'maxLength'	=>	'10',		
	)));
		
	$this->fieldSet->addField(new TextField(array(
		'name'		=>	$this->fieldPrefix.'ADMINEMAIL',
		'displayName'	=>	"Notification Email Address",
		'required'	=>	true,
		'default'	=>	'',			
		'size'	=> 40, 	'maxLength'	=> 256,
	)));	*/
	
	$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'LOGIN_CONTENT',
		'displayName'	=>	'Login Content',
		'required'		=>	true,
		'height'	=>	'200',
	)));

	$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'WELCOME_CONTENT',
		'displayName'	=>	'Welcome Content',
		'required'		=>	true,
		'height'	=>	'200',
	)));

	$this->fieldSet->addField(new SelectField(array(
		'name'			=>	$this->fieldPrefix.'JOIN_GROUPS',
		'displayName'	=>  'Join Groups',
		'required'		=>	true,
		'multi'			=> 	true,
		'linkQueryAction'	=>	'UserGroupsAdministration.Query',
		'linkQueryDisplayField'	=>	'ug_name',
		'linkQueryValueField'	=>	'ug_id',
	)));
	
	$this->fieldSet->addField(new SelectField(array(
		'name'			=>	$this->fieldPrefix.'ALLOWED_GROUPS',
		'displayName'	=>  'Allowed Groups',
		'required'		=>	true,
		'multi'			=> 	true,
		'linkQueryAction'	=>	'UserGroupsAdministration.Query',
		'linkQueryDisplayField'	=>	'ug_name',
		'linkQueryValueField'	=>	'ug_id',
	)));	
	
	
	/*$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'FB_REGISTRATION_THANK_YOU_CONTENT',
		'displayName'	=>	'Frequent Buyer Club Registration Thank You Page',
		'required'		=>	true,
		'height'	=>	'200',
	)));	
	
	$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'TI_REGISTRATION_THANK_YOU_CONTENT',
		'displayName'	=>	'Travel Industry Card Registration Thank You Page',
		'required'		=>	true,
		'height'	=>	'200',
	)));*/	
	
	
	

	
?>