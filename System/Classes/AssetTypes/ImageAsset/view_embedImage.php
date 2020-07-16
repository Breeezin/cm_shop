<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'STD');	
	
	if (array_key_exists('Size', $asset->ATTRIBUTES)) {		
		locationRelative("index.php?act=ImageManager.get&Size={$this->ATTRIBUTES['Size']}&Image=".ss_storeForAsset($asset->getID()).$asset->cereal[$this->fieldPrefix.'STD']);
	} else {
		locationRelative(ss_storeForAsset($asset->getID()).$asset->cereal[$this->fieldPrefix.'STD']);
		//print "<IMG BORDER=\"0\" ALT=\"{$asset->fields['as_name']}\" SRC=\"".ss_storeForAsset($asset->getID())."{$asset->cereal[$this->fieldPrefix.'STD']}\">";
	}
?>