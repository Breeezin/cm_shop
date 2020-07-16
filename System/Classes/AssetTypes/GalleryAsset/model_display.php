<?php
	
	$assetID = $asset->getID();
	
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());
	$directory = ss_storeForAsset($assetID);
	
	ss_paramKey($asset->cereal,$this->fieldPrefix."FORM");	
	
	$images = array();
	if (strlen($asset->cereal[$this->fieldPrefix.'FORM'])) {
		$images = unserialize($asset->cereal[$this->fieldPrefix.'FORM']);
	}
				
	$data = array();
	foreach (array("FORM","THUMBNAIL_HEIGHT","THUMBNAIL_WIDTH","IMAGES_PER_ROW","ROWS_PER_PAGE","POPUP_HEIGHT","POPUP_WIDTH") as $field) {
		ss_paramKey($asset->cereal,$this->fieldPrefix.$field);	
		$data[$field] = $asset->cereal[$this->fieldPrefix.$field];
	}
	
	$this->param('Service','List');
			
	foreach(array('query','model','view') as $prefix) {
		$name = $prefix.'_'.strtolower($this->ATTRIBUTES['Service']).'.php';
				
		if (file_exists(dirname(__FILE__).'/Services/'.$name)) include("Services/".$name);
	}
	
?>