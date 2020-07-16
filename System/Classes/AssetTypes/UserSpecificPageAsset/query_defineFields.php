<?php

	$this->fieldSet = new FieldSet(array(
		'formName'	=>	'AssetForm',
	));
	
	$this->fieldSet->addField(new HtmlMemoField2 (array(
		'name'			=>	$this->fieldPrefix.'DEFAULT_PAGE',
		'displayName'	=>	'Default Content',
		'note'			=>	null,
		'required'		=>	true,
		'verify'		=>	false,
		'unique'		=>	false,
		'size'	=>	'30',	'maxLength'	=>	'127',
		'rows'	=>	'6',	'cols'		=>	'40',
		'width'	=>	'document.body.clientWidth-85',
		'Directory' => "Custom/ContentStore/Layouts/Images/",
	)));
			
?>