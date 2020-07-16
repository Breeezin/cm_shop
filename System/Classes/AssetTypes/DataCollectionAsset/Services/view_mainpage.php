<?php
	$mainContent = ss_parseText($asset->cereal[$this->fieldPrefix.'MAINPAGE_CONTENT']);
	
	if (!strlen($mainContent)) {
    	location($assetPath."?Service=Show");
	}
	ss_paramKey($asset->cereal, $this->fieldPrefix.'MAINPAGE_LAYOUT', '');
	ss_paramKey($asset->cereal, $this->fieldPrefix.'MAINPAGE_CUSTOMTITLE', '');
	if (ss_optionExists('Data Collection Content Layout Picker') and strlen($asset->cereal[$this->fieldPrefix.'MAINPAGE_LAYOUT'])) {
		$asset->display->layout = $asset->cereal[$this->fieldPrefix.'MAINPAGE_LAYOUT'];
		if(strlen($asset->cereal[$this->fieldPrefix.'MAINPAGE_CUSTOMTITLE'])) 
			$asset->display->title = $asset->cereal[$this->fieldPrefix.'MAINPAGE_CUSTOMTITLE'];
	}
	$data = array();

	$data['Titles'] = $tableDisplayFieldTitles;	
	$data['AssetPath'] = $assetPath;
	$data['as_id'] = $assetID;
	$data['Categories'] = $categories;	
	ss_paramKey($asset->cereal, "AST_DATABASE_FIELDS", '');			
	if (strlen($asset->cereal['AST_DATABASE_FIELDS'])) {													
		$fieldsArray = unserialize($asset->cereal['AST_DATABASE_FIELDS']);
	} else {
		$fieldsArray = array();					
	}	
	$data['FieldsArray'] = $fieldsArray;
	$data['Details'] = ss_parseText($asset->cereal[$this->fieldPrefix.'SUBPAGE_CONTENT']);
	$linkTable = '';
	
	if (strstr($mainContent, '[ShowCategories]')) {
		$linkTable = $this->processTemplate('CategoryList_'.$assetID,$data);	
	}
	$searchTable = '';
	if (strstr($mainContent, '[ShowSearch]')) {		
		if (array_key_exists("Do_Service", $this->ATTRIBUTES)) {
			
			$whereSQL = '';
			$this->param('SearchKeywords','');
			$this->param('SearchFields','');
						
			if (strlen($this->ATTRIBUTES['SearchFields'])) {															
				foreach (ListToArray($this->ATTRIBUTES['SearchFields']) as $field) {
					if (strlen($this->ATTRIBUTES[$field]))	{
						$seValue = escape($this->ATTRIBUTES[$field]);
						$whereSQL .=  " AND $field  LIKE '{$seValue}'";													
					}
				} 														
			}					
			
			$keywords = ListToArray($this->ATTRIBUTES['SearchKeywords'],' ');				
			if (strlen($this->ATTRIBUTES['SearchKeywords'])) {
				$whereSQL .=  " AND  ( 1 = 0 ";						
				foreach($keywords as $word) {
					$nword = escape($word);
					$whereSQL .=  " OR DaCoSearch LIKE '%{$nword}%'";					
				}								
				$whereSQL .=  ")";	
			}				
			if (strlen($orderBySQL)) {
				$tempOrderBy = 'ORDER BY '.$orderBySQL;
			} else {
				$tempOrderBy = '';
			}
			
			$Q_List = query("
				SELECT * 
				FROM DataCollection_$assetID 
				WHERE 
					1 
					$whereSQL
				$tempOrderBy
				
					
			");
			///ss_DumpVarDie($Q_List, $whereSQL);
			$data['Q_List'] = $Q_List;
			$data['BackURL'] = getBackURL();
			
			$searchTable = $this->processTemplate('SearchResult_'.$assetID,$data);	
		} else {
			$searchTable = $this->processTemplate('Search_'.$assetID,$data);	
		}
		
	}
	$mainContent = stri_replace('[ShowCategories]',$linkTable,$mainContent); 
	$mainContent = stri_replace('[ShowSearch]',$searchTable,$mainContent); 
			
	print $mainContent;
?>
