<?php
				
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
				
		$this->fieldSet->addField(new PopupUniqueImageField (array(
			'name'			=>	'AST_PRINTPAGE_BUTTONIMAGE',
			'displayName'	=>	'Image',
			'directory'		=>	ss_storeForAsset($asset->getID()),
			'preview'		=>	true,
		)));
		
		$this->fieldSet->addField(new PopupUniqueImageField (array(
			'name'			=>	'AST_PRINTPAGE_BUTTONIMAGEOVER',
			'displayName'	=>	'Image',
			'directory'		=>	ss_storeForAsset($asset->getID()),
			'preview'		=>	true,
		)));
		
		$this->fieldSet->addField(new IntegerField(array(
			'name'			=>	'AST_PRINTPAGE_POPUP_WINDOW_WIDTH',
		)));
		
		$this->fieldSet->addField(new IntegerField(array(
			'name'			=>	'AST_PRINTPAGE_POPUP_WINDOW_HEIGHT',
		)));
?>