<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'USERGROUPS',array());

	$this->param('Email',null);
	$this->param('first_name',null);
	$this->param('last_name',null);
	$this->param('user_groups',array());
	$this->param('HTML',null);	
	
	$success = false;	
	$errors = array();
	
	
	$selectedFieldDefs = array();
	$selectedReqFields = array();
	$fieldSet = null;
	ss_paramKey($asset->cereal,$this->fieldPrefix.'FORMFIELDS','');
	
	if (ss_optionExists('Newsletter Advanced Subscribe Form')) {
		// Load the field set
			if (!is_array($asset->cereal[$this->fieldPrefix.'FORMFIELDS'])) {
				$selectedFormFields = unserialize($asset->cereal[$this->fieldPrefix.'FORMFIELDS']);
			} else  {
				$selectedFormFields = $asset->cereal[$this->fieldPrefix.'FORMFIELDS'];
			}
			$Q_UserAsset = getRow("SELECT * FROM assets WHERE as_type LIKE 'users'");
			ss_paramKey($Q_UserAsset,'as_serialized',''); 
						
			if (strlen($Q_UserAsset['as_id']) AND strlen($Q_UserAsset['as_serialized'])) {
				$cereal = unserialize($Q_UserAsset['as_serialized']);			
				ss_paramKey($cereal,'AST_USER_FIELDS','');
				if (strlen($cereal['AST_USER_FIELDS'])) {
					$fieldsArray = unserialize($cereal['AST_USER_FIELDS']);
				} else {
					$fieldsArray = array();	
				}
			} else {
				$fieldsArray = array();	
			}
				
			foreach($fieldsArray as $fieldDef) {		
				// Param all the settings we might have
				ss_paramKey($fieldDef,'uuid','');			
				ss_paramKey($fieldDef,'required',false);			
				ss_paramKey($fieldDef,'name','unknown');	
				if ($fieldDef['uuid'] == 'Name') {
					$fieldDef['required'] = true;
				}
				if ($fieldDef['uuid'] == 'Email') {
					$fieldDef['unique'] = false;
				}
				
				if (array_search($fieldDef['uuid'], $selectedFormFields) !== false) {								
					array_push($selectedFieldDefs,  $fieldDef);
					if ($fieldDef['required']) {
						array_push($selectedReqFields,  'Us'.$fieldDef['uuid']);
					}
				}
			}												
		requireOnceClass('FieldSet');
		$fieldSet = new FieldSet();		
		$fieldSet->formName = "SubForm";
		$fieldSet->addCustomizedFields($selectedFieldDefs, 'us_');
		//ss_DumpVar($fieldSet);
	}
	
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		if ($this->ATTRIBUTES['DoAction'] == 'Subscribe') {
			
			if (ss_optionExists('Newsletter Advanced Subscribe Form')) {
				
				$fieldSet->loadFieldValuesFromForm($this->ATTRIBUTES);	
				
				$errors = $fieldSet->validate();		
				
				
			} else {
				if (!strlen($this->ATTRIBUTES['Email'])) {
					$errors = array(array('Please define the email address.'));
				}
				
			}
			//ss_DumpVarDie($errors, '', true);
			
			if (count($errors) == 0) {	
				
				// clean up the html value so that we have a null
				if ($this->ATTRIBUTES['HTML'] != 1) {
					$this->ATTRIBUTES['HTML'] = null;	
				}
					
				$joinGroups = array();
				foreach($this->ATTRIBUTES['user_groups'] as $wantedGroup) {
					foreach($asset->cereal['AST_SUBSCRIBE_USERGROUPS'] as $allowedGroup) {
						if ($wantedGroup == $allowedGroup) {
							array_push($joinGroups,$allowedGroup); 	
						}	
					}
				}
				//ss_DumpVar($this->ATTRIBUTES, '', true);
				if (ss_optionExists('Newsletter Advanced Subscribe Form')) {
					$email = $this->ATTRIBUTES['us_email'];
				} else {
					$email = $this->ATTRIBUTES['Email'];
				}
				$Q_CheckExisting = query("
					SELECT * FROM users
					WHERE us_email LIKE '".escape($email)."'
				");
				
				//ss_DumpVar($Q_CheckExisting->numRows(),'nu', true);
				if ($Q_CheckExisting->numRows()) {
										
					$row = $Q_CheckExisting->fetchRow();
					if (count($joinGroups)) {
					// Delete any existing subscriptions
						$Q_DeleteExisting = query("
							DELETE FROM user_user_groups
							WHERE uug_us_id = {$row['us_id']}
								AND uug_ug_id IN (".ArrayToList($joinGroups).")
						");
					}
					// Add the user to all the groups they wanted to be in
					foreach ($joinGroups as $group) {
						$Q_InsertNew = query("
							INSERT INTO user_user_groups
								(uug_us_id, uug_ug_id)
							VALUES
								({$row['us_id']}, $group)
						");
					}
					if (ss_optionExists('Newsletter Advanced Subscribe Form')) {
						$passingATTs = $this->ATTRIBUTES;
						$passingATTs['us_html_email'] = $this->ATTRIBUTES['HTML'];
						$passingATTs['user_groups'] = $joinGroups;
						$passingATTs['ValidateFields'] = $selectedReqFields;							
						$passingATTs['us_id'] = $row['us_id'];							
						$this->ATTRIBUTES['us_id'] = $row['us_id'];							
															
						$temp = new Request("Security.Sudo",array('Action'=>'start'));	
						$result = new Request("UsersAdministration.Update",$passingATTs);
						$temp = new Request("Security.Sudo",array('Action'=>'stop'));	
						if (!is_array($result->value)) {
							$success = true;
						} else {
							$errors = $result->value;	
							ss_DumpVar($passingATTs, 'update', true);
							ss_DumpVarDie($errors, 'update', true);
						}
						
					} else {	
						// Might as well update their name details at the same time
						$Q_UpdateName = query("
							UPDATE users
							SET 
								us_first_name = '".escape($this->ATTRIBUTES['first_name'])."',
								us_last_name = '".escape($this->ATTRIBUTES['last_name'])."',
								us_html_email	= ".($this->ATTRIBUTES['HTML']===null?'null':'1').",
								us_no_spam = NULL
							WHERE us_id = {$row['us_id']}
						");
						$success = true;
					}									
				} else {
					$temp = new Request("Security.Sudo",array('Action'=>'start'));	
					if (ss_optionExists('Newsletter Advanced Subscribe Form')) {
						$passingATTs = $this->ATTRIBUTES;
						$passingATTs['us_html_email'] = $this->ATTRIBUTES['HTML'];
						$passingATTs['user_groups'] = $joinGroups;
						$passingATTs['ValidateFields'] = $selectedReqFields;																
						
						$result = new Request("UsersAdministration.Insert",$passingATTs);
						if (!is_array($result->value)) {
							$success = true;
						} else {
							$errors = $result->value;	
							ss_DumpVar($passingATTs, 'insert', true);
							ss_DumpVarDie($errors, 'insert', true);
						
						}
						
					} else {
						$result = new Request("UsersAdministration.Insert",array(
							'us_name'	=>	array(
								'first_name'	=>	$this->ATTRIBUTES['first_name'],
								'last_name'	=>	$this->ATTRIBUTES['last_name'],
							),
							'us_email'		=>	$this->ATTRIBUTES['Email'],
							'us_html_email'	=>	$this->ATTRIBUTES['HTML'],
							'user_groups'	=>	$joinGroups,
							'ValidateFields'=>	array('us_name','us_email','us_html_email','user_groups',),
						));
					}
					if (!is_array($result->value)) {
						$success = true;
					} else {
						$errors = $result->value;	
					}
					//ss_log_message_r($result);
							
					$temp = new Request("Security.Sudo",array('Action'=>'stop'));	
				}
			}
		} else {
			$this->param('Email');
			$this->param('user_groups',array());
			
			$success = false;
			
			$unjoinGroups = array();
			foreach($this->ATTRIBUTES['user_groups'] as $wantedGroup) {
				foreach($asset->cereal['AST_SUBSCRIBE_USERGROUPS'] as $allowedGroup) {
					if ($wantedGroup == $allowedGroup) {
						array_push($unjoinGroups,$allowedGroup); 	
					}	
				}
			}
	
			if (count($unjoinGroups)) {
				$Q_CheckExisting = query("
					SELECT * FROM users
					WHERE us_email LIKE '".escape($this->ATTRIBUTES['Email'])."'
				");
				
				if ($Q_CheckExisting->numRows()) {
					// oops, already in the db..
					$row = $Q_CheckExisting->fetchRow();
					
					// this ensures the user gets no more spam
					$Q_Unsubscribe = query("
						UPDATE users
						SET us_no_spam = 1
						WHERE us_id = {$row['us_id']}
					");
					// and remove from any mailling list groups
					$Q_DeleteFromGroups = query("
						DELETE FROM user_user_groups
						WHERE uug_us_id = {$row['us_id']}
							AND uug_ug_id IN (".ArrayToList($unjoinGroups).")
					");
					$success = true;
				} else {
					$success = true;
				}
			} else {
				if (ss_optionExists('Duty Free FB TI Mailling List')) {
					$Q_CheckExisting = query("
						SELECT * FROM users, Members
						WHERE us_email LIKE '".escape($this->ATTRIBUTES['Email'])."'
							AND Meuug_us_id = us_id
					");
					
					while ($row = $Q_CheckExisting->fetchRow()) {					
						// this ensures the user gets no more spam
						$Q_Unsubscribe = query("
							UPDATE users
							SET us_no_spam = 1
							WHERE us_id = {$row['us_id']}
						");
												
					} 								
				}
				$success = true;
			}
		}
	}
	//ss_log_message_r($asset->cereal);
?>
