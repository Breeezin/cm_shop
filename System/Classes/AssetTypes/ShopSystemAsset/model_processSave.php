<?php 
	// Load the field set

	if (strlen($this->fieldSet->fields[$this->fieldPrefix.'ATTRIBUTES']->value)) {
		$fieldsArray = unserialize($this->fieldSet->fields[$this->fieldPrefix.'ATTRIBUTES']->value);
	} else {
		$fieldsArray = array();	
	}
	$assetID = $asset->getID();	
	$initAdd = false;	
	$Q_DataCollection = getRow("SELECT * FROM shopsystem_products LIMIT 1");
	if (!is_array($Q_DataCollection)) {
		$Q_AddNewRow = query("INSERT INTO shopsystem_products (pr_id) VALUES (0)");
		$initAdd = true;	
		$Q_DataCollection = getRow("SELECT * FROM shopsystem_products LIMIT 1");
	}
	// these are basic column names from users table.
	// they must always exists in the db table
	
	$fixedUserColumnNames = array('',"pr_id","pr_name","pr_short","pr_long","PrStockCode",
					"pr_image1_thumb","pr_image1_normal",
					"pr_image1_large","pr_image2_normal","pr_image2_large","pr_image3_normal",
					"pr_image3_large","PrPrice","PrSpecialPrice","PrMemberPrice","PrRRP",
					"pr_ca_id","pr_as_id","pr_sort_order","pr_keywords","pr_deleted");
	foreach($fieldsArray as $fieldDef) {
		
		// Param all the settings we might have
		ss_paramKey($fieldDef,'uuid','');
		ss_paramKey($fieldDef,'type','');		
		ss_paramKey($fieldDef,'options',array());		
		ss_paramKey($fieldDef,'name','unknown');
								

		// Check the field is existing in the users database table
		$dbFieldName = 'Pr'.$fieldDef['uuid'];		
		
		// is not existing 
		if (strlen($fieldDef['uuid']) AND !array_key_exists("$dbFieldName", $Q_DataCollection)) {
			// add a new column called "Us{UUID}"
			$Q_AlterTable = query("ALTER TABLE  shopsystem_products ADD $dbFieldName LONGTEXT");								
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
		if (!array_search($key, $fixedUserColumnNames)) {
			if (strpos($key,'_')) {				
				
				$Q_AlterTable = query("ALTER TABLE shopsystem_products DROP $key");

				$attUUID = substr($key,2);
				$Q_AttSettings = query("
					SELECT ca_attr_setting, ca_id FROM shopsystem_categories 
					WHERE ca_attr_setting LIKE '$attUUID'
				");
				while($Q_AttSetting = $Q_AttSettings->fetchRow()) {
					
					$currentSetting = $Q_AttSetting['ca_attr_setting'];
					$tempArray = ListToArray($currentSetting);
					$newSetting = array();
					foreach($tempArray as $temp) {
						if ($temp != $attUUID) {
							array_push($newSetting,$temp);
						} 
					}
					$newSetting = ArrayToList($newSetting);
					$Q_Update = query("
						UPDATE shopsystem_categories 
						SET ca_attr_setting = '$newSetting' 
						WHERE ca_id = {$Q_AttSetting['ca_id']}					
					");
				}
				
				// delete all options belong to the column(field)
				$Q_DeleteOptions = query("
						DELETE FROM select_field_options						
						WHERE sfo_parent_uuid LIKE '$key'	
				");
			}
		} 
	}		
	
	if ($initAdd) {
		$Q_DeleteTheNewRow = query("DELETE FROM shopsystem_products WHERE pr_id = 0");		
	}
	
	
	if (strlen($this->fieldSet->fields[$this->fieldPrefix.'PRODUCT_OPTIONS']->value)) {
		$fieldsArray = unserialize($this->fieldSet->fields[$this->fieldPrefix.'PRODUCT_OPTIONS']->value);
	} else {
		$fieldsArray = array();	
	}
	$newOpSettings = array();
	foreach($fieldsArray as $fieldDef) {
		
		// Param all the settings we might have
		ss_paramKey($fieldDef,'uuid','');
		ss_paramKey($fieldDef,'type','');		
		ss_paramKey($fieldDef,'options',array());		
		ss_paramKey($fieldDef,'name','unknown');
								
		$newOpSettings[$fieldDef['uuid']] = 1;			
		// updates options into the database		
		$options = '';
		$optionsArray = array();
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
				array_push($optionsArray, $option['uuid']);
			}			
		}	
		// delete the removed options from the database
		if (strlen($options)) {	
			foreach ($optionsArray as $op)	{	
				$Q_DeleteProductOption = query("
					DELETE FROM shopsystem_product_extended_options
					WHERE pro_uuids LIKE '{$fieldDef['uuid']}=$op'
				");
			}
			$Q_Delete = query("
				DELETE FROM select_field_options						
				WHERE 
					sfo_parent_uuid LIKE '{$fieldDef['uuid']}'	
					AND 
					sfo_uuid NOT IN ($options)	
			");
		}
	
	
	}
	ss_paramKey($asset->cereal, 'AST_SHOPSYSTEM_PRODUCT_OPTIONS', '');

	if (strlen($asset->cereal['AST_SHOPSYSTEM_PRODUCT_OPTIONS'])) {
		$originOptionSetting = unserialize($asset->cereal['AST_SHOPSYSTEM_PRODUCT_OPTIONS']);	
	} else {
		$originOptionSetting = array();
	}
			
	foreach ($originOptionSetting as $originFieldDef)	{
		ss_paramKey($originFieldDef,'uuid','');		
		if (!array_key_exists($originFieldDef['uuid'], $newOpSettings)) {
			$Q_AttSettings = query("
				SELECT ca_option_setting, ca_id FROM shopsystem_categories 
				WHERE ca_option_setting LIKE '{$originFieldDef['uuid']}'
			");
			
			while($Q_AttSetting = $Q_AttSettings->fetchRow()) {
				
				$currentSetting = $Q_AttSetting['ca_option_setting'];
				$tempArray = ListToArray($currentSetting);
				$newSetting = array();
					
				foreach($tempArray as $temp) {
					if ($temp != $originFieldDef['uuid']) {
						array_push($newSetting,$temp);
					} 
				}
				$newSetting = ArrayToList($newSetting);
				//ss_DumpVar($newSetting,"new");
				$Q_Update = query("
					UPDATE shopsystem_categories 
					SET ca_option_setting = '$newSetting' 
					WHERE ca_id = {$Q_AttSetting['ca_id']}					
				");
				
			}	
		}		
	}
	//ss_DumpVarDie($originOptionSetting,"or");
?>
