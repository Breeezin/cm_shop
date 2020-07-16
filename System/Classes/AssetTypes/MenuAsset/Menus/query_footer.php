<?php
	// Load the fields
	require("inc_menuFields.php");	
	foreach (array('Footer Menu Settings') as $fieldTypes) {
		foreach ($menuFields[$fieldTypes] as $name => $values) {
			if ($values[2] == '(nothing)') $values[2] = '';	
			$this->param($name,$values[2]);
			if ($this->ATTRIBUTES[$name] == null) $this->ATTRIBUTES[$name] = $values[2];	
		}
	}
	
	$noMenus = false;
	if (strlen($this->ATTRIBUTES['AST_MENU_FOOTER_ROOT_ASSETLEVEL'])) {
		$assetPath = '';
		if (ListLen($this->ATTRIBUTES['MainAssetPath'],'/') >= $this->ATTRIBUTES['AST_MENU_FOOTER_ROOT_ASSETLEVEL']) {
			for ($i = 1; $i <= $this->ATTRIBUTES['AST_MENU_FOOTER_ROOT_ASSETLEVEL'];$i++) {
				$assetPath .= '/'.ListGetAt($this->ATTRIBUTES['MainAssetPath'],$i,'/');
			}
			$id = new Request("Asset.IDFromPath",array(
				'AssetPath'	=>	$assetPath
			));
			$this->ATTRIBUTES['AST_MENU_FOOTER_ROOT_ASSETID'] = $id->value;
		} else {
			$noMenus = true;	
		}

	}

	$whereSQL = '';
	if (ss_optionExists("Schedule assets")) {
		$whereSQL .= " AND (AssetOnline IS NULL OR AssetOnline = '' OR (AssetOnline = 'Date' AND AssetOnlineDate < NOW()) )";
		$whereSQL .= " AND (AssetOffline IS NULL OR AssetOffline = '' OR (AssetOffline = 'Date' AND AssetOfflineDate > NOW()) )";
	}
	
	
	if (!$noMenus) {
		$result = query("
			SELECT as_name, as_menu_name, as_subtitle FROM assets
			WHERE as_parent_as_id = {$this->ATTRIBUTES['AST_MENU_FOOTER_ROOT_ASSETID']}
				AND as_appear_in_menus = 1
				AND (as_deleted IS NULL OR as_deleted = 0)
				$whereSQL
			ORDER BY as_sort_order,as_name					
		");
	}
	
?>
