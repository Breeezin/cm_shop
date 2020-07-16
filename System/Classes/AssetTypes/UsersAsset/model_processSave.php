<?php 
	// Load the field set
	//$assetType->fieldSet->fields
	//ss_DumpVar($this);
	//ss_DumpVarDie(ss_JSStringFormat($this->fieldSet->fields[$this->fieldPrefix.'FIELDS']->value));
	if (strlen($this->fieldSet->fields[$this->fieldPrefix.'FIELDS']->value)) {
		$fieldsArray = unserialize($this->fieldSet->fields[$this->fieldPrefix.'FIELDS']->value);
	} else {
		$fieldsArray = array();	
	}
			
	$Q_UserAsset = getRow("SELECT * FROM users LIMIT 1");
	
	
	// these are basic column names from users table.
	// they must always exists in the db table
	$fixedUserColumnNames = array('',"us_id","us_first_name", "us_last_name", "us_email", "us_html_email", "us_user_name", "us_password", "us_details_serialized", "us_activated");
	
	foreach($fieldsArray as $fieldDef) {
		
		// Param all the settings we might have
		ss_paramKey($fieldDef,'uuid','');
		ss_paramKey($fieldDef,'type','');		
		ss_paramKey($fieldDef,'options',array());		
		ss_paramKey($fieldDef,'name','unknown');
								

		// Check the field is existing in the users database table
		$dbFieldName = 'us_'.$fieldDef['uuid'];		
		
		// is not existing 
		if (strlen($fieldDef['uuid']) AND !array_key_exists("$dbFieldName", $Q_UserAsset)) {
			// add a new column called "Us{UUID}"
			$Q_AlterTable = query("ALTER TABLE users ADD $dbFieldName LONGTEXT");								
		}
		
		// updates options into the database		
		$options = '';
		foreach ($fieldDef['options'] as $option) {						
			if (count($option)) {
				// search for existing options
				$Q_Search = query("
					SELECT * FROM select_field_options 
					WHERE sfo_uuid LIKE '{$option['uuid']}'
				");
				if ($Q_Search->numRows()) {
					$Q_Update = query("
						UPDATE select_field_options
						SET sfo_value = '".escape($option['name'])."', sfo_parent_uuid = '{$fieldDef['uuid']}'
						WHERE sfo_uuid LIKE '{$option['uuid']}'	
					");
				} else {
					$Q_Insert = query("
						INSERT INTO select_field_options
						(sfo_value,sfo_uuid,sfo_parent_uuid) VALUES ('".escape($option['name'])."', '{$option['uuid']}','{$fieldDef['uuid']}')
					");
				}				
				$options = ListAppend($options,"'{$option['uuid']}'");				
			}			
		}	
		// delete the removed options from the database
		if (strlen($options)) {
			$Q_Delete = query("
				DELETE FROM select_field_options						
				WHERE 
					sfo_parent_uuid LIKE '{$fieldDef['uuid']}'	
					AND 
					sfo_uuid NOT IN ($options)	
			");
		}
		// now  updates the column names			
		array_push($fixedUserColumnNames, $dbFieldName);
	}	
	
	//array_search($
		
	//ss_DumpVar($fixedUserColumnNames);
		
	foreach($Q_UserAsset as $key => $value) {
		if (!array_search($key, $fixedUserColumnNames)) {	
			if (strpos($key,'0_')) {				
				$Q_AlterTable = query("ALTER TABLE users DROP $key");
				
				
				// delete all options belong to the column(field)
				$Q_DeleteOptions = query("
						DELETE FROM select_field_options						
						WHERE sfo_parent_uuid LIKE '$key'	
				");
			}
		} 
	}
		
	
	
?>
