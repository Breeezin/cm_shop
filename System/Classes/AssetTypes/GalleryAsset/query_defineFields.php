<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	
	$this->fieldSet->addField(new HiddenField (array(
		'name'			=>	$this->fieldPrefix.'FORM',
		'displayName'	=>	'Images',
	)));
	$this->fieldSet->addField(new IntegerField(array(
		'name'			=>	$this->fieldPrefix.'THUMBNAIL_HEIGHT',		
		'displayName'	=>  'Thumnail Height',
		'required'		=>	true ,
		'size'	=>	3,	'maxlength'	=> 3,
	)));
	
	$this->fieldSet->addField(new IntegerField(array(
		'name'			=>	$this->fieldPrefix.'THUMBNAIL_WIDTH',		
		'displayName'	=>  'Thumnail Width',
		'required'		=>	true,
		'size'	=>	3,	'maxlength'	=> 3,
	)));
	
	$this->fieldSet->addField(new IntegerField(array(
		'name'			=>	$this->fieldPrefix.'IMAGES_PER_ROW',		
		'displayName'	=>  'Images Per Row',
		'required'		=>	true ,
		'size'	=>	3,	'maxlength'	=> 2,
	)));
	
	$this->fieldSet->addField(new IntegerField(array(
		'name'			=>	$this->fieldPrefix.'ROWS_PER_PAGE',		
		'displayName'	=>  'Rows Per Page',
		'required'		=>	false,
		'size'	=>	3,	'maxlength'	=> 2,
	)));
	
	$this->fieldSet->addField(new IntegerField(array(
		'name'			=>	$this->fieldPrefix.'POPUP_HEIGHT',		
		'displayName'	=>  'Popup Height',
		'required'		=>	true ,
		'size'	=>	3,	'maxlength'	=> 3,
	)));
	
	$this->fieldSet->addField(new IntegerField(array(
		'name'			=>	$this->fieldPrefix.'POPUP_WIDTH',		
		'displayName'	=>  'Popup Width',
		'required'		=>	true,
		'size'	=>	3,	'maxlength'	=> 3,
	)));
	
?>