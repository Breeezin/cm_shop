<?php

	
	

	// Default some values
	$this->param('RootAssetID','1');
	$this->param('AppearsInMenus','No');
	$this->param('ExcludeChildrenOf',array());
	$this->param('ExcludeAssets',array());
	$this->param('IncludeChildrenOf',array());
	$this->param('MaxDepth','1024');
	$this->param("ShowAssetDescription", false);
	$this->param("FilterByAdmin", false);
	$this->param("NoOfflineAssets", 'No');
	
	// If IncludeChildrenOf is supplied, then only
	// display children that are supplied in the array
//	$this->param('IncludeChildrenOf',array());	

	// build AppearsInMenus SQL
	$whereSQL = '';
	if ($this->ATTRIBUTES['AppearsInMenus'] != 'No') {
		$whereSQL .= 'AND as_appear_in_menus = 1';
	}
	
	
	// check the user whether he is from innovativemedia. if so, we shows all deleted assets,,
	//$excludeChildrenOf = ArrayToList($this->ATTRIBUTES['ExcludeChildrenOf']);
	//if (strlen($excludeChildrenOf)) $whereSQL .= " AND as_parent_as_id NOT IN ($excludeChildrenOf)";
	$isSuperUserResult = new Request("Security.Authenticate",array(
				'Permission'	=>	'IsSuperUser',
				'LoginOnFail'	=>	false,
	));
	
	$isSuperUser = $isSuperUserResult->value;
	
	$isIMediaUserResult = new Request("Security.Authenticate",array(
		'Permission'	=>	'IsDeployer',
		'LoginOnFail'	=>	false,
	));
	
	$isIMediaUser = $isIMediaUserResult->value;
	
	//check the current user whether he is super/im user or not..			
	if (!$isSuperUser AND !$isIMediaUser) {
		//$whereSQL .= ' AND as_deleted != 1 AND as_dev_asset != 1';		
		$whereSQL .= ' AND (as_dev_asset IS NULL OR as_dev_asset = 0)';		
	}
	// Find all assets that appear in menus
	$fieldNames = "as_parent_as_id, as_id, as_name, as_type, as_owner_au_id, as_menu_name";
	if ($this->ATTRIBUTES['ShowAssetDescription']) {
		$fieldNames .= ",  as_search_description";
	}
	
	if (ss_optionExists("Schedule assets")) {
		//ss_DumpVarDie($this->ATTRIBUTES);
		if ($this->ATTRIBUTES['NoOfflineAssets'] == 'Yes') {
			$whereSQL .= " AND (AssetOnline IS NULL OR AssetOnline = '' OR (AssetOnline = 'Date' AND AssetOnlineDate < NOW()) )";
			$whereSQL .= " AND (AssetOffline IS NULL OR AssetOffline = '' OR (AssetOffline = 'Date' AND AssetOfflineDate > NOW()) )";
		}
	}
	
	$result = query("
		SELECT  $fieldNames FROM assets
		WHERE as_deleted != 1 AND as_hidden != 1
			$whereSQL	
		ORDER BY as_sort_order,as_name					
	");

	// Read the result of the query into an array
	$this->treeAssets = array();
	while ($row = $result->fetchRow()) {
		array_push($this->treeAssets,$row);
	}
	
	//ss_DumpVarDie($this->treeAssets);	
	
	// Get the ID of the root asset
	$resultID = new Request("Asset.PathFromID",array('as_id' => $this->ATTRIBUTES['RootAssetID']));
	//ss_DumpVarDie($this->ATTRIBUTES);
	// Return the tree
	$tree = $this->generateTree($this->ATTRIBUTES['RootAssetID'],$resultID->value,0);
	
	
	// if the tree items need to be filtered by the user's perssion level		
	if ($this->ATTRIBUTES['FilterByAdmin']) {
		$user = ss_getUser();		
		$userGroups = ArrayKeysToList($user['user_groups']);
		$Q_Groups = query("
				SELECT aug_as_id, MAX(aug_can_administer) AS CanAdmin FROM asset_user_groups
				WHERE
					aug_ug_id IN ($userGroups)
				GROUP BY aug_as_id
		");
		$assetsCanAdminister = array();
		while ($assetGroup = $Q_Groups->fetchRow()) {
			if ($assetGroup['CanAdmin'] == 1) {
				$assetsCanAdminister[$assetGroup['aug_as_id']] = 1;
			}
		}
		//ss_DumpVar($assetsCanAdminister);
		//go through each node and check whether the current user has perssion to access
		hasChildren($tree[0], $assetsCanAdminister);		
		checkChildren($tree[0]);		
		
		//ss_DumpVarDie($tree);	
	} else {
		if (count($tree)) {
			makeAllDisplayble($tree[0]);
		}
		
	}

	
	//ss_DumpVarDie($tree);
	return $tree;
?>
