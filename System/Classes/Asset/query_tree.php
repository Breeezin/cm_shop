<?php 

	$this->param('RootAssetID','1');
	$this->param('MaxDepth','1024');
	$this->param('AppearsInMenus','No');
	$this->param('OnClick','void');
	$this->param('OnDoubleClick','void');
	$this->param('TreeOnClick','');
	$this->param('TreeStyle','');
	$this->param('OpenAssets',array());
	$this->param('IncludeChildrenOf',array());
	$this->param('ExcludeChildrenOf',array());
	$this->param('ExcludeAssets',array());
	$this->param("FilterByAdmin", false);
	foreach (array('IncludeChildrenOf','ExcludeChildrenOf','ExcludeAssets') as $name) {
		if (is_string($this->ATTRIBUTES[$name])) {
			$this->ATTRIBUTES[$name] = ListToKeyArray($this->ATTRIBUTES[$name]);
		}
	}
	
	// Note IncludeChildrenOf and ExcludeChildrenOf should be defined like :
	// array($assetid1 => 1,$assetid2 => 1)
	// not
	// array($assetid1,$assetid2);
		
	
	// Get the tree structure
	$result = new Request("Asset.TreeStructure",array(
		'RootAssetID'	=>	null,
		'AppearsInMenus'	=>	$this->ATTRIBUTES['AppearsInMenus'],
		'IncludeChildrenOf'	=>	$this->ATTRIBUTES['IncludeChildrenOf'],
		'ExcludeChildrenOf'	=>	$this->ATTRIBUTES['ExcludeChildrenOf'],
		'ExcludeAssets'	=>	$this->ATTRIBUTES['ExcludeAssets'],
		'FilterByAdmin'	=>	$this->ATTRIBUTES['FilterByAdmin'],		
	));
	
	$treeStructure = $result->value;
	//ss_DumpVar($treeStructure);
	
?>