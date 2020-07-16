<?php
	$data = array();
	ss_paramKey($asset->cereal, $this->fieldPrefix.'LISTPAGE_LAYOUT', '');
	ss_paramKey($asset->cereal, $this->fieldPrefix.'LISTPAGE_CUSTOMTITLE', '');
	if (ss_optionExists('Data Collection Content Layout Picker')){
		if (strlen($asset->cereal[$this->fieldPrefix.'LISTPAGE_LAYOUT'])) {
			$asset->display->layout = $asset->cereal[$this->fieldPrefix.'LISTPAGE_LAYOUT'];
			if(strlen($asset->cereal[$this->fieldPrefix.'LISTPAGE_CUSTOMTITLE'])) 
				$asset->display->title = $asset->cereal[$this->fieldPrefix.'LISTPAGE_CUSTOMTITLE'];
		}
	} 		
	$data['CategoryName'] = "";	
	if ($CategoryName !== null) {
		$data['CategoryName'] = $CategoryName['sfo_value'];
		$asset->display->title .= " - ".$CategoryName['sfo_value']; 
	}
	
	$data['Titles'] = $tableDisplayFieldTitles;	
	$data['Q_List'] = $Q_List;
	$data['AssetPath'] = $assetPath;
	$data['as_id'] = $assetID;
	$data['WhereSQL'] = $whereSQL;
	$data['OrderBy'] = $orderBys;
	ss_paramKey($asset->cereal, "AST_DATABASE_FIELDS", '');			
	if (strlen($asset->cereal['AST_DATABASE_FIELDS'])) {													
		$fieldsArray = unserialize($asset->cereal['AST_DATABASE_FIELDS']);
	} else {
		$fieldsArray = array();					
	}	
	$data['FieldsArray'] = $fieldsArray;
	$data['Details'] = ss_parseText($asset->cereal[$this->fieldPrefix.'SUBPAGE_CONTENT']);
	
	
	
	$data['MonthlyScheduleOptions'] = $monthlyScheduleOptions;
	$listContent = ss_parseText($asset->cereal[$this->fieldPrefix.'LISTPAGE_CONTENT']);	
	
	$backTo = $this->processTemplate('BackToMain_'.$assetID,$data);	
	$listContent = stri_replace('[Back To Main]',$backTo,$listContent); 
	//ss_DumpVar($listContent, '', true);	
	//$this->useTemplate('ListTable_'.$assetID,$data);	
	if (strpos($listContent,'[List]') !== false) {
		$linkTable = $this->processTemplate('ListTable_'.$assetID,$data);	
		$listContent = stri_replace('[List]',$linkTable,$listContent); 
	}
	
	if (strpos($listContent,'[ListDetail]') !== false) {		
		$detailsTable ='<table width="100%" border="0" cellspacing="0" cellpadding="5">';

		$cols = 0;
		requireClass('DataCollectionAdministration');
		$recordAdmin = new DataCollectionAdministration($assetID);
		while($Q_Details = $Q_List->fetchRow()) {
			$subPageContent = ss_parseText($asset->cereal[$this->fieldPrefix.'SUBPAGE_CONTENT']);
			
			foreach($fieldsArray as $fieldDef) {		
				// Param all the settings we might have
				ss_paramKey($fieldDef,'uuid','');			
				ss_paramKey($fieldDef,'type','');			
				ss_paramKey($fieldDef,'options',array());			
				ss_paramKey($fieldDef,'name','unknown');									
				ss_paramKey($fieldDef,'AppearInList','no');									
				
				
											
				$displayField = "DaCo".$fieldDef['uuid'];
				// Find the value for the field
				if ($fieldDef['type'] == 'MonthlyScheduleField') {
					foreach ($fieldDef['options'] as $key => $values) {
						$monthlyScheduleOptions[$values['uuid']] = "<IMG src=\"Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".$this->getClassName()."/Images/option_".($key + 1).".jpg\">";				
					}
					$monthlyScheduleOptions[0] = "<IMG src=\"Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".$this->getClassName()."/Images/option_0.jpg\">";				
				}
					
					
				if ($recordAdmin->tableTimeStamp == $displayField) {
					$value = formatDateTime($Q_Details[$displayField], "Y-m-d");
				} else {
					if (array_key_exists($displayField, $recordAdmin->fields) AND is_object($recordAdmin->fields[$displayField])) {
						if ($fieldDef['type'] == "MonthlyScheduleField") {
							$value = $recordAdmin->fields[$displayField]->displayValue($Q_Details[$displayField], true, $monthlyScheduleOptions, 12);					
						} else if ($fieldDef['type'] == "PopupUniqueImageField") {
							$imgFile = ss_storeForAsset($assetID)."/".$fieldDef['uuid']."/{$Q_Details[$displayField]}";
							if (file_exists($imgFile) and strlen($Q_Details[$displayField])) {
								$value = "<img src='$imgFile'>";
							} else {
                                if (file_exists('Custom/ContentStore/Templates/DataCollectionAsset/Images/noImage.gif')){
                                    $value = "<img src='Custom/ContentStore/Templates/DataCollectionAsset/Images/noImage.gif'>";
                                } else {
                                    $value = "";
                                }
							}
						} else  {
							$value = $recordAdmin->fields[$displayField]->displayValue($Q_Details[$displayField]);
						} 
					} else {
						$value = $Q_Details[$displayField];
					}
				}									
									
				$subPageContent = stri_replace("[{$fieldDef['name']}]",$value,$subPageContent);
			}
			
			if ($cols == 0) {
				$detailsTable .= '<tr align="left" valign="top">';								
			} 
			$subPageContent = "<TD>".$subPageContent."</TD>";
			if ((($cols+1)%2) == 0) {
				$detailsTable .= $subPageContent."</TR>";
				$cols = 0;
			} else {
				$detailsTable .= $subPageContent;
				$cols++;
			}						
		}
		$listContent .= "</table>";
		
		$listContent = stri_replace('[ListDetail]',$detailsTable,$listContent); 	
	}
	
	print $listContent;
?>
