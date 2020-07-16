<?php 
	//  Display a button to open the enquiry form 

	global $cfg;
	ss_paramKey($asset->cereal, "AST_TELLAFRIEND_BUTTONIMAGE", '');
	ss_paramKey($asset->cereal, "AST_TELLAFRIEND_BUTTONIMAGEOVER", '');
	ss_paramKey($asset->cereal, "AST_TELLAFRIEND_POPUP_WINDOW_HEIGHT", 550);
	ss_paramKey($asset->cereal, "AST_TELLAFRIEND_POPUP_WINDOW_WIDTH", 600);
	
	$normal = $asset->cereal['AST_TELLAFRIEND_BUTTONIMAGE'];
	$rollover = $asset->cereal['AST_TELLAFRIEND_BUTTONIMAGEOVER'];	
	//$GetPath = new Request("Asset.PathFromID", array('as_id' => $asset->id));
	//$assetPath = ss_withoutPreceedingSlash($GetPath->value);
	$assetPath = ss_URLEncodedFormat(ss_withoutPreceedingSlash($asset->getPath()));
	
	/// javascript : calculate the window's size and get the center position
	$onClick = "OnClick=\"w={$asset->cereal['AST_TELLAFRIEND_POPUP_WINDOW_WIDTH']};h={$asset->cereal['AST_TELLAFRIEND_POPUP_WINDOW_HEIGHT']};x=Math.round((screen.availWidth-w)/2);y = Math.round((screen.availHeight-h)/2); result=window.open('', 'tellafriend', 'width='+w+',height='+h+',toolbar=0,location=0,scrollbars=1,statusbar=1,menubar=0,resizable=1,top='+y+',left='+x+',screeenY='+y+',screenX='+x);\"";
	
	if (strlen($normal)) {
		if (strlen($rollover)) {
			print("<A ID=\"tellAFriendLink\" target=\"tellafriend\" HREF=\"{$cfg['currentServer']}$assetPath\" $onClick OnMouseOut=\"document.images['{$asset->fields['as_name']}'].src='".ss_storeForAsset($asset->getID())."{$asset->cereal['AST_TELLAFRIEND_BUTTONIMAGE']}'\" ONMOUSEOVER=\"document.images['{$asset->fields['as_name']}'].src='".ss_storeForAsset($asset->getID())."{$asset->cereal['AST_TELLAFRIEND_BUTTONIMAGEOVER']}'\"><IMG BORDER=\"0\" Name=\"{$asset->fields['as_name']}\" ALT=\"{$asset->fields['as_name']}\" SRC=\"".ss_storeForAsset($asset->getID())."{$asset->cereal['AST_TELLAFRIEND_BUTTONIMAGE']}\"></A>");
		} else {
			print("<A ID=\"tellAFriendLink\" target=\"tellafriend\" HREF=\"{$cfg['currentServer']}$assetPath\" $onClick ><IMG BORDER=\"0\" Name=\"{$asset->fields['as_name']}\" ALT=\"{$asset->fields['as_name']}\" SRC=\"".ss_storeForAsset($asset->getID())."{$asset->cereal['AST_TELLAFRIEND_BUTTONIMAGE']}\"></A>");
		}			
	} else {
		print("<A class=\"TellAFriendLink\" ID=\"tellAFriendLink\" target=\"tellafriend\" HREF=\"{$cfg['currentServer']}$assetPath\" $onClick>{$asset->fields['as_name']}</A>");
	}
?><SCRIPT LANGUAGE="Javascript">
	tellLink = document.getElementById('tellAFriendLink');
	tellLink.href = tellLink.href + "?TellingAbout=" + escape(document.location.href);
</SCRIPT>