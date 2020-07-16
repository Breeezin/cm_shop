<?php
	$this->param("SearchField",'');
	$this->param("FieldValue", '');
	$this->param("SearchKeywords", '');
	$this->param("SearchExtraFields", '');
	
	ss_paramKey($asset->cereal, $this->fieldPrefix.'GROUPS', array());
	$userGroups = ArrayToList($asset->cereal[$this->fieldPrefix.'GROUPS']);

    if (ss_OptionExists('User Search Shows Full List')){
        $this->ATTRIBUTES['Do_Service'] = 1;
    }

	$Q_Users = null;
	if(array_key_exists("Do_Service", $this->ATTRIBUTES)) {
		$Q_AllowedUsers = query("
				SELECT uug_us_id 
				FROM user_user_groups 
				WHERE uug_ug_id IN ($userGroups) GROUP BY uug_us_id
		");
		$allowedUsers = $Q_AllowedUsers->columnValuesList('uug_us_id');
		$whereSQL = '';
		if (strlen($allowedUsers)) {			
			$whereSQL .= " AND us_id IN ($allowedUsers)";
		} else {
			$whereSQL .= " AND us_id IN (-1)";
		} 
		if (strlen($this->ATTRIBUTES['SearchField'])) {
			$seValue = escape($this->ATTRIBUTES['FieldValue']);
			$whereSQL .= " AND {$this->ATTRIBUTES['SearchField']} LIKE '%{$seValue}%'";
		}
		
		if (strlen($this->ATTRIBUTES['SearchExtraFields']) && strlen($this->ATTRIBUTES['SearchKeywords'])) {
			$keywords = ListToArray($this->ATTRIBUTES['SearchKeywords'],' ');
			$whereSQL .=  " AND  ( 1 = 0 ";		
			foreach (ListToArray($this->ATTRIBUTES['SearchExtraFields']) as $field) {				
								
				foreach($keywords as $word) {
					$word = escape($word);
					$whereSQL .=  "OR $field  LIKE '%{$word}%'";					
				}				
			} 
			$whereSQL .=  ")";					
		}
		//ss_DumpVar($this->ATTRIBUTES, $whereSQL);
	
		$Q_Users = query("
				SELECT * 
				FROM users WHERE 1 $whereSQL
				ORDER BY us_first_name, us_last_name
		");
	}
?>
