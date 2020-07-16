<?php

	$this->paramLen('DaCoID');
	
	$this->param('BackURL','');
	
	
	$asset->display->layout = "subpage";
	
	ss_paramKey($asset->cereal, "AST_DATABASE_FIELDS", '');			
	
	if (strlen($asset->cereal['AST_DATABASE_FIELDS'])) {													
		$fieldsArray = unserialize($asset->cereal['AST_DATABASE_FIELDS']);
	} else {
		$fieldsArray = array();
	}

	$Q_Details = getRow("
		SELECT * FROM DataCollection_$assetID
		WHERE DaCoID = ".safe($this->ATTRIBUTES['DaCoID'])."
	");

?>
