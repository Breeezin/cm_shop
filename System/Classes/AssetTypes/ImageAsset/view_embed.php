<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'STD');	
	
	if (array_key_exists('Size', $asset->ATTRIBUTES)) {
		print ("<IMG BORDER=\"0\" ALT=\"{$asset->fields['as_name']}\" SRC=\"index.php?act=ImageManager.get&Size={$this->ATTRIBUTES['Size']}&Image=".ss_storeForAsset($asset->getID()).$asset->cereal[$this->fieldPrefix.'STD']."\">");
		
	} else {
		print "<IMG BORDER=\"0\" ALT=\"{$asset->fields['as_name']}\" SRC=\"".ss_storeForAsset($asset->getID())."{$asset->cereal[$this->fieldPrefix.'STD']}\">";
	}
?>