<?php

	$result = array(
		'bodyStart'	=>	null,
		'bodyEnd'	=>	null,
	);	

	if ($noMenus) {
		print("&nbsp;");
		return $result;
	}
	
	$rowCount = count($menuStructure);
	$menuCount = md5(uniqid(""));
	
	
	// ----------------------------------------
	// Construct the drop down menu definitions
	// ----------------------------------------
	
	$dropDowns = '';
	if (strpos($GLOBALS['BodyStart'],'mm_menu.js') === FALSE) {
		$dropDowns .= '<SCRIPT type="text/javascript" LANGUAGE="JavaScript" SRC="System/Classes/AssetTypes/MenuAsset/Menus/mm_menu.js"></SCRIPT>';
	}
	$lastMenu = NULL;
	$counter = 1;
	
	// get parent asset ids of the displayed asset item
	
	$currentAsset = $this->ATTRIBUTES['MainAssetID']; // 	
	$currentAssetParents = array();
	$showDropDownMenu = ss_optionExists('Show Menu Expand And Dropdowns');
	if ($showDropDownMenu) {
		$currentAssetParents = ss_getAssetParentIDs($currentAsset);
	}
	$currentAssetTree = new Request ('Asset.AncestorsFromID', array('as_id' => $currentAsset));
	$currentAssetTree = $currentAssetTree->value;
	
	
	$isLoggedInResult = new Request("Security.Authenticate",array(
		'Permission'	=>	'IsLoggedIn',
		'LoginOnFail'	=>	'no',
	));
	$isLoggedIn = $isLoggedInResult->value;
	
	// can't have drop downs on 'expander' menus .. the 'counter' will be out of sync
	if ($this->ATTRIBUTES['AST_MENU_EXPAND_CURRENT_ASSET']) {
		$this->ATTRIBUTES['AST_MENU_DROPDOWNS'] = 0;
	}
	
	
	foreach($menuStructure as $key => $item) {
		if ($showCategoryID and $item['as_id'] == $showCategoryID){
			if ($item['as_type'] =='ShopSystem') {				
				$categories = ss_getShopCategories($item['as_id'], true, $item['Path']);
				if ($item['HasChildren']) {
					$item['Children'] = array_merge($item['Children'], $categories);					
				} else {
					$menuStructure[$key]['HasChildren'] = count($categories)?true:false;
					$item['HasChildren'] = $menuStructure[$key]['HasChildren'];
					$menuStructure[$key]['Children'] = &$categories;
					$item['Children'] = $menuStructure[$key]['Children'];
				//	ss_DumpVar($categories, '', true);
				}
				//ss_DumpVar($item, count($item['Children']), true);
			} else {				
				$rootAsset = getRow("SELECT * FROM assets WHERE as_type LIKE 'ShopSystem'");
				$pathresult = new Request("Asset.PathFromID",array(
					'as_id'	=>	$rootAsset['as_id']
				));
					
				$categories = ss_getShopCategories($rootAsset['as_id'], true, $pathresult->value);					
				if ($item['HasChildren']) {
					$item['Children'] = array_merge($item['Children'], $categories);
				} else {
					$menuStructure[$key]['HasChildren'] = count($categories)?true:false;
					$item['HasChildren'] = $menuStructure[$key]['HasChildren'];
					$menuStructure[$key]['Children'] = &$categories;
					$item['Children'] = $menuStructure[$key]['Children'];
				}												
			}
			//ss_DumpVarDie($item);			
		} 
		$menuName = "mm_menu_{$menuCount}_{$counter}";
		$menuID = "menuid_{$menuCount}_{$counter}";
		$displayChildren = true;
		if ($this->ATTRIBUTES['AST_MENU_DROPDOWNS'] == 0) {
			$displayChildren = false;
		}
		if (ss_optionExists("Hide Memeber's Menu")) {
			if (($item['RootParentAssetType'] == 'Members' or $item['as_type'] == 'Members') and !$isLoggedIn) {				
				$displayChildren = false;
			} 
		}
		
		if ($showDropDownMenu and 
			($currentAsset == $item['as_id'] or array_search($item['as_id'], $currentAssetParents) !== false)) {
			$displayChildren = false;
		}
		
		
		// If the menu has a drop down, add macromedia code for pop ups
		if (count($item['Children']) and $displayChildren) {
			
			$defineChildren = $this->standardDefineSubmenus($item['Children'],$menuName);
			$addChildren = $this->standardAddSubmenus($item['Children'],$menuName);
			
			$menuItemBorder = '';
			
			//window.{$menuName}.menuItemBgColor = '{$this->ATTRIBUTES['AST_MENU_DDITEMBGCOLOR']}';													
			if(strlen($this->ATTRIBUTES['AST_MENU_DDITEMBORDER'])) {
				$menuItemBorder = "		window.{$menuName}.menuItemBorder={$this->ATTRIBUTES['AST_MENU_DDITEMBORDER']};					
				
				";
			}
			$dropDowns .= 
				"<SCRIPT type=\"text/javascript\" LANGUAGE=\"Javascript1.2\">\n".
				"	<!--\n".
				"	if (!window.{$menuName}) {".
				"		{$defineChildren}".
				"		window.{$menuName} = new Menu(\"root\",{$this->ATTRIBUTES['AST_MENU_DDWIDTH']},{$this->ATTRIBUTES['AST_MENU_DDROWHEIGHT']},\"{$this->ATTRIBUTES['AST_MENU_DDFONTFAMILY']}\",{$this->ATTRIBUTES['AST_MENU_DDFONTSIZE']},\"{$this->ATTRIBUTES['AST_MENU_DDFONTCOLOR']}\",\"{$this->ATTRIBUTES['AST_MENU_DDFONTHIGHLIGHTCOLOR']}\",\"{$this->ATTRIBUTES['AST_MENU_DDBGCOLOR']}\",\"{$this->ATTRIBUTES['AST_MENU_DDBGHIGHLIGHTCOLOR']}\",\"left\",\"middle\",3,0,{$this->ATTRIBUTES['AST_MENU_DDTIMEOUT']},6,0,true,true,true,0,true,true);".
				"		window.{$menuName}.hideOnMouseOut=true;".
				"		window.{$menuName}.childMenuIcon=\"{$this->ATTRIBUTES['AST_MENU_DDARROW']}\";".
				"		window.{$menuName}.bgColor='{$this->ATTRIBUTES['AST_MENU_DDSEPARATORCOLOR']}';".
				"		window.{$menuName}.menuBorder=1;".								
				$menuItemBorder.								
				"		window.{$menuName}.menuLiteBgColor='{$this->ATTRIBUTES['AST_MENU_DDLIGHTBORDERCOLOR']}';".
				"		window.{$menuName}.menuHiliteBgColor='{$this->ATTRIBUTES['AST_MENU_DDBGHIGHLIGHTCOLOR']}';".
				"		window.{$menuName}.menuBorderBgColor='{$this->ATTRIBUTES['AST_MENU_DDBORDERCOLOR']}';".
				"		{$addChildren}".
				"	}\n".
				"	//-->\n".
				"</SCRIPT>\n".
				"";
			$lastMenu = $menuName;
		}
		$counter++;
	}
	$result['bodyStart'] = $dropDowns;
	
	// We need to 'write' the menus, so insert this before the </BODY> tag
	if ($lastMenu != NULL) {
		$result['bodyEnd'] = "<SCRIPT type=\"text/javascript\" LANGUAGE=\"Javascript1.2\">
						<!-- 
							{$lastMenu}.writeMenus();
						//-->
					</SCRIPT>";
	}
	
	
	// ---------------------------------
	// Display the main part of the menu
	// ---------------------------------

	// Figure out how to draw the table depending on orientation
	if (strtolower($this->ATTRIBUTES['AST_MENU_ORIENTATION']) == 'vertical') {
		$menuStart = "";		$menuFinish = "";
		$itemStart = "<TR>";	$itemFinish = "</TR>";
	} else if (strtolower($this->ATTRIBUTES['AST_MENU_ORIENTATION']) == 'horizontal') {
		$menuStart = "<TR>";	$menuFinish = "</TR>";
		$itemStart = "";		$itemFinish = "";
	}
	
	if (count($menuStructure)) {
		print $this->ATTRIBUTES['AST_MENU_BEFORE_HTML'];
	}
	print "<TABLE SUMMARY=\"Navigation\" ".
		" CELLSPACING=\"{$this->ATTRIBUTES['AST_MENU_CELLSPACING']}\"".
		" CELLPADDING=\"{$this->ATTRIBUTES['AST_MENU_CELLPADDING']}\"".
		" CLASS=\"{$this->ATTRIBUTES['AST_MENU_TABLECLASS']}\"".
		">".$menuStart;
	$counter = 1;
	$firstExpanded = false;
	$firstExtraExpanded = false;
	$separator = '';
	$stylePrefix = ''; // used for inserting 'EXPANDED_' into the menu styles
	foreach($menuStructure as $item) {		
		// figure out all the crap
		require('inc_prepareMenuItem.php');
		
		// Display the menu item	
		$aClass = $this->ATTRIBUTES['AST_MENU_LINKCLASS'];
        //allows a different class to be pulled in for parents with children
        $cellClass = $item['HasChildren'] ? $cellClass. ' ChildMenuCell' : $cellClass;

		print "{$separator}{$itemStart}<TD CLASS=\"{$cellClass}\" $cellTitle $cellMouseOver $cellMouseOut $cellClick>$startDiv<A NAME=\"$menuID\" ID=\"$menuID\" $linkMouseOver $linkMouseOut HREF=\"{$link}\" CLASS=\"{$this->ATTRIBUTES['AST_MENU_LINKCLASS']}\">{$itemDisplay}</A>$endDiv</TD>{$itemFinish}";

		// Display the separator
		if (strlen($this->ATTRIBUTES['AST_MENU_SEPARATOR']) > 0)
			$separator = "{$itemStart}<TD CLASS=\"{$this->ATTRIBUTES['AST_MENU_SEPARATORCELLCLASS']}\">{$this->ATTRIBUTES['AST_MENU_SEPARATOR']}</TD>{$itemFinish}";
		$counter++;
		
		if (($showDropDownMenu or $this->ATTRIBUTES['AST_MENU_EXPAND_CURRENT_ASSET']) and $selectedItem) {
			require('inc_expandedMenuItems.php');
		}
		
	}
	print $menuFinish."</TABLE>";
	
	if (count($menuStructure)) {
		print $this->ATTRIBUTES['AST_MENU_AFTER_HTML'];
	}
	
	
	$GLOBALS['BodyStart'] .= $result['bodyStart'];
	$GLOBALS['BodyEnd'] .= $result['bodyEnd'];

	
	
	return $result;
?>
