<?php
	ss_RestrictPermission('CanAdministerAtLeastOneAsset');	
	
	$this->display->layout = 'none';

	$this->param('ParentAssetID');
	$this->param('AssetList');
	
	startTransaction();
	
	$Q_CurrentOrder = query("
		SELECT as_id FROM assets
		WHERE as_parent_as_id = ".safe($this->ATTRIBUTES['ParentAssetID'])."
		ORDER BY as_sort_order
	");

	// Make array of current order
	$currentOrder = ',';
	while ($asset = $Q_CurrentOrder->fetchRow()) {
		$currentOrder .= $asset['as_id'].',';
	}

	// Update the order of the defined assets
	$counter = 0;
	foreach(ListToArray($this->ATTRIBUTES['AssetList']) as $assetID) {
		query("
			UPDATE assets
			SET as_sort_order = $counter
			WHERE as_id = ".safe($assetID)."
		");
		$currentOrder = str_replace(",$assetID,",",,",$currentOrder);
		$counter++;
	}
	
	// Update remaining ones
	foreach(ListToArray($currentOrder) as $assetID) {
		query("
			UPDATE assets
			SET as_sort_order = $counter
			WHERE as_id = ".safe($assetID)."
		");
		$counter++;
	}
	
	commit();
	
?>