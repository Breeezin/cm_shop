<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	
	$this->fieldSet->addField(new FieldSetBuilderField (array(
		'name'			=>	$this->fieldPrefix.'FIELDS',
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
			
	$this->fieldSet->addField(new TextField (array(
		'name'			=>	$this->fieldPrefix.'EMAIL_SUBJECT',
		'displayName'	=>  'Email Subject',
		'required'		=>	false,
		'size'			=>	40,
	)));

	$this->fieldSet->addField(new EmailField (array(
		'name'			=>	$this->fieldPrefix.'EMAIL_RECIPIENT',
		'displayName'	=>  'Email Recipient',
		'required'		=>	false,
		'size'			=>	40,
	)));
	
	$this->fieldSet->addField(new TextField (array(
		'name'			=>	$this->fieldPrefix.'SUBMIT_BUTTON',
		'displayName'	=>  'Submit Button Text',
		'required'		=>	false,
		'defaultValue'	=>	'Send',
		'size'			=>	20,
	)));	
	
?>