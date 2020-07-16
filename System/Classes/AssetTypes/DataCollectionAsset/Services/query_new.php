<?php
if (ss_OptionExists('Advanced Data Collection')){
	requireOnceClass("DataCollectionAdministration");
	//ss_DumpVarDie($assetID);
	$DoCoAdmin = new DataCollectionAdministration($assetID);
	$this->ATTRIBUTES['BackURL'] = $assetPath."/Service/New";
	$this->ATTRIBUTES['act'] = $assetPath."/Service/New/Do_Service/Yes";	
	$DoCoAdmin->ATTRIBUTES = $this->ATTRIBUTES;
	
	$errors = array();	
}	
?>
