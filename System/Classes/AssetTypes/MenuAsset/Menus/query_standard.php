<?php
	
	// Set some defaults
	$this->param('user_groups',array(0));
	$this->param('bodyStart','');
	$this->param('bodyEnd','');
	
	// Load the fields
	require("inc_menuFields.php");	
	foreach (array('Standard Menu Settings','Drop Down Menu Settings') as $fieldTypes) {
		foreach ($menuFields[$fieldTypes] as $name => $values) {
			if ($values[2] == '(nothing)') $values[2] = '';
			$this->param($name,$values[2]);
			if ($this->ATTRIBUTES[$name] == null) $this->ATTRIBUTES[$name] = $values[2];
		}
	}

	// by default we pull through the bg color so there is no separator
	if (strlen($this->ATTRIBUTES['AST_MENU_DDSEPARATORCOLOR']) == 0) {
		$this->ATTRIBUTES['AST_MENU_DDSEPARATORCOLOR'] = $this->ATTRIBUTES['AST_MENU_DDBGCOLOR'];
	}
	
	if ($this->ATTRIBUTES['AST_MENU_DDOFFSETX'] == $menuFields['Drop Down Menu Settings']['AST_MENU_DDOFFSETX'][2]) {
		if ($this->ATTRIBUTES['AST_MENU_ORIENTATION'] == 'Vertical') {
			$this->ATTRIBUTES['AST_MENU_DDOFFSETX'] = 100;
		} else {
			$this->ATTRIBUTES['AST_MENU_DDOFFSETX'] = 0;
		}
	}

	if ($this->ATTRIBUTES['AST_MENU_DDOFFSETY'] == $menuFields['Drop Down Menu Settings']['AST_MENU_DDOFFSETY'][2]) {
		if ($this->ATTRIBUTES['AST_MENU_ORIENTATION'] == 'Vertical') {
			$this->ATTRIBUTES['AST_MENU_DDOFFSETY'] = 0;
		} else {
			$this->ATTRIBUTES['AST_MENU_DDOFFSETY'] = 20;
		}
	}

	
	// Build a list of groups that the current logged in user is in
	$groups = ''; $comma = '';
	foreach ($_SESSION['User']['user_groups'] as $group) {
			$groups .= $comma.$group;
			$comma = ',';
	}
	/*// Find which assets the user is not allowed to view
	$assetUserGroupsQuery = query("
		SELECT * FROM asset_user_groups
		WHERE aug_ug_id IN ($groups)
			AND Use_ = 0
	");*/

	// Make an array of assets that we hide the children of
	// (any assets that the current user doesn't have access to 'Use')
	$excludeChildrenOf = array();
	/*if ($assetUserGroupsQuery->numRows() > 0) {
		$excludeFreq = array();
		while ($row = $assetUserGroupsQuery->fetchRow()) {
			if (array_key_exists($row['AssetLink'],$excludeFreq)) {
				$excludeFreq[$row['AssetLink']]++;
			} else {
				$excludeFreq[$row['AssetLink']] = 1;
			}
		}
		
		foreach ($excludeFreq as $assetID => $frequency) {
			if ($frequency == count($_SESSION['User']['user_groups'])) {
				$excludeChildrenOf[$assetID] = 1;
			}
		}
	}*/
	
//	while ($row = $assetUserGroupsQuery->fetchRow()) {
//		$excludeChildrenOf[$row['AssetLink']] = 1;
//	}

	$noMenus = false;

	if (strlen($this->ATTRIBUTES['AST_MENU_ROOT_ASSETLEVEL'])) {
		$assetPath = '';
		if (ListLen($this->ATTRIBUTES['MainAssetPath'],'/') >= $this->ATTRIBUTES['AST_MENU_ROOT_ASSETLEVEL']) {
			for ($i = 1; $i <= $this->ATTRIBUTES['AST_MENU_ROOT_ASSETLEVEL'];$i++) {
				$assetPath .= '/'.ListGetAt($this->ATTRIBUTES['MainAssetPath'],$i,'/');
			}
			$id = new Request("Asset.IDFromPath",array(
				'AssetPath'	=>	$assetPath
			));
			$this->ATTRIBUTES['AST_MENU_ROOT_ASSETID'] = $id->value;
		} else {
			$noMenus = true;	
		}

	}

	if (!$noMenus) {
		
		$treeStructureArray = array(
			'RootAssetID'		=>	$this->ATTRIBUTES['AST_MENU_ROOT_ASSETID'],
			'AppearsInMenus'	=>	'Yes',
			'ExcludeChildrenOf'	=>	$excludeChildrenOf,							
			'NoOfflineAssets'	=>	'Yes',
		);
		
		if (ss_optionExists("Hide Memeber's Menu")) {
			$treeStructureArray['ShowRootParentAssetType'] = true;
		} 
		if (ss_optionExists("Show Menu Description")) {
			$treeStructureArray['ShowAssetDescription'] = true;
		} 
		
		// Get the menu structure
		$structure = new Request("Asset.TreeStructure",$treeStructureArray);
		//ss_DumpVar($structure);
		$menuStructure = $structure->value;
		$showCategoryID = ss_optionExists('Show Shop Category Menu');
		if ($treeStructureArray['RootAssetID'] != 1 and $showCategoryID) {
			if ($showCategoryID == $treeStructureArray['RootAssetID']) {
				$rootAsset = getRow("SELECT * FROM assets WHERE as_id = {$treeStructureArray['RootAssetID']}");
				//ss_DumpVar($rootAsset, $assetPath);
				if ($rootAsset['as_type'] == 'ShopSystem') {
					if (isset($assetPath)) {
						$categories = ss_getShopCategories($treeStructureArray['RootAssetID'], true, $assetPath);					
						$menuStructure = array_merge($menuStructure, $categories);					
					}
				} else {
					$rootAsset = getRow("SELECT * FROM assets WHERE as_type LIKE 'ShopSystem'");
					$pathresult = new Request("Asset.PathFromID",array(
						'as_id'	=>	$rootAsset['as_id']
					));
					
					$categories = ss_getShopCategories($rootAsset['as_id'], true, $pathresult->value);					
					$menuStructure = array_merge($menuStructure, $categories);					
				
				}
			}
		}
//		ss_DumpVar($menuStructure,'Dont panic ~ this is only for IM, - Nam', true);
	}
	
	
?>