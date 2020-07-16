<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	
	$this->fieldSet->addField(new FileField (array(
		'name'			=>	$this->fieldPrefix.'FILENAME',
		'displayName'	=>	'File',
		'directory'		=>	ss_storeForAsset($asset->getID()),
		'secure'		=>	true,
	)));
	
	$this->fieldSet->addField(new PopupUniqueImageField (array(
		'name'			=>	$this->fieldPrefix.'DOWNLOADBUTTON',
		'displayName'	=>	'Download Button',
		'directory'		=>	ss_storeForAsset($asset->getID()),
		'preview'	=>	false,
	)));	
	$this->fieldSet->addField(new PopupUniqueImageField (array(
		'name'			=>	$this->fieldPrefix.'DOWNLOADBUTTONOVER',
		'displayName'	=>	'Download Button Over',
		'directory'		=>	ss_storeForAsset($asset->getID()),
		'preview'	=>	false,
	)));	
	
?>