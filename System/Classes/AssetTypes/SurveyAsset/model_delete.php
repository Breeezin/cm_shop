<?php
	$assetID = $asset->getID();
	
	$Q_Survey = getRow("SELECT * FROM Survey_$assetID LIMIT 1");
	if (!is_array($Q_Survey)) {
		$Q_AddNewRow = query("INSERT INTO Survey_{$assetID} (efs_id) VALUES (0)");
		$initAdd = true;	
		$Q_Survey = getRow("SELECT * FROM Survey_$assetID LIMIT 1");
	}
	
	$selectOptions = "";
	
	foreach ($Q_Survey as $key => $value) {
		$selectOptions = ss_comma($selectOptions).str_replace('Su','',$key);		
	}
	if (strlen($selectOptions)) {
		$Q_DeleteOptions = query("DELETE FROM select_field_options WHERE sfo_parent_uuid IN ($selectOptions)");
	}
	$Q_DeleteSurvey = query("DROM TABLE Survey_{$assetID}");
	
?>
