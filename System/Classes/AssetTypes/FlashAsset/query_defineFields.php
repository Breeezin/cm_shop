<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	
	$this->fieldSet->addField(new FileField (array(
		'name'			=>	$this->fieldPrefix.'FILENAME',
		'displayName'	=>	'Flash File',
		'directory'		=>	ss_storeForAsset($asset->getID()),
		'secure'		=>	true,
	)));
		
	$this->fieldSet->addField(new AttributesField(array(
		'name'			=>	$this->fieldPrefix.'ATTRIBUTES',
		'displayName'	=>	'Flash Attributes',
		'required'		=> 	false,
		'options'		=>  array(
								array('name'=>'attName','title'=>'Attribute', 'permission' => 'IsDeployer'), 
								array('name'=>'attValue','title'=>'Value', 'permission' => ''), 								
							),				
		'managedBy'		=> 	'IsDeployer',		
	)));
	
	$this->fieldSet->addField(new IntegerField(array(
		'name'			=>	$this->fieldPrefix.'HEIGHT',		
		'displayName'	=>  'Height',
		'required'		=>	true ,
		'size'	=>	3,	'maxlength'	=> 3,
	)));
	
	$this->fieldSet->addField(new IntegerField(array(
		'name'			=>	$this->fieldPrefix.'WIDTH',		
		'displayName'	=>  'Width',
		'required'		=>	true,
		'size'	=>	3,	'maxlength'	=> 3,
	)));
?>