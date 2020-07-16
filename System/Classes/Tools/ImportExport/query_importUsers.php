<?php
	

	/*if (!array_key_exists("DisableOutputBuffering",$_REQUEST)) {
		location('index.php?'.$_SERVER['QUERY_STRING'].'&DisableOutputBuffering=1');	
	}*/

	$this->param('Code');
	$this->param('user_groups');
	$this->param('UserUpdate', ''); // option for user update or only insert
	
	$insertGroups = unserialize($this->ATTRIBUTES['user_groups']);
	$groups = ss_URLEncodedFormat(ArrayToList($insertGroups));
	
	// Load the tabbed file
	//$targetDir = ss_withTrailingSlash(dirname($_SERVER['SCRIPT_FILENAME'])).'Custom/Cache/Incoming/';
	//$Q_Users = ss_ParseTabDelimitedFile($targetDir.$this->ATTRIBUTES['DataFile']);
	
	// Load the users asset
	$UsersAsset = getRow("
		SELECT * FROM assets
		WHERE as_id = ".ss_systemAsset('users')."
	");
	
	// Deserialize the settings
	$settings = unserialize($UsersAsset['as_serialized']);
	
	ss_paramKey($settings,'AST_USER_FIELDS','');
	
	if (!strlen($settings['AST_USER_FIELDS'])) {
		$fields = array();	
	} else {
		$fields = unserialize($settings['AST_USER_FIELDS']);
	}

	$Q_ImportUsers = query("
		SELECT imu_id FROM import_users
		WHERE imu_user_code LIKE '".escape($this->ATTRIBUTES['Code'])."'
	");
	
?>