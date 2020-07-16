<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	
	$this->fieldSet->addField(new FieldSetBuilderField (array(
		'name'			=>	$this->fieldPrefix.'FIELDS',
		'showComments'	=>	true,
	)));
	
	$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'THANK_YOU_PAGE',
		'displayName'	=>	'Thank You Page',
		'note'			=>	null,
		'required'		=>	true,		
		'verify'		=>	false,
		'unique'		=>	false,
		'size'	=>	'30',	'maxLength'	=>	'127',
		'rows'	=>	'6',	'cols'		=>	'40',
		'width'	=>	'document.body.clientWidth-85',
		'Directory' => "Custom/ContentStore/Layouts/Images/",
	)));
	
	$this->fieldSet->addField(new EmailField (array(
		'name'			=>	$this->fieldPrefix.'ADMINEMAIL',
		'displayName'	=>  'Email Recipient',
		'required'		=>	true,
		'size'			=>	40,
	)));

	$this->fieldSet->addField(new AttributesField(array(
		'name'			=>	$this->fieldPrefix.'PRODUCTS',
		'displayName'	=>	'Products',
		'required'		=> 	false,
		'options'		=>  array(
								array('name'=>'name','title'=>'Product Name', 'permission' => 'IsDeployer'),
								array('name'=>'price','title'=>'Price', 'permission' => ''),
							),
		'managedBy'		=> 	'IsDeployer',
	)));
	
	$this->fieldSet->addField(new CheckBoxField (array(
		'name'			=>	$this->fieldPrefix.'USE_CUSTOM_DISPLAY_TEMPLATE',
		'displayName'	=>  'Use Custom Display Template?',
		'required'		=>	false,
		'size'			=>	20,
	)));		
	
	$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'CUSTOM_DISPLAY_TEMPLATE',
		'displayName'	=>	'Custom Display Template',
		'note'			=>	null,
		'required'		=>	false,		
		'verify'		=>	false,
		'unique'		=>	false,
		'size'	=>	'30',	'maxLength'	=>	'127',
		'rows'	=>	'6',	'cols'		=>	'40',
		'width'	=>	'document.body.clientWidth-85',
		'Directory' => "Custom/ContentStore/Layouts/Images/",
	)));	

?>