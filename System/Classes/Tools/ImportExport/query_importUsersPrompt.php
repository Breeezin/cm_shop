<?php

	$Q_UserGroups = query("
		SELECT * FROM user_groups
		WHERE ug_id > 0
		ORDER By ug_name
	");
	
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

?>