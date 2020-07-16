<?php 
	$this->param("as_id");
	$this->param("AssetPath");
	
	$Q_Asset  = getRow("SELECT as_name, as_parent_as_id FROM assets WHERE as_id = {$this->ATTRIBUTES['as_id']}");
	
	$assetName = ss_newAssetName($Q_Asset['as_name'], $Q_Asset['as_parent_as_id']);
	$Q_RestoreAsset = query("UPDATE assets SET as_deleted=0 WHERE as_id = {$this->ATTRIBUTES['as_id']}");
	
	$message = "{$Q_AssetPath} was sucessfully stored.";
	
	if ($assetName != $Q_Asset['as_name']) {
		$message = "{$Q_AssetPath} was stored and renamed to $assetName";
	}
	
	location("index.php?act=RecycleBin.AssetList&Message=".ss_URLEncodedFormat($message));
?>