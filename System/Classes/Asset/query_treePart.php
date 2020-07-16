<?php

	$this->param('as_id',1);

	$result = new Request('Asset.PathFromID',array(
		'as_id'	=>	$this->ATTRIBUTES['as_id']
	));
	$assetPath = $result->value;

	// Get the tree structure
	$result = new Request('Asset.TreeStructure',array(
		'RootAssetID'		=>	$this->ATTRIBUTES['as_id'],
		'AppearsInMenus'	=>	'No',
	));
	
	$treeStructure = $result->value;
	
?>