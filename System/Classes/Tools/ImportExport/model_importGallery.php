<?php 
	$this->param('as_name');
	// New image asset
	$result = new Request('Asset.Add',array(
		'as_name'	=>	$this->ATTRIBUTES['as_name'],
		'as_type'	=>	'Gallery',
		'as_appear_in_menus'	=>	0,
		'as_parent_as_id'	=>	ss_systemAsset('index.php'),
		'DoAction'	=>	1,
		'AsService'	=>	true,
	));
	
	if ($result->value !== null) {
		$newAssetID = $result->value;
		
		$assetCereal = null;	
		require("Custom/ContentStore/ImportExport/{$this->ATTRIBUTES['as_name']}.php");									
		if ($assetCereal === null) {
			print "Reading {$this->ATTRIBUTES['as_name']} has failed. there could be syntax error";
		} else {
			$Q_Asset = getRow("SELECT * FROM assets WHERE as_id = $newAssetID");
			// Figure out a file name for the image
			$assetName = $Q_Asset['as_name'];
		
			print  "Gallery Asset ID is ".$newAssetID."<BR>";
			print  "Gallery Asset Name is ".$assetName."<BR>";
			// Update the asset details
			$assetDetailsCereal = serialize($assetCereal);
			$result = query("
				UPDATE assets
				SET as_serialized = '".escape($assetDetailsCereal)."'
				WHERE as_id = {$newAssetID}
			");
			
			// Copy the image to the asset store
			$assetStore = ss_storeForAsset($newAssetID);
			ss_copyDirectory("Custom/ContentStore/ImportExport/".$this->ATTRIBUTES['as_name'], $assetStore);
		}
	} else {
			print("Gallery import failed. Please try it again.");
	}
?>