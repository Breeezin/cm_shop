<?php 

	ss_paramKey($asset->cereal,$this->fieldPrefix.'LAYOUT', '');
	if (strlen($asset->cereal[$this->fieldPrefix.'LAYOUT'])) {
		$asset->display->layout = $asset->cereal[$this->fieldPrefix.'LAYOUT'];
	}	

	$this->ATTRIBUTES['us_id'] = ss_getUserID();
	$this->ATTRIBUTES['BackURL'] = $assetPath;
	
?>
