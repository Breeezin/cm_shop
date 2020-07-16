<?php		
		$this->fieldSet = new FieldSet(array(
			'formName'	=>	'AssetForm',
		));
				
		$this->fieldSet->addField(new IntegerField(array(
			'name'		=>	$this->fieldPrefix.'ITEMSPERDISPLAY',
			'required'	=>	false,
		)));
		
		$this->fieldSet->addField(new CheckBoxField(array(
			'name'		=>	$this->fieldPrefix.'SHOWTYPEFILTER',
			'required'	=>	false,
		)));
		
		$this->fieldSet->addField(new MultiCheckArrayFromQueryField (array(
			'name'			=>	$this->fieldPrefix.'TYPES',
			'displayName'	=>	'Item Type To Search',
			'note'			=>	null,
			'required'		=>	true,
			'verify'		=>	false,
			'unique'		=>	false,
			'size'	=>	'30',	'maxLength'	=>	'40',
			'rows'	=>	'6',	'cols'		=>	'40',
			'columns'	=>	3,
			'linkQueryAction'	=>	'AssetTypesAdministration.Query',
			'linkQueryValueField'	=>	'at_id',
			'linkQueryDisplayField'	=>	'at_display',					
			'linkQueryParameters'	=>	array('FilterSQL'	=>	'AND at_allow_search = 1'),				
		)));	
		
		$this->fieldSet->addField(new CheckBoxField(array(
			'name'		=>	$this->fieldPrefix.'ENABLE_ITEMS',
			'onClick'	=> 'enableAssets(this)',
			'required'	=>	false,			
		)));
		
		
		$this->fieldSet->addField(new MultiAssetTreeField(array(
			'name'			=>	$this->fieldPrefix.'ASSETS',
			'displayName'	=>	'Target',
			'required'		=>	false,
			'size'	=>	'10',	'maxLength'	=>	'255',		
			'onFocus'		=>	'',	
			'treeProperty'   => array('openerFormName'=>'AssetForm',
									  'treeDescription'=>'Please select an item for search.',
									  'treeAssetRootID'=>'1',
									  'treeStyle'=>'width:260;height:300; overflow:auto;border:solid black 1px;',
									  'appearsInMenus'=>'No',
									  'includeChildrenOf'=>array(ss_systemAsset('index.php') => 1),
									  'excludeAssets'=>array(),
									  'excludeChildrenOf'=>array(),
									  'appearsInMenus'=>'No',),
			'treePopWindowProperty' => 'width=300,height=350,scrollbar=1',
		)));	
?>