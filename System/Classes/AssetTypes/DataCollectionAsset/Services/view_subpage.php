<?php
	ss_paramKey($asset->cereal, $this->fieldPrefix.'SUBPAGE_LAYOUT', '');
	ss_paramKey($asset->cereal, $this->fieldPrefix.'SUBPAGE_CUSTOMTITLE', '');
	if (ss_optionExists('Data Collection Content Layout Picker') and strlen($asset->cereal[$this->fieldPrefix.'SUBPAGE_LAYOUT'])) {
		$asset->display->layout = $asset->cereal[$this->fieldPrefix.'SUBPAGE_LAYOUT'];

		if(strlen($asset->cereal[$this->fieldPrefix.'SUBPAGE_CUSTOMTITLE']))
			$asset->display->title = $asset->cereal[$this->fieldPrefix.'SUBPAGE_CUSTOMTITLE'];
	}
	if (array_key_exists('Layout', $this->ATTRIBUTES) and strlen($this->ATTRIBUTES['Layout'])) {
		$asset->display->layout = $this->ATTRIBUTES['Layout'];
	}
	$subPageContent = ss_parseText($asset->cereal[$this->fieldPrefix.'SUBPAGE_CONTENT']);
	requireClass('DataCollectionAdministration');
	//array_push($tableDisplayFieldTitles, $fieldDef['name']);				
	$recordAdmin = new DataCollectionAdministration($assetID);
	$monthlyScheduleOptions = array();
	$formContent = '';
	if (strpos($subPageContent,'[Form Detail]') !== false) {
		$formContent = $this->processTemplate('FormDetail_'.$assetID, $data);
    }
	$fieldValues = array();	
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
				} else if ($fieldDef['type'] == "NameField") {					
						if($displayField == 'us_name') {
							$value = $Q_Details['us_first_name'].' '.$Q_Details['us_last_name'];										
						} else {
					  		$value = $recordAdmin->fields[$displayField]->displayValue($Q_Details[$displayField]);										
						}
					} else if (strtolower(ListFirst($fieldDef['type'],'_')) == 'datacollectionfield' || strtolower(ListFirst($fieldDef['type'],'_')) == 'datacollectionmultifield') {					
					  	$value = $recordAdmin->fields[$displayField]->displayFullDetails($Q_Details[$displayField]);					
					} else  {
						$value = $recordAdmin->fields[$displayField]->displayValue($Q_Details[$displayField]);						
					} 
			} else {
				$value = $Q_Details[$displayField];
			}
		}	
		$fieldValues[$fieldDef['uuid']]	= $value;
		if(!strlen($value))	$value = '&nbsp;';
		$subPageContent = stri_replace("[{$fieldDef['name']}]",$value,$subPageContent);
		$formContent = stri_replace("[{$fieldDef['name']}]",$value,$formContent);				
	}

	if (strpos($subPageContent,'[Form Detail]')  !== false) {		
		$subPageContent = stri_replace('[Form Detail]',$formContent,$subPageContent); 
	}

	if (strpos($subPageContent,'[BackURL]') !== false) {
		$newData = array('BackURL'=> $this->ATTRIBUTES['BackURL']);
		$backTo = $this->processTemplate('BackURL_'.$assetID,$newData);	
		
		$subPageContent = stri_replace('[BackURL]',$backTo,$subPageContent);	
	}
	if (strpos($subPageContent,'[Print]') !== false) {
		$newData = array('CurrentURL'=> getBackURL());
		$backTo = $this->processTemplate('Print_'.$assetID,$newData);	
		
		$subPageContent = stri_replace('[Print]',$backTo,$subPageContent);	
	}
	
	if (strstr($subPageContent, '[Custom Detail]')) {
		
		$newData = array(
				'BackURL' => getBackURL(), 
				'FieldValues' => $fieldValues, 
				'DaCoID' => $Q_Details['DaCoID'],				
				'this' => $this,				
		);
		
		$customDetails = $this->processTemplate('CustomDetail_'.$assetID,$newData);	
		
		$subPageContent = stri_replace('[Custom Detail]',$customDetails,$subPageContent);	
	}

    if (ss_optionExists('Data Collection Detail Groups')){
        $allowedGroup = ss_optionExists('Data Collection Detail Groups');
        if (array_key_exists($allowedGroup, $_SESSION['User']['user_groups'])){
            print $subPageContent;
        } else {
            print "You must be a full member to access these details.";
        }
    }else{
        print $subPageContent;
    }

?>
