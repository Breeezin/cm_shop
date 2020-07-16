<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'FILENAME','');
	ss_paramKey($asset->cereal,$this->fieldPrefix.'DOWNLOADBUTTON','');
	ss_paramKey($asset->cereal,$this->fieldPrefix.'DOWNLOADBUTTONOVER','');

	$fileName = $asset->cereal[$this->fieldPrefix.'FILENAME'];
	$filePath = ss_storeForAsset($asset->getID()).$fileName;
	
	if (file_exists($filePath)) {
		if (strlen($asset->cereal[$this->fieldPrefix.'DOWNLOADBUTTON'])) {
			$normal = ss_storeForAsset($asset->getID()).$asset->cereal[$this->fieldPrefix.'DOWNLOADBUTTON'];
			if (strlen($asset->cereal[$this->fieldPrefix.'DOWNLOADBUTTONOVER'])) {
				$rollover =  ss_storeForAsset($asset->getID()).$asset->cereal[$this->fieldPrefix.'DOWNLOADBUTTONOVER'];
				$name = md5(rand());
				
				// Image with rollover link
				print("<a href=\"".ss_withoutPreceedingSlash(ss_EscapeAssetPath($asset->getPath()))."\" onmouseout=\"document.images['{$name}'].src='".ss_JSStringFormat($normal)."'\" onmouseover=\"document.images['{$name}'].src='".ss_JSStringFormat($rollover)."'\">");
				print("<img id=\"$name\" border=\"0\" src=\"{$normal}\" alt=\"Download ".ss_HTMLEditFormat($asset->fields['as_name'])."\">");
			} else {
				// Image link
				print("<a href=\"".ss_withoutPreceedingSlash(ss_EscapeAssetPath($asset->getPath()))."\">");
				print("<img border=\"0\" src=\"{$normal}\" alt=\"Download ".ss_HTMLEditFormat($asset->fields['as_name'])."\">");
			}
		} else {
			// Text link
			print("<a href=\"".ss_withoutPreceedingSlash(ss_EscapeAssetPath($asset->getPath()))."\">");
			print("Download ".ss_HTMLEditFormat($asset->fields['as_name']));
		}
		
		print("</a>");
		
	} else {
		print("The file could not be found.");
	}
?>