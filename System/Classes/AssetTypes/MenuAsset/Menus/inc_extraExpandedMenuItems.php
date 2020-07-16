<?php

	// set our prefix so we get the nice new styles
	$stylePrefix = 'EXTRA_EXPANDED_';
	
	$treeStructureArray = array(
		'RootAssetID'		=>	$item['as_id'],
		'AppearsInMenus'	=>	'Yes',
		'ExcludeChildrenOf'	=>	$excludeChildrenOf,			
	);
	
	if (ss_optionExists("Hide Memeber's Menu")) $treeStructureArray['ShowRootParentAssetType'] = true;
	if (ss_optionExists("Show Menu Description")) $treeStructureArray['ShowAssetDescription'] = true;
	
	// Get the menu structure
	$structure = new Request("Asset.TreeStructure",$treeStructureArray);
	$expandedMenuStructure = $structure->value;

	// draw the sub menu thinggy
	$firstExtraExpanded = true;
	foreach ($expandedMenuStructure as $item) {
		// figure out all the crap
		require('inc_prepareMenuItem.php');
		
		// Display the menu item		
		
		print "{$separator}{$itemStart}<TD CLASS=\"{$cellClass}\" $cellTitle $cellMouseOver $cellMouseOut $cellClick>$startDiv<A NAME=\"$menuID\" ID=\"$menuID\" $linkMouseOver $linkMouseOut HREF=\"{$link}\" CLASS=\"{$this->ATTRIBUTES["AST_MENU_{$stylePrefix}LINKCLASS"]}\">{$itemDisplay}</A>$endDiv</TD>{$itemFinish}";

		// Display the separator
		if (strlen($this->ATTRIBUTES['AST_MENU_SEPARATOR']) > 0)
			$separator = "{$itemStart}<TD CLASS=\"{$this->ATTRIBUTES["AST_MENU_{$stylePrefix}SEPARATORCELLCLASS"]}\">{$this->ATTRIBUTES['AST_MENU_SEPARATOR']}</TD>{$itemFinish}";
		$counter++;
		$firstExtraExpanded = false;
	}

	// revert back to the original styles
	$stylePrefix = 'EXPANDED_';
	
?>