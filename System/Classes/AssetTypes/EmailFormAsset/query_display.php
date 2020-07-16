<?php

	requireOnceClass('FieldSet');

	ss_paramKey($asset->cereal,$this->fieldPrefix.'FIELDS','');
	
	// Load the field set
	if (strlen($asset->cereal[$this->fieldPrefix.'FIELDS'])) {
		$fieldsArray = unserialize($asset->cereal[$this->fieldPrefix.'FIELDS']);
	} else {
		$fieldsArray = array();	
	}
	
	$fieldSet = new FieldSet();
	$fieldSet->addCustomizedFields($fieldsArray);
	
	foreach($fieldSet->fields as $field) {
		if (strtolower(get_class($fieldSet->fields[$field->name])) == 'passwordfield') {
			$fieldSet->fields[$field->name]->verify = false;
		}
	}
	/*
	foreach($fieldsArray as $fieldDef) {
		
		// Param all the settings we might have
		ss_paramKey($fieldDef,'name','Unknown');
		ss_paramKey($fieldDef,'type','Unknown');
		ss_paramKey($fieldDef,'required',0);
		ss_paramKey($fieldDef,'size','');
		ss_paramKey($fieldDef,'options','');
		ss_paramKey($fieldDef,'defaultValue','');
		ss_paramKey($fieldDef,'uuid','');
						
		if ($fieldDef['type'] != 'Comment') {

			// Assign settings that are the same for each field
			$fieldSettings = array(
				'name'			=>	"F".$fieldDef['uuid'],
				'displayName'	=>	$fieldDef['name'],
				'required'		=>	$fieldDef['required'],
				'defaultValue'	=>	$fieldDef['defaultValue'],
			);
			
			// Assign the 'size' and 'options' values as required for each field type
			switch ($fieldDef['type']) {
				case 'NameField':
					if (strlen($fieldDef['size'])) {
						$fieldSettings['size'] = floor($fieldDef['size']/2);	
					}
					break;
					
				case 'TextField':
				case 'EmailField':
					if (strlen($fieldDef['size'])) {
						$fieldSettings['size'] = $fieldDef['size'];	
					}
					break;
					
				case 'MemoField':
					$fieldSettings['rows'] = 5;	
					if (strlen($fieldDef['size'])) {
						$fieldSettings['cols'] = $fieldDef['size'];	
					}
					break;
				
			}

			// Add the field to the field set
			$fieldSet->addField(new $fieldDef['type']($fieldSettings));
		}		
	}*/
	
?>