<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'FILENAME','');	

	$fileName = $asset->cereal[$this->fieldPrefix.'FILENAME'];
	$filePath = ss_storeForAsset($asset->getID()).$fileName;
	
	print('comming soon');
?>