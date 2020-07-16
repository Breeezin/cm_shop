<?php

	$temp = new Request('Security.Sudo',array('Action' => 'Start'));
	$result = new Request('shopsystem_categories.QueryAllArray',array(
		'as_id'	=>	$asset->getID(),
	));
	/*$result2 = new Request('shopsystem_categories.QueryAllArray',array(
		'as_id'	=>	$asset->getID(),
	));*/
	$temp = new Request('Security.Sudo',array('Action' => 'Stop'));
	
	$categories = $result->value;
	//print($result->display);
/*	ss_DumpVar($categories,'1');
	ss_DumpVar($result2->value,'2');*/
	
	$data = array(
		'CategoriesArray'	=>	$categories,		
		'AssetPath'			=>	ss_withoutPreceedingSlash($asset->getPath()),
		'as_id'			=>	$asset->getID(),
	);

	// Check for custom layout
	$checkLayout = ss_optionExists('Shop Search Layout');
	if ($checkLayout !== false) $asset->display->layout = $checkLayout;
		
	// Always link in the shop style sheet
	ss_customStyleSheet($this->styleSheet);
	$this->useTemplate('Search',$data);
?>