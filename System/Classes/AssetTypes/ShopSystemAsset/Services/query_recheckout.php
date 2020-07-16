<?php

	$this->param('tr_id', '');
	$this->param('tr_token', '');

	requireOnceClass("UsersAdministration");	
	
	$loggedIn = -1;
	//if (array_key_exists('GetDetail', $this->ATTRIBUTES))
	$loggedIn = ss_getUserID();
	
	// refresh the basket, recalc freight
	/*
	$argh = new Request('Asset.Display',array(
		'as_id'	=>	$asset->getID(),
		'Service'	=>	'UpdateBasket',
		'AsService'	=>	true,
		'Mode'		=>	'Refresh',
	));
	*/
	
	$userAdmin = new UsersAdministration(false,true);		//	isn't admin and yes hide password (optionally)

?>
