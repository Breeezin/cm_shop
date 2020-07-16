<?php

	// Restrict to the admin groups
	ss_paramKey($asset->cereal,$this->fieldPrefix.'UPLOAD_USERGROUPS',array());
	ss_RestrictPermission('IsInAnyOfTheseGroups',null,$asset->cereal[$this->fieldPrefix.'UPLOAD_USERGROUPS']);

	requireOnceClass("MediaReleasesAdministration");
	$asset->display->layout = 'glasstower';
	$mediaReleasesAdmin = new MediaReleasesAdministration(false);
	$this->ATTRIBUTES['BackURL'] = $assetPath."?Service=New";
	$this->ATTRIBUTES['act'] = $assetPath."?Service=New&Do_Service=Yes";	
	$mediaReleasesAdmin->ATTRIBUTES = $this->ATTRIBUTES;
	
	$errors = array();	
	
?>