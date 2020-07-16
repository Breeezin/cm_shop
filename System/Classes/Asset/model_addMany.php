<?php 

	function addErrorEntry($array, $key, $msg) {
		
		if (!array_key_exists("$key", $array)) {
			$array["$key"] = array();
		}
		
		array_push($array["$key"], "$msg");
		return $array;
	}
		
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		$this->param('AssetsToAdd', '');
		
		$allAssets = ListToArray($this->ATTRIBUTES['AssetsToAdd'], Chr(10));
		
		// get all layouts and stylesheet to array
		$ListLayouts = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Layouts.txt')),Chr(10));
		$layouts = array();
		foreach ($ListLayouts as $aLayout) {
			$index = ListLast($aLayout,":");
			$layouts["$index"] = ListFirst($aLayout,":");		
		}
		
		$StyleSheetsList = ListToArray(file_get_contents(expandPath('Custom/ContentStore/Layouts/Stylesheets.txt')),Chr(10));
		$stylesheets =  array();
		foreach ($StyleSheetsList as $aStylesheet) {
			$index = ListLast($aStylesheet,":");
			$stylesheets["$index"] = ListFirst($aStylesheet,":");
		}
			
		
		foreach($allAssets as $aAsset) {			
			
			$assetPath = ListGetAt($aAsset,1,":");
			$assetType = ListGetAt($aAsset,2,":");
			$appearsInMenus = strtolower(ListGetAt($aAsset,3,":")) == 'yes' ? 1 : 0;			
			$assetLayout = ListGetAt($aAsset,4,":");
			$assetStylesheet = ListGetAt($aAsset,5,":");
			
			//Check there's a parent asset 			
			$assetParentID = 1;
			$assetParentPath = "";
			$assetParentFound = false;
			$pathLen = ListLen($assetPath,"/");
			$assetName = ListLast($assetPath,"/");
			
			if ($pathLen > 1) {
				if (ListFirst($assetPath,"/") == "index.php") {					
					$assetParentFound = true;				
				} 				
				if (!$assetParentFound) {
					
	 				// Find the parent asset ID
	 				$assetParentPath = ListDeleteAt($assetPath,$pathLen,"/"); 	 																	
					$this->ATTRIBUTES['AssetPath'] = $assetParentPath;
					$assetParentID = $this->getIDFromPath();
				}
			}
			
			if ($assetParentID == null) {
				$errors = addErrorEntry($errors,$assetPath, "Parent asset not found: $assetParentPath");
			} else {
				$anyError = false;
				$layout = "";
				$stylesheet = "";
				$type = "";
				if (array_key_exists($assetType, $types)) {
					$type = $types[$assetType];	
				} else {
					$errors = addErrorEntry($errors,$assetPath, "Asset type not found: $assetType");
					$anyError = true;
				}
				
				if (array_key_exists($assetLayout, $layouts)) {
					$layout = $layouts[$assetLayout];	
				} else {
					$errors = addErrorEntry($errors,$assetPath, "Asset layout not found: $assetLayout");
					$anyError = true;
				}
				
				if (array_key_exists($assetStylesheet, $stylesheets)) {
					$stylesheet = $stylesheets[$assetStylesheet];	
				} else {					
					$errors = addErrorEntry($errors,$assetPath, "Asset stylesheet not found: $assetStylesheet");
					$anyError = true;
				}
				if (!$anyError) {									
					$assetName = ss_newAssetName($assetName,$assetParentID);					
					//!--- Add a new asset --->
					$result = new Request('Asset.Add',array(
						'as_name'	=>	$assetName,
						'as_type'	=>	$type,
						'as_appear_in_menus'	=>	$appearsInMenus,
						'as_parent_as_id'	=>	$assetParentID,
						'DoAction'	=>	1,
						'AsService'	=>	true,
					));
					$newID = $result->value;
					if ($newID !== null) {									
						$layoutCereal = array('LYT_LAYOUT' => $layout, 'LYT_STYLESHEET' => $stylesheet,);
						$layoutCereal = serialize($layoutCereal);
						$Q_UpAsset = query("UPDATE assets SET as_layout_serialized = '$layoutCereal'  WHERE as_id = {$newID}");					
					} else {
						if (array_key_exists("EntryErrors",$this->ATTRIBUTES))
							$errors = addErrorEntry($errors,$assetPath, "Couldn't create $assetType: ".$this->ATTRIBUTES['EntryErrors']);
					}					
				}
			}
		}
	}
?>