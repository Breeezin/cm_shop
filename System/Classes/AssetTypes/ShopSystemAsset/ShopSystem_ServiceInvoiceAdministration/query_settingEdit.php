<?php 
	
	$assetID = $this->assetLink;
	$fieldsArray = array();
	$attOptions = array();
	$errors = array();
	$Q_Asset = getRow("SELECT * FROM assets WHERE as_id = $assetID");
	$Q_Cat = getRow("SELECT * FROM {$this->tableName} WHERE {$this->tablePrimaryKey} = {$this->ATTRIBUTES[$this->tablePrimaryKey]}");
	ss_paramKey($Q_Asset,'as_serialized',''); 
	
	if (strlen($Q_Asset['as_serialized'])) {
		$cereal = unserialize($Q_Asset['as_serialized']); 							
		ss_paramKey($cereal, "AST_SHOPSYSTEM_ATTRIBUTES", '');							
		
		if (strlen($cereal['AST_SHOPSYSTEM_ATTRIBUTES'])) {
			$fieldsArray = unserialize($cereal['AST_SHOPSYSTEM_ATTRIBUTES']);
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
	$attSetting = null;
	if (count($attOptions)) {		
		$attSetting =  new MultiCheckFromArrayField (array(
			'name'			=>	'ca_attr_setting',
			'displayName'	=>	'Product Attributes Setting',
			'options'		=> 	$attOptions,
			'note'			=>	null,
			'required'		=>	false,
			'verify'		=>	false,
			'unique'		=>	false,
			'value'			=> 	$Q_Cat['ca_attr_setting'],
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		));
	}
	
	$fieldsArray = array();
	$attOptions = array();		
	ss_paramKey($Q_Asset,'as_serialized',''); 
	
	if (strlen($Q_Asset['as_serialized'])) {
		$cereal = unserialize($Q_Asset['as_serialized']); 							
		ss_paramKey($cereal, "AST_SHOPSYSTEM_PRODUCT_OPTIONS", '');							
		
		if (strlen($cereal['AST_SHOPSYSTEM_PRODUCT_OPTIONS'])) {
			$fieldsArray = unserialize($cereal['AST_SHOPSYSTEM_PRODUCT_OPTIONS']);
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
	$optionSetting = null;
	if (count($attOptions)) {		
		$optionSetting = new MultiCheckFromArrayField (array(
			'name'			=>	'ca_option_setting',
			'displayName'	=>	'Product Options Setting',
			'options'		=> 	$attOptions,
			'note'			=>	null,
			'required'		=>	false,
			'verify'		=>	false,
			'unique'		=>	false,
			'value'			=> 	$Q_Cat['ca_option_setting'],
			'size'	=>	'30',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
		));
	}
	

?>