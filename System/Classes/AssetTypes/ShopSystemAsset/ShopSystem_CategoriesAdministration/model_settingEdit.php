<?php 
	if (array_key_exists("DoAction", $this->ATTRIBUTES)) {
		$updateAttField = '';
		$updateOpField = '';
		
		if ($attSetting != null) {
			$attSetting->value = $this->ATTRIBUTES['ca_attr_setting'];
			$errors = array_merge($errors, $attSetting->validate());			
			$updateAttField = "ca_attr_setting = ".$attSetting->valueSQLText()."";
		}
		if ($optionSetting != null) {
			$optionSetting->value = $this->ATTRIBUTES['ca_option_setting'];
			$errors = array_merge($errors, $optionSetting->validate());			
			$updateOpField = "ca_option_setting = ".$optionSetting->valueSQLText()."";
		}
		//ss_DumpVarDie($this->ATTRIBUTES);
		if (!count($errors)){
			if (strlen($updateAttField)) {
				/*
				$this->param('AttApplyToSubs', '');
				$this->param('OpApplyToSubs', '');
				
				$this->param("as_id", $this->assetLink);			
				$subs = $this->queryAllArray();
				$subCaIDs = "";
				*/
				$whereAtt = "{$this->tablePrimaryKey} = {$this->ATTRIBUTES[$this->tablePrimaryKey]}";
				/*
				foreach($subs as $sub) {
					$subCaIDs = ListAppend($subCaIDs, $sub);
				}
				if (strlen($this->ATTRIBUTES['AttApplyToSubs'])) {
					$whereAtt = "{$this->tablePrimaryKey} IN ($subCaIDs)";						
				}
				if (strlen($this->ATTRIBUTES['OpApplyToSubs'])) {
					$whereOp = "{$this->tablePrimaryKey} IN ($subCaIDs)";						
				}
				*/
				$Q_AttUpdate = query("UPDATE {$this->tableName} SET $updateAttField WHERE $whereAtt");
			
			}
			if (strlen($updateOpField)) {				
				$whereOp = "{$this->tablePrimaryKey} = {$this->ATTRIBUTES[$this->tablePrimaryKey]}";
								
				$Q_OpUpdate = query("UPDATE {$this->tableName} SET $updateOpField WHERE $whereOp");					
			}					
			
			location($this->ATTRIBUTES['BackURL']);
		}
	}
?>