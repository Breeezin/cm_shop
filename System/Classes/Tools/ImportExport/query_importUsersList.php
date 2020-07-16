<?php
	$this->param('ImportUsersList');
	$this->param('Code');
	$this->param('UserUpdate', '');
	$this->param('Groups');
	
	$counter = 0;
	
	// Get the data
	$Q_Import = query("
		SELECT * FROM import_users
		WHERE imu_id IN (".$this->ATTRIBUTES['ImportUsersList'].")
			AND imu_user_code LIKE '".escape($this->ATTRIBUTES['Code'])."'
	");

	requireOnceClass("UsersAdministration");
	// Make a new users admin class
	$userAdmin = new UsersAdministration();	
	$errorMessages = '';
	$errorCount = 0;
	while ($row = $Q_Import->fetchRow()) {
		$insertData = unserialize($row['imu_user_data']);
		
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
					$errorCount++;
					$errorMessages .= "<p>Could not update {$insertData['us_email']} {$insertData['us_name']['first_name']} {$insertData['us_name']['last_name']}:";
					foreach($errors as $errorList) {
						foreach($errorList as $error) {
							$errorMessages .= "<li>".ss_HTMLEditFormat($error)."</li>";
						}
					}
					$errorMessages .= "</p>";
				}	
			} else {	
				$errorMessages .= "<p>Could not add {$insertData['us_email']} {$insertData['us_name']['first_name']} {$insertData['us_name']['last_name']}.<li>Email address already exists.</li></p>";	
				$errorCount++;
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
				$errorCount++;
				$errorMessages .= "<p>Could not add {$insertData['us_email']} {$insertData['us_name']['first_name']} {$insertData['us_name']['last_name']}:";
				foreach($errors as $errorList) {
					foreach($errorList as $error) {
						$errorMessages .= "<li>".ss_HTMLEditFormat($error)."</li>";
					}
				}
				$errorMessages .= "</p>";
			}
		}		
		
	}
	
	// Delete the rubbish
	$res = query("
		DELETE FROM import_users
		WHERE imu_id IN (".$this->ATTRIBUTES['ImportUsersList'].")
			AND imu_user_code LIKE '".escape($this->ATTRIBUTES['Code'])."'
	");
	
?>