<?php

	// set our prefix so we get the nice new styles
	//ss_DumpVar($item, $this->ATTRIBUTES['AST_MENU_EXPAND_CURRENT_ASSET'].' where', true);
	if (strlen($showDropDownMenu) and $item['HasChildren']) {
		///ss_DumpVar($this->ATTRIBUTES['AST_MENU_EXPAND_CURRENT_ASSET']);
		//ss_DumpVar($item)
		$result = new Request('Asset.Embed', array('as_id' => $showDropDownMenu, 'MainAssetPath' => $this->ATTRIBUTES['MainAssetPath'], 'MainAssetID' => $currentAsset));
		//print $result->value;
		///ss_DumpVar($result, 'where', true);
		print "{$separator}{$itemStart}<TD>".$result->display."</TD>{$itemFinish}";
	} else {
	
		$stylePrefix = 'EXPANDED_';
		
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
		$firstExpanded = true;
		foreach ($expandedMenuStructure as $item) {
			// figure out all the crap
			require('inc_prepareMenuItem.php');

			// Display the menu item		
			//print "{$separator}{$itemStart}<TD CLASS=\"{$cellClass}\" $cellTitle $cellMouseOver $cellMouseOut $cellClick>$startDiv<A NAME=\"$menuID\" ID=\"$menuID\" $linkMouseOver $linkMouseOut HREF=\"{$link}\" CLASS=\"{$this->ATTRIBUTES['AST_MENU_LINKCLASS']}\">{$itemDisplay}</A>$endDiv</TD>{$itemFinish}";
			print "{$separator}{$itemStart}<TD CLASS=\"{$cellClass}\" $cellTitle $cellMouseOver $cellMouseOut $cellClick>$startDiv<A NAME=\"$menuID\" ID=\"$menuID\" $linkMouseOver $linkMouseOut HREF=\"{$link}\" CLASS=\"{$this->ATTRIBUTES["AST_MENU_{$stylePrefix}LINKCLASS"]}\">{$itemDisplay}</A>$endDiv</TD>{$itemFinish}";
	
			// Display the separator
			if (strlen($this->ATTRIBUTES['AST_MENU_SEPARATOR']) > 0)
				$separator = "{$itemStart}<TD CLASS=\"{$this->ATTRIBUTES["AST_MENU_{$stylePrefix}SEPARATORCELLCLASS"]}\">{$this->ATTRIBUTES['AST_MENU_SEPARATOR']}</TD>{$itemFinish}";
			$counter++;
			/*if ($this->ATTRIBUTES['AST_MENU_EXPAND_CURRENT_ASSET'] and $selectedItem) {
				require('inc_extraExpandedMenuItems.php');
			}*/		
			$firstExpanded = false;
		}
	
		// revert back to the original styles
		$stylePrefix = '';
	}
	
?>