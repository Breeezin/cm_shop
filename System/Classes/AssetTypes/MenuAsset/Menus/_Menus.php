<?php

class Menus extends Plugin {
	

	function display() {
		$this->cache = 'Application';
		$this->display->layout = 'None';
		
		// Set some defaults for all menu types
		$this->param('AST_MENU_TYPE','standard');
		/*$this->param('root','/index.php');
		$this->param('uppercase','no');
		$this->param('lowercase','no');*/
	
		// Display specific menu type
		switch (strtolower($this->ATTRIBUTES['AST_MENU_TYPE'])) {
			case 'footer':	
				require('query_footer.php');
				return include('view_footer.php');
			case 'standard':	
				require('query_standard.php');
				return include('view_standard.php');
			default:
				$menu = 'Unknown menu type';
		}
		
	}
	
	function standardDefineSubMenus($menuStructure,$menuName,$currentLevel=1) {
		$result = '';
		$counter = 1;
		foreach ($menuStructure as $item) {
			if ((count($item['Children']) > 0) and ($currentLevel < $this->ATTRIBUTES['AST_MENU_DDMAXLEVELS'])) {
				$defineChildren = $this->standardDefineSubmenus($item['Children'],$menuName.$counter,$currentLevel+1);
				$addChildren = $this->standardAddSubmenus($item['Children'],$menuName.$counter,$currentLevel+1);
				if ($item['as_menu_name'] != null) {
					$itemDisplay = $item['as_menu_name'];
				} else {
					$itemDisplay = $item['as_name'];
				}
				$result .= 
					"{$defineChildren}".
					"window.{$menuName}{$counter} = new Menu(\"{$this->ATTRIBUTES['AST_MENU_DDBULLETPOINT']}".ss_JSStringFormat($itemDisplay)."\",{$this->ATTRIBUTES['AST_MENU_DDWIDTH']},{$this->ATTRIBUTES['AST_MENU_DDROWHEIGHT']},\"{$this->ATTRIBUTES['AST_MENU_DDFONTFAMILY']}\",{$this->ATTRIBUTES['AST_MENU_DDFONTSIZE']},\"{$this->ATTRIBUTES['AST_MENU_DDFONTCOLOR']}\",\"{$this->ATTRIBUTES['AST_MENU_DDFONTHIGHLIGHTCOLOR']}\",\"{$this->ATTRIBUTES['AST_MENU_DDBGCOLOR']}\",\"{$this->ATTRIBUTES['AST_MENU_DDBGHIGHLIGHTCOLOR']}\",\"left\",\"middle\",3,0,200,6,0,true,true,true,0,true,true);".
					"window.{$menuName}{$counter}.hideOnMouseOut=true;".
					"window.{$menuName}{$counter}.childMenuIcon=\"{$this->ATTRIBUTES['AST_MENU_DDARROW']}\";".
					"window.{$menuName}{$counter}.bgColor='{$this->ATTRIBUTES['AST_MENU_DDSEPARATORCOLOR']}';".
					"window.{$menuName}{$counter}.menuBorder=1;".
					"window.{$menuName}{$counter}.menuLiteBgColor='{$this->ATTRIBUTES['AST_MENU_DDLIGHTBORDERCOLOR']}';".
					"window.{$menuName}{$counter}.menuHiliteBgColor='{$this->ATTRIBUTES['AST_MENU_DDBGHIGHLIGHTCOLOR']}';".
					"window.{$menuName}{$counter}.menuBorderBgColor='{$this->ATTRIBUTES['AST_MENU_DDBORDERCOLOR']}';".
					"{$addChildren}".
					"";
			}
			$counter++;
		} 
		return $result;		
	}

	function standardAddSubmenus($menuStructure,$menuName,$currentLevel=1) {
		global $cfg;
		$result = '';
		$counter = 1;
		foreach ($menuStructure as $item) {

			$link = ltrim($item['Path'],'/');
			if (strtolower(substr($link,0,10)) == 'index.php/' ) {
				$link = substr($link,10);	
			}			
			$link = $cfg['currentServer'].$link;
			
			if ($item['as_menu_name'] != null) {
				$itemDisplay = $item['as_menu_name'];
			} else {
				$itemDisplay = $item['as_name'];
			}
			if (ss_optionExists('Allow Macrons')) $link = str_replace('&#', 'AndSharp', $link);			
			if ((count($item['Children']) > 0) and ($currentLevel < $this->ATTRIBUTES['AST_MENU_DDMAXLEVELS'])) {
				$result .= "window.{$menuName}.addMenuItem({$menuName}{$counter},\"location='".$link."'\");";
			} else {
				$result .= "window.{$menuName}.addMenuItem(\"{$this->ATTRIBUTES['AST_MENU_DDBULLETPOINT']}".ss_JSStringFormat($itemDisplay)."\",\"location='".$link."'\");";
			}
			$counter++;
		} 
		return $result;		
	}
	
	function exposeServices() {
		return array(
			'Menus.Display'	=>		array('method' => 'display'),
		);
	}
	
	
}
