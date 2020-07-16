<?php 
	$this->param("Type", "Attributes");
	
	
	$assetID = $this->assetLink;
	$fieldsArray = array();
	$attOptions = array();
	$errors = array();
	$Q_Asset = getRow("SELECT * FROM assets WHERE as_id = $assetID");
	
	ss_paramKey($Q_Asset,'as_serialized',''); 
	
	if (strlen($Q_Asset['as_serialized'])) {
		$cereal = unserialize($Q_Asset['as_serialized']); 
		
		$type = strtoupper($this->ATTRIBUTES['Type']);
		if ($this->ATTRIBUTES['Type'] == 'Options') {
			$type = 'PRODUCT_'.$type;
		}							
		ss_paramKey($cereal, "AST_SHOPSYSTEM_".$type, '');							
		
		if (strlen($cereal['AST_SHOPSYSTEM_'.$type])) {
			$fieldsArray = unserialize($cereal['AST_SHOPSYSTEM_'.$type]);
		} else {
			$fieldsArray = array();					
		}
	}
	
	foreach ($fieldsArray as $field) {				
		ss_paramKey($field,'ShowTo','');
		ss_paramKey($field,'name','');
		ss_paramKey($field,'uuid','');
		if ($field['ShowTo'] == 'selected') {
			$attOptions[$field['name']] = $field['uuid'];
		}
	}	
	$this->param("AsetID", $this->assetLink);
	$this->param("ca_id", '');	
	$allCategoriesResult = $this->queryAllArray(true);	
	$this->display->title = 'Setting Product '.$this->ATTRIBUTES['Type'];
?>