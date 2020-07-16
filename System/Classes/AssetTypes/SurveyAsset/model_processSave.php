<?php
	// Load the field set

	if (strlen($this->fieldSet->fields[$this->fieldPrefix.'FIELDS']->value)) {
		$fieldsArray = unserialize($this->fieldSet->fields[$this->fieldPrefix.'FIELDS']->value);
	} else {
		$fieldsArray = array();
	}

	$assetID = $asset->getID();
	$initAdd = false;	
	$Q_Survey = getRow("SELECT * FROM Survey_$assetID LIMIT 1");
	if (!is_array($Q_Survey)) {
		$Q_AddNewRow = query("INSERT INTO Survey_{$assetID} (efs_id) VALUES (0)");
		$initAdd = true;	
		$Q_Survey = getRow("SELECT * FROM Survey_$assetID LIMIT 1");
	}

	$fixedUserColumnNames = array();
	foreach($fieldsArray as $fieldDef) {
		// Param all the settings we might have
		ss_paramKey($fieldDef,'uuid','');
		ss_paramKey($fieldDef,'type','');
		ss_paramKey($fieldDef,'options',array());
		ss_paramKey($fieldDef,'name','unknown');
								

		// Check the field is existing in the users database table
		$dbFieldName = 'Su'.$fieldDef['uuid'];
		
		// is not existing
		if (strlen($fieldDef['uuid']) ) {
            if ( !array_key_exists("$dbFieldName", $Q_Survey)) {
    			// add a new column called "Us{UUID}"
    			$Q_AlterTable = query("ALTER TABLE  Survey_$assetID ADD $dbFieldName LONGTEXT");
            }
           // must always check just incase field type is changed.
           if ($fieldDef['type'] == 'RadioWithOtherFromArrayField') {
                if ( array_key_exists($dbFieldName."_otherValue", $Q_Survey) == false ) {
                    // adding extra field if "Radio With Other" field
        			$Q_AlterTable = query("ALTER TABLE  Survey_$assetID ADD ".$dbFieldName."_otherValue LONGTEXT");
                }
            }
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
		
	foreach($Q_Survey as $key => $value) {
		if (array_search($key, $fixedUserColumnNames) === false) {		
			if (strpos($key,'_') ) {
                if (strpos($key,'_otherValue') === false ) {
    				$Q_AlterTable = query("ALTER TABLE  Survey_$assetID DROP $key");
    				// delete all options belong to the column(field)
    				$Q_DeleteOptions = query("
    						DELETE FROM select_field_options
    						WHERE sfo_parent_uuid LIKE '$key'
    				");
                }
			}
		} 
	}		
	
	if ($initAdd) {
		$Q_DeleteTheNewRow = query("DELETE FROM Survey_{$assetID} WHERE efs_id = 0");		
	}

?>
