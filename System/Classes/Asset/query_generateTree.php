<?php
	// input: $root, $path, $currentDepth = 0, $excludeChildrentOf
	$this->param("ShowRootParentAssetType", false);
	
	if ($path == '/index.php') {
		$path = '';	
	}
	
	$result = array();

	// Check if we've gone too deep into the tree
	if ($currentDepth >= $this->ATTRIBUTES['MaxDepth']) return $result;
		
	// Loop thru all the assets
	foreach ($this->treeAssets as $asset) {
		
		if (!array_key_exists($asset['as_id'],$this->ATTRIBUTES['ExcludeAssets'])) {
			// Find ones which have the specified root
			if ($asset['as_parent_as_id'] == $root) {
					
				//if ($asset['as_parent_as_id'] == 500) die("here");
				
				// Set the asset path
				$asset['Path'] = "$path/{$asset['as_name']}";
				
				// Set the asset parent path
				$asset['ParentPath'] = $path;
				$asset['ParentID'] = $asset['as_parent_as_id'];				
				
				if ($this->ATTRIBUTES['ShowRootParentAssetType']) {
					$assetTreeResult = new Request ('Asset.AncestorsFromID', array('as_id' => $asset['as_id']));
					$temp = $assetTreeResult->value;				
							
					if (count($temp) == 1) {
						$asset['RootParentAssetType'] = '';		
					} else {
						$counter = count($temp);
						$assettemp = '';
						foreach($temp as $x => $y) {
							$assettemp = $x;							
							if ($counter == 2)  break;
							$counter--;
						}
						$Q_GetAssetType = getRow("SELECT as_type FROM assets WHERE as_id = $assettemp");
						$asset['RootParentAssetType'] = $Q_GetAssetType['as_type'];	
					}
				}
				
				if ($this->ATTRIBUTES['ShowAssetDescription']) {										
					$asset['AssetDescription'] = $asset['as_search_description'];
				}
				// Check if we can display the children of this asset
				$includeChildren = true;
				if (count($this->ATTRIBUTES['IncludeChildrenOf'])) {
					$includeChildren = array_key_exists($asset['as_id'],$this->ATTRIBUTES['IncludeChildrenOf']);
				}
				
				// Get their chilren
				if (!array_key_exists($asset['as_id'],$this->ATTRIBUTES['ExcludeChildrenOf'])) {
						
					if ($includeChildren) {
						// Include the children
						
						$asset['Children'] = $this->generateTree($asset['as_id'],$asset['Path'],$currentDepth+1);
						$asset['HasChildren'] = count($asset['Children']) > 0;
					} else {
						// Only check if chilren exist. Don't care what the children are.
						// This is useful for the progressively loading menu.
						//ss_DumpVar("this",$this);
						//if ($asset['as_name'] == 'Home') die("here2");
						$asset['Children'] = array();
						$asset['HasChildren'] = FALSE;
						foreach ($this->treeAssets as $checkAsset) {
							if ($checkAsset['as_parent_as_id'] == $asset['as_id']) {
								//$asset['Children'] = $this->generateTree($asset['as_id'],$asset['Path'],$currentDepth+1);							
								$asset['HasChildren'] = TRUE;
								break;
							}
						}
					}
					
				} else {
					//ss_DumpVarDie($);
					$asset['HasChildren'] = FALSE;
					$asset['Children'] = array();
				}	
				
				// Add it to the list
				array_push($result,$asset);
			}
		}
	}
	
	return $result;
	
?>