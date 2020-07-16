<?php

	$this->param('as_id');
	$this->param('Type', 'Main');
	
	$Page = getRow("
		SELECT * FROM assets
		WHERE as_id = ".safe($this->ATTRIBUTES['as_id'])."
	");

	$PageDetails = array();
	if (($Page['as_serialized'] !== null) and (strlen($Page['as_serialized']))) {
		$PageDetails = unserialize($Page['as_serialized']);
	}
	
	$LayoutDetails = array();
	if (($Page['as_layout_serialized'] !== null) and (strlen($Page['as_layout_serialized']))) {
		$LayoutDetails = unserialize($Page['as_layout_serialized']);
	}
	
	$result = new Request('Asset.PathFromID',array(
		'as_id'	=>	$this->ATTRIBUTES['as_id'],
	));
	$PagePath = $result->value;
	
	// Now read the file contents
	$inputFileName = $this->assetPathToExportFile($PagePath);
	$inputSubFileName = $this->assetPathToExportFile($PagePath.'-LYT_SUBCONTENT');
	
	if (file_exists(expandPath('Custom/ContentStore/ImportExport/'.$inputFileName))) {
		$content = file_get_contents(expandPath('Custom/ContentStore/ImportExport/'.$inputFileName));
	}
	
	if (file_exists(expandPath('Custom/ContentStore/ImportExport/'.$inputSubFileName))) {
		$subContent = file_get_contents(expandPath('Custom/ContentStore/ImportExport/'.$inputSubFileName));
	}
	
?>