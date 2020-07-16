<?php

	// Firstly, we'll grab some users =b
	$Q_Users = query("
		SELECT 
			us_id,
			us_first_name as `First Name`,
			us_last_name as `Last Name`,
			us_email as `Email`
	 	FROM users
		WHERE us_id > 1
		ORDER BY us_first_name, us_last_name
	");

	$Q_Users->addColumn('User Groups');
	
	// Now add some user groups
	$currentRow = 0;
	while ($row = $Q_Users->fetchRow()) {
		$Q_UserGroups = query("
			SELECT ug_name FROM user_groups, user_user_groups
			WHERE uug_us_id = {$row['us_id']}
				AND uug_ug_id = ug_id
			ORDER BY ug_name
		");
		$ugs = $Q_UserGroups->columnValuesList('ug_name',', ','');
		$Q_Users->setCell('User Groups',$ugs,$currentRow);
		$currentRow++;
	}
	
	$users = ss_queryToTab($Q_Users,array('us_id'));
	
?>
