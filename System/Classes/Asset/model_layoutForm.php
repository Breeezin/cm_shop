<?php
/*	requireOnceClass('FieldSet');

	$this->param("as_layout_serialized", '');
	$this->param("FormName", '');
	$this->param("Layout", 'None');
	$this->param("as_id", 0);
*/	
	$RelativeHere = $this->classDirectory."/";

//	$this->display->layout = 'None';
	
	$layout = array();
	
	if (count($this->layout)) {
		//$this->layout = deserialize($this->ATTRIBUTES['as_layout_serialized']);
		//$this->layout = unserialize($this->ATTRIBUTES['as_layout_serialized']);
		
		ss_paramKey($this->layout,'LYT_LAYOUT');
		ss_paramKey($this->layout,'LYT_STYLESHEET');		
		ss_paramKey($this->layout,'LYT_TITLEIMAGE');
		
		$layout['LYT_LAYOUT'] = $this->layout['LYT_LAYOUT'];
		$layout['LYT_STYLESHEET'] = $this->layout['LYT_STYLESHEET'];
		$layout['LYT_TITLEIMAGE'] = ss_storeForAsset($this->ATTRIBUTES['as_id']).$this->layout['LYT_TITLEIMAGE'];
		
		/*
		$layout['LYT_KEYWORDS'] = $this->layout['LYT_KEYWORDS'];
		$layout['LYT_DESCRIPTION'] = $this->layout['LYT_DESCRIPTION'];
		*/
		
		/*$layout['Lyt_Menu_NormalImage'] = ss_storeForAsset($this->ATTRIBUTES['as_id']).$this->layout['Lyt_Menu_NormalImage'];
		$layout['Lyt_Menu_MouseOverImage'] = ss_storeForAsset($this->ATTRIBUTES['as_id']).$this->layout['Lyt_Menu_MouseOverImage'];
		$layout['Lyt_Image_Folder'] = $this->layout['Lyt_Image_Folder'];*/
	}else {
		$layout['LYT_LAYOUT'] = '';
		$layout['LYT_STYLESHEET'] = '';
		$layout['LYT_TITLEIMAGE'] = '';
		/*
		$layout['LYT_KEYWORDS'] = '';		
		$layout['LYT_DESCRIPTION'] = '';
		*/
		
/*		$layout['Lyt_Menu_NormalImage'] = '';
		$layout['Lyt_Menu_MouseOverImage'] = '';
		$layout['Lyt_Image_Folder'] = '';*/
	}

	$layout['as_search_keywords'] = $this->fields['as_search_keywords'];
	$layout['as_search_description'] = $this->fields['as_search_description'];
	
	/*if (strlen($layout['Lyt_Image_Folder']) != 0) {
		$fileFolder =$layout['Lyt_Image_Folder'];
	} else {
		$fileFolder = rand();
	}*/
	
	/*$this->fieldSet = new FieldSet();
	
	$this->fieldSet->addField(new UniqueImageField (array(
					'name'			=>	'Lyt_TitleImage',
					'displayName'	=>	'Title Image',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
					'directory'		=>	ss_secretStoreForAsset($this->ATTRIBUTES['as_id'], $fileFolder)."/",
					'imageName' 	=> $layout['Lyt_TitleImage'],
					'preview'		=>false,
					'iconDir'		=> $RelativeHere,
	)));
	
	
	$this->fieldSet->addField(new UniqueImageField (array(
					'name'			=>	'Lyt_Menu_NormalImage',
					'displayName'	=>	'Menu Normal',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
					'directory'		=>	ss_secretStoreForAsset($this->ATTRIBUTES['as_id'], $fileFolder)."/",
					'imageName' 	=> $layout['Lyt_Menu_NormalImage'],
					'preview'		=> false,
					'iconDir'		=> $RelativeHere,
	)));
	
	$this->fieldSet->addField(new UniqueImageField (array(
					'name'			=>	'Lyt_Menu_MouseOverImage',
					'displayName'	=>	'Menu MouseOver',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
					'directory'		=>	ss_secretStoreForAsset($this->ATTRIBUTES['as_id'], $fileFolder)."/",
					'imageName' 	=> $layout['Lyt_Menu_MouseOverImage'],
					'preview'		=> false,
					'iconDir'		=> $RelativeHere,
	)));
		
	$this->fieldSet->addField(new HiddenField (array(
			'name'			=>	'Lyt_Image_Folder',
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'folder'			=>  $fileFolder,
			'linkQueryAction'	=>	NULL,
			'linkQueryValueField'	=>	NULL,
			'linkQueryDisplayField'	=>	NULL,
	)));*/
	
	$ListLayouts = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Layouts.txt')),chr(10));
	
	$ListStylesheets = '';
	if (ss_optionExists("StyleSheet Picker")) {
		$ListStylesheets = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Stylesheets.txt')),chr(10));
	}
	
	$layout['fieldSet'] = $this->layoutFieldSet->fields;
	$layout['ListLayouts'] = $ListLayouts;
	$layout['ListStylesheets'] = $ListStylesheets;
	$layout['assetFieldSet'] = $this->fieldSet;

	$this->useTemplate('LayoutForm',$layout);

?>
