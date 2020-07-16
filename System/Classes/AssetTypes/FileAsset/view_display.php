<?php

	ss_paramKey($asset->cereal,$this->fieldPrefix.'FILENAME','');

	$fileName = $asset->cereal[$this->fieldPrefix.'FILENAME'];
	$filePath = ss_storeForAsset($asset->getID()).$fileName;
	
	if (file_exists($filePath) and strlen($fileName)) {
		locationRelative($filePath);
	} else {
		print("The file could not be found.");
	}
?>