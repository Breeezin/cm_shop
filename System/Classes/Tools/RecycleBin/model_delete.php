<?php 
	$this->param("as_id");

	$as_id = (int)$this->ATTRIBUTES['as_id'];
	
	$Q_CanDelete = query("SELECT * FROM assets	WHERE (as_id = $as_id) AND (NOT as_system = 1)");

	
	if ($Q_CanDelete->numRows()) {
		$aAsset = $Q_CanDelete->fetchRow();		
											
		// Include and instantiate the class type
		$className = $aAsset['as_type'].'Asset';
		requireClass($className);
		$temp = new $className;
	
		// Call the display handler for the specific type
		$temp->delete(&$this);			
				
		$assetDir = expandPath(ss_storeForAsset($aAsset['as_id']));
		//  delete the content directory 
		ss_deleteFilesWithSub($assetDir);

		$Q_DeleteAssetLink = query("
			DELETE FROM asset_user_groups WHERE aug_as_id = {$aAsset['as_id']}
		");
		$Q_DeleteAssetLink = query("
			DELETE FROM asset_users WHERE au_as_id = {$aAsset['as_id']}
		");	
		$Q_DeleteAsset = query("DELETE FROM assets WHERE (as_id = {$aAsset['as_id']}) AND (NOT as_system = 1)");
		
		$message = "{$aAsset['as_name']} was sucessfully deleted.";
		if (is_dir($assetDir)) {
			$message = "$assetDir was failed to delete. Please delete the directory manually. Thank you.";
		}
		location("index.php?act=RecycleBin.AssetList&Message=".ss_URLEncodedFormat($message));
				
	}		
	
?>
