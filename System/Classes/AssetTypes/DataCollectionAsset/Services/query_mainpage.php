<?php 
	
	ss_paramKey($asset->cereal, "AST_DATABASE_FIELDS", '');			
	$tableDisplayFieldTitles = array();
	$monthlyScheduleOptions = array();

	if (strlen($asset->cereal['AST_DATABASE_FIELDS'])) {													
		$fieldsArray = unserialize($asset->cereal['AST_DATABASE_FIELDS']);
	} else {
		$fieldsArray = array();					
	}
	
	$categories = array();
	$orderBys = array();
	$orderBySQL = '';
	foreach($fieldsArray as $fieldDef) {		
		// Param all the settings we might have
		ss_paramKey($fieldDef,'uuid','');			
		ss_paramKey($fieldDef,'type','');			
		ss_paramKey($fieldDef,'name','unknown');									
		ss_paramKey($fieldDef,'options',array());									
		ss_paramKey($fieldDef,'AppearInList','no');									
		ss_paramKey($fieldDef,'CategoryBy','no');									
//		if ($fieldDef['AppearInList'] == 'yes') {			
			if ($fieldDef['CategoryBy'] == 'yes' and ($fieldDef['type'] == "RadioFromArrayField" || $fieldDef['type'] == "SelectFromArrayField" || $fieldDef['type'] == "MultiCheckFromArrayField" || ListFirst($fieldDef['type'],'_') == "DataCollectionField") ) {				
				if($fieldDef['type'] == "SelectFromArrayField" or $fieldDef['type'] == "RadioFromArrayField" or $fieldDef['type'] == "MultiCheckFromArrayField") {
					$options = array();
					foreach ($fieldDef['options'] as $option) {						
						if (count($option)) {
							$options[$option['uuid']] = $option['name'];
						}			
					}	
				} else {
					$options = array();
					$dataAssetID = ListLast($fieldDef['type'],'_');
					$Q_DataCollection = getRow("SELECT * FROM assets WHERE as_id = ".$dataAssetID.".");					
					$cereal = unserialize($Q_DataCollection['as_serialized']); 							
					
					ss_paramKey($cereal, "AST_DATABASE_FIELDS", '');							
					ss_paramKey($cereal, "AST_DATABASE_SUBPAGE_CONTENT", '');												
					if (strlen($cereal['AST_DATABASE_FIELDS'])) {
						$dataFieldsArray = unserialize($cereal['AST_DATABASE_FIELDS']);
					} else {
						$dataFieldsArray = array();					
					}

									
					if (count($dataFieldsArray)) {						
						$Q_Options = query("
								SELECT DaCoID, DaCo{$dataFieldsArray[0]['uuid']} 
								FROM DataCollection_$dataAssetID 
								WHERE DaCo{$dataFieldsArray[0]['uuid']} IS NOT NULL");
												
						while($option = $Q_Options->fetchRow()) {
							$options[$option['DaCoID']] = $option["DaCo{$dataFieldsArray[0]['uuid']}"]; 
						}																		
					}	
				}
		
				$categories[$fieldDef['uuid']] = array("Name"=>$fieldDef['name'],'Options'=>$options);
			}
			if ($fieldDef['AppearInList'] == 'yes') {
				if (!($fieldDef['type'] == 'MonthlyScheduleField' and is_array($assetLink)) ) {																			
					$orderBySQL .= ss_comma($orderBySQL);
					if ($fieldDef['type'] == 'DateField') {
						$orderBys["DaCo".$fieldDef['uuid']] = "DESC";
						$orderBySQL .= "DaCo".$fieldDef['uuid']." DESC";	
					} else {
						$orderBys["DaCo".$fieldDef['uuid']] = "ASC";	
						$orderBySQL .= "DaCo".$fieldDef['uuid']." ASC";	
					}															
				}
				
			}
//		}		
	}
	
?>