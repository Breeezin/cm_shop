<?php 
	$data = array();
	
	$data['NoUser'] = false;
	if ($Q_Users === null) $data['NoUser'] = true;
	else if (!$Q_Users->numRows()) $data['NoUser'] = true;
	$data['users'] = $Q_Users;
	
			
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
	$this->assetPath = $assetPath;
	$this->assetID = $assetID;
	
	$data['this'] = $this;
	$data['FieldsArray'] = $fieldsArray;
	$data['AssetPath'] = $assetPath;
	$data['SearchField'] = $this->ATTRIBUTES['SearchField'];
	$data['FieldValue'] = $this->ATTRIBUTES['FieldValue'];
	$data['SearchKeywords'] = $this->ATTRIBUTES['SearchKeywords'];
	
	//ss_DumpVar($data);	
	$userDetailContent = ss_parseText($asset->cereal[$this->fieldPrefix.'DETAIL_TEMPLATE']);
	$data['UserDetail'] = $userDetailContent;
	
	/*
	requireClass('DataCollectionAdministration');
	//array_push($tableDisplayFieldTitles, $fieldDef['name']);				
	$recordAdmin = new DataCollectionAdministration($assetID);
	$monthlyScheduleOptions = array();
	$formContent = '';
	if (strpos($userDetailContent,'[Form Detail]')) {
		$formContent = $this->processTemplate('FormDetail_'.$assetID,$data);			
	}
		
	foreach($fieldsArray as $fieldDef) {		
		// Param all the settings we might have
		ss_paramKey($fieldDef,'uuid','');			
		ss_paramKey($fieldDef,'type','');			
		ss_paramKey($fieldDef,'size','');			
		ss_paramKey($fieldDef,'options',array());			
		ss_paramKey($fieldDef,'name','unknown');									
		ss_paramKey($fieldDef,'AppearInList','no');									
		
		
									
		$displayField = "DaCo".$fieldDef['uuid'];
		// Find the value for the field
		if ($fieldDef['type'] == 'MonthlyScheduleField') {
			foreach ($fieldDef['options'] as $key => $values) {
				$monthlyScheduleOptions[$values['uuid']] = "<IMG src=\"Custom/ContentStore/Templates/".$this->getClassName()."/Images/option_".($key + 1).".jpg\">";				
			}
			
			$monthlyScheduleOptions[0] = "<IMG src=\"Custom/ContentStore/Templates/".$this->getClassName()."/Images/option_0.jpg\">";				
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
						
						if (strlen($fieldDef['size'])) {
							$imgProperties = ss_ListToKeyArray($fieldDef['size']);
							if (array_key_exists('s', $imgProperties)) {							
								$windowLink = "href='$imgFile' target='_blank'";
								if (array_key_exists('w', $imgProperties) and array_key_exists('h', $imgProperties)) {
									$windowLink = "href='javascript:void(0);' onClick = \"window.open('$imgFile', '{$fieldDef['uuid']}', 'height={$imgProperties['h']},width={$imgProperties['w']},scrollbars,resizable');void(0);\"";	
								} 
								if (!strpos($imgProperties['s'],'x')) {
									$imgProperties['s'] .='x';
								}
								$value = "<a $windowLink ><img border=0 src='index.php?act=ImageManager.get&Image=".ss_URLEncodedFormat($imgFile)."&Size={$imgProperties['s']}'></a>";	
							} else {
								$value = "<img src='$imgFile'>";
							}
						} else {
							$value = "<img src='$imgFile'>";
						}
					} else {
						$value = "";
					}					
				} else  {
					$value = $recordAdmin->fields[$displayField]->displayValue($Q_Details[$displayField]);
				} 
			} else {
				$value = $Q_Details[$displayField];
			}
		}									
		if(!strlen($value))	$value = '&nbsp;';
		$userDetailContent = stri_replace("[{$fieldDef['name']}]",$value,$userDetailContent);
		$formContent = stri_replace("[{$fieldDef['name']}]",$value,$formContent);				
	}
	*/
	
	$this->useTemplate("UserSearchResult", $data);
?>