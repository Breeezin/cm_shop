<?php		
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
				
		$this->fieldSet->addField(new HtmlMemoField2 (array(
					'name'			=>	'AST_PAGE_PAGECONTENT',
					'displayName'	=>	'Page Content',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'30',	'maxLength'	=>	'127',
					'rows'	=>	'6',	'cols'		=>	'40',
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
					'Directory' => "Custom/ContentStore/Layouts/Images/",
		)));

		$this->fieldSet->addField(new HtmlMemoField2 (array(
					'name'			=>	'AST_PAGE_TRANSLATEDPAGECONTENT',
					'displayName'	=>	'Translated Page Content',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'30',	'maxLength'	=>	'127',
					'rows'	=>	'6',	'cols'		=>	'40',
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
					'Directory' => "Custom/ContentStore/Layouts/Images/",
		)));
		
?>