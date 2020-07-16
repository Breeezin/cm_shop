<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	$this->fieldSet->addField(new PopupUniqueImageField (array(
		'name'			=>	'AST_IMAGE_STD',
		'displayName'	=>	'Image',
		'directory'		=>	ss_storeForAsset($asset->getID()),
		'preview'	=>	false,
	)));

?>