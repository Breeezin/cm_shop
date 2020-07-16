<?php
	$result = new Request('Security.Authenticate',array(
		'Permission'	=>	'CanAdministerAtLeastOneAsset',
	));
	
	$this->param("as_name", "Untitled Item");
	$this->param("Layout", "");
	$this->param("Stylesheet", "");
	
	$this->param("AsService",false);
	$this->param("EntryErrors","");
	
	//Find out the asset types we know about and the limits assigned to them 	
	require("query_addMany.php");
	// now we have asset types, layouts and stylesheets to display 
	
?>