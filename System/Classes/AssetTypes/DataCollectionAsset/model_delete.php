<?php 
	$assetID = $asset->getID();
	
	$Q_DataCollection = getRow("SELECT * FROM DataCollection_$assetID LIMIT 1");
	if (!is_array($Q_DataCollection)) {
		$Q_AddNewRow = query("INSERT INTO DataCollection_{$assetID} (DaCoID) VALUES (0)");
		$initAdd = true;	
		$Q_DataCollection = getRow("SELECT * FROM DataCollection_$assetID LIMIT 1");
	}
	
	$selectOptions = "";
	
	foreach ($Q_DataCollection as $key => $value) {
		$selectOptions = ss_comma($selectOptions).str_replace('DaCo','',$key);		
	}
	if (strlen($selectOptions)) {
		$Q_DeleteOptions = query("DELETE FROM select_field_options WHERE sfo_parent_uuid IN ($selectOptions)");
	}
	$Q_DeleteDataCollection = query("DROM TABLE DataCollection_{$assetID}");
	
?>