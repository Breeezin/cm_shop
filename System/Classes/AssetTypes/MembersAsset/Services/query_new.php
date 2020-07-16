<?php 

/*	requireOnceClass("MembersAdministration");
	
	$userAdmin = new MembersAdministration($asset);*/
	
	requireOnceClass("UsersAdministration");
	$userAdmin = new UsersAdministration(false);

	/*if (ss_optionExists('Member Edit Fields')) {
		requireOnceClass("MembersAdministration");
		$userAdmin = new MembersAdministration($asset, true);
	} else {
	}*/

/*
	$userAdmin->addField(new TextField(array(
		'name'	=>	'us_user_name',
		'displayName'	=>	'User Name',
		'note'	=>	'Give this user name to your friends to enter when they join up!',
		'size'	=>	20,
		'maxLength'	=>	255,
	)));
	$referralDisplayType = 'input';
	$referralNote = 'This cannot be changed after initial registration.';
	$userAdmin->addField(new TextField(array(
		'name'	=>	'us_referral_user_name',
		'displayName'	=>	'User Name of your Referrer',
		'note'	=>	$referralNote,
		'size'	=>	20,
		'maxLength'	=>	255,
		'displayType'	=>	$referralDisplayType,
	)));
*/
	
	
	$this->ATTRIBUTES['BackURL'] = $assetPath."/Service/New";
	$this->ATTRIBUTES['act'] = $assetPath."/Service/New/Do_Service/Yes";	
	$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
	
	$errors = array();	
	
?>
