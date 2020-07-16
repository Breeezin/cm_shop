<?
	$menuFields = array();

	$menuFields['Menu Type'] = array(
		'AST_MENU_TYPE'			=>	array('RestrictedTextField','Type','Standard',10,array('Standard','Footer'),'updateFieldSets(this)',true),
	);
	$standardSettings =  array(
		'AST_MENU_ORIENTATION'	=>	array('RestrictedTextField','Orientation','Vertical',10,array('Vertical','Horizontal'),null,true),
		'AST_MENU_ROOT_ASSETID'	=>	array('AssetTreeField','Root Asset','1',30,null,null,false),
		'AST_MENU_ROOT_ASSETLEVEL'	=>	array('IntegerField','Root Asset Level','(nothing)',10,null,null,false),
		'AST_MENU_SEPARATOR'	=>	array('TextField','Separator HTML','(nothing)',30,null,null,false),
		'AST_MENU_TABLECLASS'	=>	array('TextField','Table Class','MenuTable',30,null,null,false),
		'AST_MENU_FIRSTCELLCLASS'	=>	array('TextField','First Cell Class','(nothing)',30,null,null,false),
		'AST_MENU_OTHERCELLCLASS'	=>	array('TextField','Other Cell Class','(nothing)',30,null,null,false),
		'AST_MENU_ROLLOVERCLASS'	=>	array('TextField','Cell Rollover Class','(nothing)',30,null,null,false),
		'AST_MENU_LINKCLASS'	=>	array('TextField','Text Link Class','MenuLink',30,null,null,false),
		'AST_MENU_SEPARATORCELLCLASS'	=>	array('TextField','Separator Cell Class','MenuSparatorCell',30,null,null,false),
		'AST_MENU_CELLPADDING'	=>	array('IntegerField','Cell Padding','0',10,null,null,false),
		'AST_MENU_CELLSPACING'	=>	array('IntegerField','Cell Spacing','0',10,null,null,false),		
		'AST_MENU_BEFORE_HTML'	=>	array('MemoField','Before HTML','',10,null,null,false),		
		'AST_MENU_AFTER_HTML'	=>	array('MemoField','After HTML','',10,null,null,false),		
		'AST_MENU_EXPAND_CURRENT_ASSET'	=>	array('DescriptiveIntegerField','Expand Current Asset?','0',10,array('No'=>0,'Yes'=>1),null,true),
		'AST_MENU_EXPANDED_LINKCLASS'	=>	array('TextField','Expanded Text Link Class','MenuLink',30,null,null,false),
		'AST_MENU_EXPANDED_ROLLOVERCLASS'	=>	array('TextField','Expanded Rollover Class','(nothing)',30,null,null,false),
		'AST_MENU_EXPANDED_FIRSTCELLCLASS'	=>	array('TextField','Expanded First Cell Class','(nothing)',30,null,null,false),
		'AST_MENU_EXPANDED_OTHERCELLCLASS'	=>	array('TextField','Expanded Other Cell Class','(nothing)',30,null,null,false),
		'AST_MENU_EXPANDED_SEPARATORCELLCLASS'	=>	array('TextField','Expanded Separator Cell Class','MenuSparatorCell',30,null,null,false),
		'AST_MENU_EXTRA_EXPANDED_LINKCLASS'	=>	array('TextField','Extra Expanded Text Link Class','MenuLink',30,null,null,false),
		'AST_MENU_EXTRA_EXPANDED_ROLLOVERCLASS'	=>	array('TextField','Extra Expanded Rollover Class','(nothing)',30,null,null,false),
		'AST_MENU_EXTRA_EXPANDED_FIRSTCELLCLASS'	=>	array('TextField','Extra Expanded First Cell Class','(nothing)',30,null,null,false),
		'AST_MENU_EXTRA_EXPANDED_OTHERCELLCLASS'	=>	array('TextField','Extra Expanded Other Cell Class','(nothing)',30,null,null,false),
		'AST_MENU_EXTRA_EXPANDED_SEPARATORCELLCLASS'	=>	array('TextField','Extra Expanded Separator Cell Class','MenuSparatorCell',30,null,null,false),
		'AST_MENU_TEXT_ONLY'	=>	array('DescriptiveIntegerField','Text Only?','0',10,array('No'=>0,'Yes'=>1),null,true),
	);
	
	if (ss_optionExists("Show Menu Description")) {
		$standardSettings['AST_MENU_SHOW_DESCRIPTION'] = array('TextField','Description Element ID','',30,null,null,false);	
	}		
	
	$menuFields['Standard Menu Settings'] = $standardSettings;

	$menuFields['Drop Down Menu Settings'] = array(
		'AST_MENU_DROPDOWNS'	=>	array('DescriptiveIntegerField','Display Dropdowns?',1,10,array('Yes'=>1,'No'=>0),null,true),
		'AST_MENU_DDWIDTH'	=>	array('IntegerField','Width','120',10,null,null,false),
		'AST_MENU_DDROWHEIGHT'	=>	array('IntegerField','Row Height','12',10,null,null,false),
		'AST_MENU_DDOFFSETX'	=>	array('IntegerField','Offset X','100 (Vertical Menus) or 0 (Horizontal Menus)',10,null,null,false),
		'AST_MENU_DDOFFSETY'	=>	array('IntegerField','Offset Y','0 (Vertical Menus) or 20 (Horizontal Menus)',10,null,null,false),
		'AST_MENU_DDFONTCOLOR'	=>	array('TextField','Font Colour','#000000',10,null,null,false),
		'AST_MENU_DDFONTHIGHLIGHTCOLOR'	=>	array('TextField','Font Highlight Colour','#000000',10,null,null,false),
		'AST_MENU_DDFONTSIZE'	=>	array('IntegerField','Font Size','9',10,null,null,false),
		'AST_MENU_DDFONTFAMILY'	=>	array('TextField','Font Family','Arial, Helvetica, sans-serif',30,null,null,false),		
		
		'AST_MENU_DDITEMBORDER'	=>	array('IntegerField','Item Border','(nothing)',null,null,null,false),
		/*
		'AST_MENU_DDITEMBGCOLOR'	=>	array('TextField','Item Background Colour','(nothing)',null,null,null,false),
		*/
		'AST_MENU_DDBGCOLOR'	=>	array('TextField','Background Colour','#FFFFFF',10,null,null,false),		
		'AST_MENU_DDBGHIGHLIGHTCOLOR'	=>	array('TextField','Background Highlight Colour','#EFEFEF',10,null,null,false),
		'AST_MENU_DDBORDERCOLOR'	=>	array('TextField','Border Colour','#000000',10,null,null,false),
		'AST_MENU_DDLIGHTBORDERCOLOR'	=>	array('TextField','Light Border Colour','#FFFFFF',10,null,null,false),
		'AST_MENU_DDSEPARATORCOLOR'	=>	array('TextField','Separator Colour','(nothing)',10,null,null,false),
		'AST_MENU_DDARROW'	=>	array('TextField','Arrow Path','System/Classes/AssetTypes/MenuAsset/Menus/arrows.gif',30,null,null,false),
		'AST_MENU_DDBULLETPOINT'	=>	array('TextField','Bullet Point','(nothing)',10,null,null,false),
		'AST_MENU_DDTIMEOUT'	=>	array('IntegerField','Timeout','200',10,null,null,false),		
		'AST_MENU_DDMAXLEVELS'	=>	array('IntegerField','Max Levels','100',10,null,null,false),		
	);	
	
	$menuFields['Footer Menu Settings'] = array(
		'AST_MENU_FOOTER_ROOT_ASSETID'	=>	array('IntegerField','Root Asset ID','1',10,null,null,false),
		'AST_MENU_FOOTER_ROOT_ASSETLEVEL'	=>	array('IntegerField','Root Asset Level','(nothing)',10,null,null,false),
		'AST_MENU_FOOTER_LINKCLASS'	=>	array('TextField','Text Link Class','FooterLink',30,null,null,false),
		'AST_MENU_FOOTER_SEPARATOR'	=>	array('TextField','Separator HTML',' | ',30,null,null,false),
		'AST_MENU_FOOTER_LINKSPERROW'	=>	array('IntegerField','Links Per Row','1024',10,null,null,false),
		'AST_MENU_FOOTER_LINKSPERFIRSTROW'	=>	array('IntegerField','Links Per First Row','1024',10,null,null,false),
	);
	
?>
