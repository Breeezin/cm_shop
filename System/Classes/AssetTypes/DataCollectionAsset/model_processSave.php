<?php
	// Load the field set

	if (strlen($this->fieldSet->fields[$this->fieldPrefix.'FIELDS']->value)) {
		$fieldsArray = unserialize($this->fieldSet->fields[$this->fieldPrefix.'FIELDS']->value);
	} else {
		$fieldsArray = array();	
	}
	$assetID = $asset->getID();	
	$initAdd = false;	
	$Q_DataCollection = getRow("SELECT * FROM DataCollection_$assetID LIMIT 1");
	if (!is_array($Q_DataCollection)) {
		$Q_AddNewRow = query("INSERT INTO DataCollection_{$assetID} (DaCoID) VALUES (0)");
		$initAdd = true;	
		$Q_DataCollection = getRow("SELECT * FROM DataCollection_$assetID LIMIT 1");
	}
	// these are basic column names from users table.
	// they must always exists in the db table
	
	$fixedUserColumnNames = array();
	foreach($fieldsArray as $fieldDef) {
		
		// Param all the settings we might have
		ss_paramKey($fieldDef,'uuid','');
		ss_paramKey($fieldDef,'type','');		
		ss_paramKey($fieldDef,'options',array());		
		ss_paramKey($fieldDef,'name','unknown');
								

		// Check the field is existing in the users database table
		$dbFieldName = 'DaCo'.$fieldDef['uuid'];		
		
		// is not existing 
		if (strlen($fieldDef['uuid']) AND !array_key_exists("$dbFieldName", $Q_DataCollection)) {
			// add a new column called "Us{UUID}"
			$Q_AlterTable = query("ALTER TABLE  DataCollection_$assetID ADD $dbFieldName LONGTEXT");								
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
		
	foreach($Q_DataCollection as $key => $value) {
		if (array_search($key, $fixedUserColumnNames) === false) {		
			if (strpos($key,'_')) {			
				$Q_AlterTable = query("ALTER TABLE  DataCollection_$assetID DROP $key");
				
				
				// delete all options belong to the column(field)
				$Q_DeleteOptions = query("
						DELETE FROM select_field_options						
						WHERE sfo_parent_uuid LIKE '$key'	
				");
			}
		} 
	}		
	
	if ($initAdd) {
		$Q_DeleteTheNewRow = query("DELETE FROM DataCollection_{$assetID} WHERE DaCoID = 0");		
	}
	
?>
