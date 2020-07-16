<?php 

	
		
	
	
	ss_paramKey($asset->cereal,$this->fieldPrefix.'LAYOUT', '');
	if (strlen($asset->cereal[$this->fieldPrefix.'LAYOUT'])) {
		$asset->display->layout = $asset->cereal[$this->fieldPrefix.'LAYOUT'];
	}	
		
	$this->ATTRIBUTES['us_id'] = ss_getUserID();
	requireOnceClass("UsersAdministration");
	$userAdmin = new UsersAdministration(false);

	/*if (ss_optionExists('Member Edit Fields')) {
		requireOnceClass("MembersAdministration");
		$userAdmin = new MembersAdministration($asset, true);
	} else {
	}*/
	
	/*
	$check = getRow("SELECT * FROM users WHERE us_id = ".safe($this->ATTRIBUTES['us_id']));
	$referralDisplayType = 'input';
	$referralNote = 'This cannot be changed after initial registration.';
	if ($check['us_user_name'] === null) {
		$userAdmin->addField(new TextField(array(
			'name'	=>	'us_user_name',
			'displayName'	=>	'User Name',
			'note'	=>	'Give this user name to your friends to enter when they join up!',
			'size'	=>	20,
			'maxLength'	=>	255,
		)));
	} else {
		$userAdmin->addField(new TextField(array(
			'name'	=>	'us_user_name',
			'displayName'	=>	'User Name',
			'size'	=>	20,
			'maxLength'	=>	255,
			'displayType'	=>	'output',
		)));
		$referralDisplayType = 'output';
		$referralNote = null;
	}
	$userAdmin->addField(new TextField(array(
		'name'	=>	'us_referral_user_name',
		'displayName'	=>	'User Name of your Referrer',
		'note'	=>	$referralNote,
		'size'	=>	20,
		'maxLength'	=>	255,
		'displayType'	=>	$referralDisplayType,
	)));
	*/

	
	$this->ATTRIBUTES['BackURL'] = $assetPath;
	
	//$this->ATTRIBUTES['act'] = $assetPath."/Service/Edit/Do_Service/Yes";	
	$userAdmin->ATTRIBUTES = $this->ATTRIBUTES;
	$userAdmin->primaryKey = $this->ATTRIBUTES['us_id'];	
	$errors = array();	
	
?>
