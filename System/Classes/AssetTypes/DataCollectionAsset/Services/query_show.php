<?php
	$this->param("Category","");
	$this->param("By","");
	
	ss_paramKey($asset->cereal, "AST_DATABASE_FIELDS", '');			
	$tableDisplayFieldTitles = array();
	$monthlyScheduleOptions = array();

	if (strlen($asset->cereal['AST_DATABASE_FIELDS'])) {													
		$fieldsArray = unserialize($asset->cereal['AST_DATABASE_FIELDS']);
	} else {
		$fieldsArray = array();					
	}
	$orderBys = 'DaCoSortOrder ASC';

	foreach($fieldsArray as $fieldDef) {		
		// Param all the settings we might have
		ss_paramKey($fieldDef,'uuid','');			
		ss_paramKey($fieldDef,'type','');			
		ss_paramKey($fieldDef,'name','unknown');									
		ss_paramKey($fieldDef,'options',array());									
		ss_paramKey($fieldDef,'AppearInList','no');									
		ss_paramKey($fieldDef,'CategoryBy','no');									
		if ($fieldDef['AppearInList'] == 'yes') {			
			if ($fieldDef['type'] == 'MonthlyScheduleField') {
				//$tempTitle = '<table width="100%">';
				$tempTitle = '';
				// make an initial array for months to display		
				$today = getdate(); 
				$year = $today['year'];						
				$howmany = 6;				
				$monthCounter = 1;				
				
				for($i = $today['mon']; $i <= 12; $i++) {								
					if ($monthCounter > $howmany) break;
					$tempTitle .= "<TD class=\"mainTable\">".date('M', mktime(0,0,0,$i,1,$year))."</TD>";															
					$monthCounter++;					
				}
								
				if($today['mon'] > 1) {
					$nextYear = $year + 1;										
					for($i = 1; $i < $today['mon']; $i++) {						
						if ($monthCounter > $howmany) break;																
						$tempTitle .= "<TD class=\"mainTable\">".date('M', mktime(0,0,0,$i,1,$nextYear))."</TH>";															
						$monthCounter++;						
					}										
				}
				
				//$tempTitle .= '</table>';
				array_push($tableDisplayFieldTitles, $tempTitle);				
			} else {
				array_push($tableDisplayFieldTitles, $fieldDef['name']);				
			
			}
			
				$orderBys .= ss_comma($orderBys)."DaCo".$fieldDef['uuid']."";
			
		
		}
		if ($fieldDef['type'] == 'MonthlyScheduleField') {
			foreach ($fieldDef['options'] as $key => $values) {
				$monthlyScheduleOptions[$values['uuid']] = "<IMG src=\"Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".$this->getClassName()."/Images/option_".($key + 1).".jpg\">";				
			}
			$monthlyScheduleOptions[0] = "<IMG src=\"Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}".$this->getClassName()."/Images/option_0.jpg\">";				
		}
	}

	$whereSQL = '';
	$CategoryName = null;
	if (strlen($this->ATTRIBUTES['Category'])) {
		if (strlen($this->ATTRIBUTES['By'])) {
			$seValue = escape($this->ATTRIBUTES['By']);
			$whereSQL .= " AND DaCo".$this->ATTRIBUTES['Category']." LIKE '$seValue'";
			$CategoryName = getRow("SELECT * FROM select_field_options WHERE sfo_uuid LIKE '$seValue'");
		}				
	}
	$customOrderBys = ss_optionExists('Data Collection List Sort By');
	if (strlen($customOrderBys)) {
		$orderBys = $customOrderBys;	
	}

    //added 15.08.05
    if(ss_OptionExists('Advanced Data Collection')){
        $whereSQL = ' AND DaCoApproved = 1';
    }

	$Q_List = query("
		SELECT * FROM DataCollection_$assetID
		WHERE 1 
		$whereSQL
		ORDER BY $orderBys
	");
	
?>
