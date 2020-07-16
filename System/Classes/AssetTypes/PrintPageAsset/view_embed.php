<?php
	//Display a button to open the enquiry form 
	
	global $cfg;
	ss_paramKey($asset->cereal, "AST_PRINTPAGE_BUTTONIMAGE", '');
	ss_paramKey($asset->cereal, "AST_PRINTPAGE_BUTTONIMAGEOVER", '');
	ss_paramKey($asset->cereal, "AST_PRINTPAGE_POPUP_WINDOW_HEIGHT", 550);
	ss_paramKey($asset->cereal, "AST_PRINTPAGE_POPUP_WINDOW_WIDTH", 660);
	
	
	
	$this->param('MainAssetPath',"");
	
	$normal = $asset->cereal['AST_PRINTPAGE_BUTTONIMAGE'];
	$rollover = $asset->cereal['AST_PRINTPAGE_BUTTONIMAGEOVER'];	
	//$GetPath = new Request("Asset.PathFromID", array('as_id' => $asset->id));
	//$assetPath = ss_withoutPreceedingSlash($GetPath->value);
	$assetPath = ss_URLEncodedFormat(ss_withoutPreceedingSlash($asset->getPath()));
	
	/// javascript : calculate the window's size and get the center position
	$onClick = "OnClick=\"w={$asset->cereal['AST_PRINTPAGE_POPUP_WINDOW_WIDTH']};h={$asset->cereal['AST_PRINTPAGE_POPUP_WINDOW_HEIGHT']};x=Math.round((screen.availWidth-w)/2);y = Math.round((screen.availHeight-h)/2); result=window.open('', 'printpage', 'width='+w+',height='+h+',toolbar=0,location=0,scrollbars=1,statusbar=1,menubar=0,resizable=1,top='+y+',left='+x+',screeenY='+y+',screenX='+x);\"";
	
	//ss_DumpVar($asset);
	//$link = $cfg['currentSite'].$_REQUEST['REQUEST_URI'];	
	$layout = 'print';
	if (array_key_exists('Layout', $asset->ATTRIBUTES)) {
		$layout = $asset->ATTRIBUTES['Layout'];
	}
	$mainlayout = 'print';
	if (array_key_exists('MainLayout', $asset->ATTRIBUTES)) {
		$mainlayout = $asset->ATTRIBUTES['MainLayout'];
	}
    if (ss_OptionExists('Use Print Layout') and isset($asset->layout['LYT_LAYOUT'])){
        $layout = $asset->layout['LYT_LAYOUT'];
        $mainlayout = $asset->layout['LYT_LAYOUT'];
    }

	$link = getBackURL();
	if (strlen($normal)) {
		if (strlen($rollover)) {
			print("<A target=\"printpage\" HREF=\"$link&Layout={$layout}&MainLayout={$mainlayout}\" $onClick OnMouseOut=\"document.images['{$asset->fields['as_name']}'].src='".ss_storeForAsset($asset->getID())."{$asset->cereal['AST_PRINTPAGE_BUTTONIMAGE']}'\" ONMOUSEOVER=\"document.images['{$asset->fields['as_name']}'].src='".ss_storeForAsset($asset->getID())."{$asset->cereal['AST_PRINTPAGE_BUTTONIMAGEOVER']}'\"><IMG BORDER=\"0\" Name=\"{$asset->fields['as_name']}\" ALT=\"{$asset->fields['as_name']}\" SRC=\"".ss_storeForAsset($asset->getID())."{$asset->cereal['AST_PRINTPAGE_BUTTONIMAGE']}\"></A>");
		} else {
			print("<A target=\"printpage\" HREF=\"$link&Layout=$layout&MainLayout=$mainlayout\" $onClick ><IMG BORDER=\"0\" Name=\"{$asset->fields['as_name']}\" ALT=\"{$asset->fields['as_name']}\" SRC=\"".ss_storeForAsset($asset->getID())."{$asset->cereal['AST_PRINTPAGE_BUTTONIMAGE']}\"></A>");
		}			
	} else {
		print("<A class=\"PrintPageLink\" target=\"printpage\" HREF=\"$link&Layout=$layout&MainLayout=$mainlayout\" $onClick>{$asset->fields['as_name']}</A>");
	}

?>
