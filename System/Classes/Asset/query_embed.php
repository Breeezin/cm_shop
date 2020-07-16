<?php

	// Get the asset specified by attributes or server path_info
	$this->loadAsset();

	// Check if they are allowed to access this asset
	$result = new Request('Security.Authenticate',array(
		'Permission'	=>	'CanAccessAsset',
		'as_id'		=>	$this->getID(),
	));	
	
?>