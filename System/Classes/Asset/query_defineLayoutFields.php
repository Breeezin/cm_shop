<?php
	requireOnceClass('FieldSet');

	$this->layoutFieldSet = new FieldSet(array(
		'formName' => 'AssetForm')
	);
	
	$this->layoutFieldSet->addField(new PopupUniqueImageField (array(
		'name'			=>	'LYT_TITLEIMAGE',
		'directory'		=>	ss_storeForAsset($this->getID()),
	)));
	$this->layoutFieldSet->addField(new PopupUniqueImageField (array(
		'name'			=>	'LYT_MENU_NORMALIMAGE',
		'directory'		=>	ss_storeForAsset($this->getID()),
	)));
	$this->layoutFieldSet->addField(new PopupUniqueImageField (array(
		'name'			=>	'LYT_MENU_MOUSEOVERIMAGE',
		'directory'		=>	ss_storeForAsset($this->getID()),
	)));
	
	$this->layoutFieldSet->addField(new HiddenField (array(
			'name'	=>	'LYT_LAYOUT',
	)));
	$this->layoutFieldSet->addField(new CheckBoxField (array(
			'name'	=>	'LYT_LAYOUT_APPLY_TO_CHILDREN',
			'defaultValue'	=>	1
	)));

	
	$this->layoutFieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	'LYT_LAYOUT_SUBPAGECONTENT',
				'displayName'	=>	'Sub Content',
				'note'			=>	NULL,
				'required'		=>	FALSE,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'height'	=>	'200',			
				'Directory' => "Custom/ContentStore/Layouts/Images/",
	)));

	$this->layoutFieldSet->addField(new HtmlMemoField2 (array(
				'name'			=>	'LYT_LAYOUT_SECURITYPAGE',
				'displayName'	=>	'Error page',
				'note'			=>	NULL,
				'required'		=>	FALSE,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'height'	=>	'200',
				'Directory' => "Custom/ContentStore/Layouts/Images/",
	)));

	$this->layoutFieldSet->addField(new HiddenField (array(
			'name'	=>	'LYT_STYLESHEET',
	)));	
	
	$this->layoutFieldSet->addField(new TextField (array(
			'name'	=>	'LYT_WINDOWTITLE',
			'size'	=>	50,
	)));	
	$ListLayouts = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Layouts.txt')),chr(10));
	
	$ListStylesheets = '';
	if (ss_optionExists("StyleSheet Picker")) {
		$ListStylesheets = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Stylesheets.txt')),chr(10));
	}
	
	$layout['fieldSet'] = $this->fieldSet->fields;
	$layout['ListLayouts'] = $ListLayouts;
	$layout['ListStylesheets'] = $ListStylesheets;


?>
