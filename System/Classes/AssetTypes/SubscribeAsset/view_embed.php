<?php 
	//Display a button to open the enquiry form 
	
	ss_paramKey($asset->cereal,$this->fieldPrefix.'BUTTONIMAGE','');
	ss_paramKey($asset->cereal,$this->fieldPrefix.'BUTTONIMAGEOVER','');
	
	global $cfg;
	$normal = $asset->cereal[$this->fieldPrefix.'BUTTONIMAGE'];
	$rollover = $asset->cereal[$this->fieldPrefix.'BUTTONIMAGEOVER'];	
	$assetPath = ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath()));
	
	if (strlen($normal)) {
		$normal = ss_storeForAsset($asset->getID()).$normal;
		if (strlen($rollover)) {
			$rollover = ss_storeForAsset($asset->getID()).$rollover;
			print("<a id=\"subscribeNewsletterLink\" href=\"$assetPath\" onmouseout=\"document.images['".ss_HTMLEditFormat($asset->fields['as_name'])."'].src='".ss_JSStringFormat($normal)."'\" onmouseover=\"document.images['".ss_HTMLEditFormat($asset->fields['as_name'])."'].src='".ss_JSStringFormat($rollover)."'\"><img border=\"0\" name=\"{$asset->fields['as_name']}\" alt=\"{$asset->fields['as_name']}\" src=\"".ss_JSStringFormat($normal)."\" /></a>");
		} else {
			print("<a href=\"$assetPath\"><img border=\"0\" alt=\"".ss_HTMLEditFormat($asset->fields['as_name'])."\" src=\"{$normal}\" /></a>");
		}			
	} else {
		print("<a href=\"$assetPath\" class=\"SubscribeLink\">".ss_HTMLEditFormat($asset->fields['as_name'])."</a>");
	}

?>