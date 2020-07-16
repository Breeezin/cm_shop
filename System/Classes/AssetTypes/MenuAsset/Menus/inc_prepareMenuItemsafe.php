<?php

	$displayChildren = true;
	$useParentPath = false;
	if ($this->ATTRIBUTES['AST_MENU_DROPDOWNS'] == 0) {
		$displayChildren = false;
	}

	if (ss_optionExists("Hide Memeber's Menu")) {
		if (($item['RootParentAssetType'] == 'Members' or $item['as_type'] == 'Members') and !$isLoggedIn) {
			$displayChildren = false;
			if ($item['as_type'] != 'Members') {
				$useParentPath = true;
			}
		}
	}
	$menuName = "mm_menu_{$menuCount}_{$counter}";
	$menuID = "menuid_{$menuCount}_{$counter}";

	// Figure out what class to apply to the cell
	if ($counter == 1 or $firstExpanded or $firstExtraExpanded) {
		$cellClass = $this->ATTRIBUTES["AST_MENU_{$stylePrefix}FIRSTCELLCLASS"];
	} else {
		$cellClass = $this->ATTRIBUTES["AST_MENU_{$stylePrefix}OTHERCELLCLASS"];
	}

	// Figure out the link

	$link = ltrim($item['Path'],'/');

	if ($useParentPath) {
		if (strlen($item['ParentPath'])) {
			$link = ltrim($item['ParentPath'],'/');
		}
	}

	if (strtolower(substr($link,0,10)) == 'index.php/' ) {
		$link = substr($link,10);
	}
	//ss_DumpVar($link);
	$link = ss_EscapeAssetPath($link);

	// Figure out what to display as the item name
	$linkMouseOver = '';
	$linkMouseOut = '';
	$menuAnchor = $menuID;


	$imageFileFullPath = "Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}Images/MenuLabels/l-".str_replace('/','~',str_replace(' ','-',ltrim($item['Path'],'/')));
	$highlightsImageFileFullPath = "Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}Images/MenuLabels/l-".str_replace('/','~',str_replace(' ','-',ltrim($item['Path'],'/')))."-r";

	if (file_exists($imageFileFullPath.'.gif') or file_exists($imageFileFullPath.'.png')) {
		$imageFile = $imageFileFullPath;
	} else {
		$imageFile = "Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}Images/MenuLabels/l-".str_replace(' ','-',$item['as_name']);
	}

	if (file_exists($highlightsImageFileFullPath.'.gif') or file_exists($highlightsImageFileFullPath.'.png')) {
		$highlightsImageFile = $highlightsImageFileFullPath;
	} else {
		$highlightsImageFile = "Custom/ContentStore/Layouts/{$GLOBALS['cfg']['currentSiteFolder']}Images/MenuLabels/l-".str_replace(' ','-',$item['as_name'])."-r";
	}

	$selectedItem = false;
	$gif = $imageFile;
	if (file_exists($imageFile.'.gif') and !($this->ATTRIBUTES['AST_MENU_TEXT_ONLY'])) {
		// Menu item has an image

		// check if the menu item has highlight image
		if (file_exists($highlightsImageFile.'.gif')) {
			// check if the item is existing in the parent asset ids of the currently displayed asset
			// or check if the item is the currently displayed asset
			if (array_key_exists($item['as_id'], $currentAssetTree)) {
				$gif = $highlightsImageFile;
				$selectedItem = true;
			} else if($currentAsset == $item['as_id']){
				$gif = $highlightsImageFile;
				$selectedItem = true;
			}
		}

		$itemDisplay = "<IMG NAME=\"assetMenuImage{$menuCount}_{$counter}\" BORDER=\"0\" SRC=\"{$gif}.gif\" ALT=\"{$item['as_name']}\">";

		$menuAnchor = "assetMenuImage{$menuCount}_{$counter}";
		if (file_exists($highlightsImageFile.'.gif') and !$selectedItem) {
			// Menu item has a roll over image
			$linkMouseOver = "document.images.assetMenuImage{$menuCount}_{$counter}.src='{$highlightsImageFile}.gif';";
			$linkMouseOut = "document.images.assetMenuImage{$menuCount}_{$counter}.src='$gif.gif';";
		}
	} else 	if (file_exists($imageFile.'.png') and !($this->ATTRIBUTES['AST_MENU_TEXT_ONLY'])) {
		// Menu item has an image

		// check if the menu item has highlight image
		if (file_exists($highlightsImageFile.'.png')) {
			// check if the item is existing in the parent asset ids of the currently displayed asset
			// or check if the item is the currently displayed asset
			if (array_key_exists($item['as_id'], $currentAssetTree)) {
				$gif = $highlightsImageFile;
				$selectedItem = true;
			} else if($currentAsset == $item['as_id']){
				$gif = $highlightsImageFile;
				$selectedItem = true;
			}
		}

		$itemDisplay = "<IMG NAME=\"assetMenuImage{$menuCount}_{$counter}\" BORDER=\"0\" SRC=\"{$gif}.png\" ALT=\"{$item['as_name']}\">";

		$menuAnchor = "assetMenuImage{$menuCount}_{$counter}";
		if (file_exists($highlightsImageFile.'.png') and !$selectedItem) {
			// Menu item has a roll over image
			$linkMouseOver = "document.images.assetMenuImage{$menuCount}_{$counter}.src='{$highlightsImageFile}.png';";
			$linkMouseOut = "document.images.assetMenuImage{$menuCount}_{$counter}.src='$gif.png';";
		}
	} else {
		// Menu item has no images
		if ($item['as_menu_name'] != null) {
			$itemDisplay = $item['as_menu_name'];
		} else {
			$itemDisplay = $item['as_name'];
		}
	}



	// If the menu has a drop down, add macromedia code for pop ups
	if (count($item['Children']) and $displayChildren) {
		$linkMouseOver = "MM_showMenu(window.{$menuName},{$this->ATTRIBUTES['AST_MENU_DDOFFSETX']},{$this->ATTRIBUTES['AST_MENU_DDOFFSETY']},null,'{$menuAnchor}'); ".$linkMouseOver;
		$linkMouseOut = $linkMouseOut.'MM_startTimeout();';
	}

	//done for James Dunlop - just shows/hides a second menu
	if (count($item['Children']) and $displayChildren and ss_optionExists('Horizontal Subnav')){
	        $linkMouseOver = 'showSubNav()';
	        $linkMouseOut = 'hideSubNav()';
    }


	if (strlen($linkMouseOver) > 0)	$linkMouseOver = "ONMOUSEOVER=\"$linkMouseOver\"";
	if (strlen($linkMouseOut) > 0)	$linkMouseOut = "ONMOUSEOUT=\"$linkMouseOut\"";


	$cellMouseOver = '';
	$cellMouseOut = '';
	$cellClick = '';
	if (strlen($this->ATTRIBUTES["AST_MENU_{$stylePrefix}ROLLOVERCLASS"]) > 0) {
		$cellMouseOver = "ONMOUSEOVER=\"this.className='{$this->ATTRIBUTES["AST_MENU_{$stylePrefix}ROLLOVERCLASS"]}';";
		$cellMouseOut = "ONMOUSEOUT=\"this.className='$cellClass';";
		if (!$selectedItem) {
			if (array_key_exists($item['as_id'], $currentAssetTree)) {
				$cellClass = $this->ATTRIBUTES["AST_MENU_{$stylePrefix}ROLLOVERCLASS"];
				$cellMouseOut = "ONMOUSEOUT=\"this.className='$cellClass';";
				$selectedItem = true;
			} else if($currentAsset == $item['as_id']){
				$cellClass = $this->ATTRIBUTES["AST_MENU_{$stylePrefix}ROLLOVERCLASS"];
				$cellMouseOut = "ONMOUSEOUT=\"this.className='$cellClass';";
				$selectedItem = true;
			}
			if (ss_optionExists('Allow Macrons')) $link = str_replace('&#', 'AndSharp', $link);
			$cellClick = "ONCLICK=\"document.location='".ss_JSStringFormat($GLOBALS['cfg']['currentServer']).ss_JSStringFormat($link)."';\"";
		}
	}

	if (!$selectedItem) {
		if (array_key_exists($item['as_id'], $currentAssetTree)) {
			$selectedItem = true;
		} else if($currentAsset == $item['as_id']){
			$selectedItem = true;
		}
	}


	$startDiv = '';
	$endDiv = '';
	$cellTitle = '';
	if (ss_optionExists("Show Menu Description") and !$isLoggedIn) {

		if (strlen($item['AssetDescription'])) {
			$cellTitle = "title=\"".ss_HTMLEditFormat($item['AssetDescription'])."\"";
			//$startDiv = "<div title=\"".ss_HTMLEditFormat($item['AssetDescription'])."\">";
			//$endDiv = '</div>';
			/*
			if (!strlen($cellMouseOver)) {
				$cellMouseOver = "ONMOUSEOVER=\"";
				$cellMouseOut = "ONMOUSEOUT=\"";
			}
			$cellMouseOver .= "Menu_setTextOfLayer('{$this->ATTRIBUTES['AST_MENU_SHOW_DESCRIPTION']}','','".ss_JSStringFormat($item['AssetDescription'])."')";
			$cellMouseOut .= "Menu_setTextOfLayer('{$this->ATTRIBUTES['AST_MENU_SHOW_DESCRIPTION']}','','')";
			*/
		}
	}
	if (strlen($cellMouseOver)) {
		$cellMouseOver .= "\"";
	}
	if (strlen($cellMouseOut)) {
		$cellMouseOut .= "\"";
	}


?>