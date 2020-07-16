<?php 

	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		$this->param("as_id");
		$this->param("as_parent_as_id");
		
		// Find out what we should name ourselves ---;		
		$assetName = ss_newAssetName($asset['as_name'],$this->ATTRIBUTES['as_parent_as_id']);
		//Now we have asset name is our new name --->
		
		
		//<!--- Find the correct value for as_sort_order --->
		$Q_GetPosition = getRow("
			SELECT MAX(as_sort_order) AS Last FROM assets
			WHERE as_parent_as_id = {$this->ATTRIBUTES['as_parent_as_id']}
		");
		
		if (strlen($Q_GetPosition['Last']))
			$assetPosition = $Q_GetPosition['Last']+1;
		else 
			$assetPosition = 1;

		
		$Q_UpdateAsset = query("
			Update assets
			SET as_name = '{$assetName}',
				as_last_modified = Now(),				
				as_archive = 1,
				as_parent_as_id = {$this->ATTRIBUTES['as_parent_as_id']},
				as_sort_order = $assetPosition
			WHERE (as_id = {$this->ATTRIBUTES['as_id']})
		");
								
	}
?>