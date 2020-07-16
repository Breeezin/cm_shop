<?php
	requireOnceClass("UsersAdministration");
	// Make a new users admin class
	$userAdmin = new UsersAdministration();
	
	startAdminPercentageBar('Importing users...');

	$counter = 0;
	while ($user = $Q_Users->fetchRow()) {

		$insertData = array(
			'us_html_email'	=>	1,
			'us_name'	=>	array(
				'first_name'	=>	'',
				'last_name'	=>	'',
			),
		);

		// Pull in first name, last name, email and password
		if (array_key_exists("First Name",$user)) {
			$insertData['us_name']['first_name'] = $user['First Name'];			
		}
		if (array_key_exists("Last Name",$user)) {
			$insertData['us_name']['last_name'] = $user['Last Name'];			
		}
		if (array_key_exists("Email",$user)) {
			$insertData['us_email'] = $user['Email'];			
		}
        if ( ss_optionExists('User Import Username Hash') and array_key_exists("UserName",$user) ) {
			$insertData['us_password'] = substr(md5($user['UserName']),0,8);
        } else if (array_key_exists("Password",$user)) {
			$insertData['us_password'] = $user['Password'];
		} else if (array_key_exists("Email",$user)) {
			$insertData['us_password'] = $user['Email'];
		}

		// Add the verify password value
		$insertData['UsPassword_V'] = $insertData['us_password'];
		$insertData['user_groups'] = array();
		
		// Kludge if they supplied the first and last name in the same field..
		if (array_key_exists('Full Name',$user)) {
			if (ListLen($user['Full Name'],' ') > 1) {
				$insertData['us_name']['last_name'] = ListLast($user['Full Name'],' ');			
				$insertData['us_name']['first_name'] = substr($user['Full Name'],0,0-(strlen($insertData['us_name']['last_name'])+1));			
			} else {
				$insertData['us_name']['first_name'] = $user['Full Name'];			
			}
		}

		// Load all the custom fields also
		foreach($fields as $field) {
			ss_paramKey($field,"name");
			ss_paramKey($field,"uuid");
			if ($field['uuid'] !== 'Name' and $field['uuid'] !== 'Email' and $field['uuid'] !== 'Password') {
				if (array_key_exists($field['name'],$user)) {
					// Do something here to find the uuid of the value?
					$insertData['Us'.$field['uuid']] = $user[$field['name']];
				}
			}
		}
		
		$insertData['user_groups'] = $insertGroups;
		$insertData['DoAction'] = 'Yes';
		
		$Q_CheckExisting = query("
			SELECT * FROM users
			WHERE us_email LIKE '".escape($insertData['us_email'])."'
		");
		
		if ($Q_CheckExisting->numRows()) {
			if (strlen($this->ATTRIBUTES['UserUpdate'])) {
				if (ss_isOffline()) {
					$oldDebugStatus = $GLOBALS['cfg']['debugMode'];
					$GLOBALS['cfg']['debugMode'] = false;
				}
				
				// Load the values
				$updateUser = $Q_CheckExisting->fetchRow();
				$userAdmin->primaryKey = $updateUser['us_id'];
				$userAdmin->loadFieldValuesFromForm($insertData);	
				
				// Insert the new user
				$errors = $userAdmin->update();	
	
				if (ss_isOffline()) {
					$GLOBALS['cfg']['debugMode'] = $oldDebugStatus;
				}
	
				if (count($errors)) {
					print ("Could not update {$insertData['us_email']} {$insertData['us_name']['first_name']} {$insertData['us_name']['last_name']}:");
					ss_DumpVar($errors);	
				}	
			} else {				
				print("Could not add {$insertData['us_email']}. Address already exists.");	
			}
		} else {

			if (ss_isOffline()) {
				$oldDebugStatus = $GLOBALS['cfg']['debugMode'];
				$GLOBALS['cfg']['debugMode'] = false;
			}
			
			// Load the values
			$userAdmin->loadFieldValuesFromForm($insertData);	
			
			// Insert the new user
			$errors = $userAdmin->insert();	

			if (ss_isOffline()) {
				$GLOBALS['cfg']['debugMode'] = $oldDebugStatus;
			}

			if (count($errors)) {
				print ("Could not add {$insertData['us_email']} {$insertData['us_name']['first_name']} {$insertData['us_name']['last_name']}:");
				ss_DumpVar($errors);	
			}
		}
		
		$counter++;
		updateAdminPercentageBar($counter/$Q_Users->numRows());
	}

	stopAdminPercentageBar('nothing');
	
?>
