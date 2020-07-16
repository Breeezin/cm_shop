<?php
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
				
		$this->fieldSet->addField(new MemoField (array(
					'name'			=>	'AST_PAGE_PAGECONTENT',
					'displayName'	=>	'Page Content',
					'note'			=>	NULL,
					'required'		=>	FALSE,
					'verify'		=>	FALSE,
					'unique'		=>	FALSE,
					'size'	=>	'30',	'maxLength'	=>	'127',
					'rows'	=>	'20',	'cols'		=>	'100',
					'linkQueryAction'	=>	NULL,
					'linkQueryValueField'	=>	NULL,
					'linkQueryDisplayField'	=>	NULL,
					'Directory' => "Custom/ContentStore/Layouts/Images/",
		)));
		
?>
