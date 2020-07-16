<?php
	ss_paramKey($asset->cereal,$this->fieldPrefix.'FORM');	
	$images = array();
	$data = array();
	
	if (strlen($asset->cereal[$this->fieldPrefix.'FORM'])) {
		$images = unserialize($asset->cereal[$this->fieldPrefix.'FORM']);
	}
	$counter = count($images);
	if ($counter) {
		$randomNum = rand(0,$counter-1);
		$data['Image'] = $images[$randomNum];
	} else {
		$data['Image'] = array();
	}
	global $cfg;
	$data['AssetPath'] = $cfg['currentServer'].ss_withoutPreceedingSlash($asset->getPath());
	$data['Directory'] = ss_storeForAsset($asset->getID());
	
	print $this->processTemplate('Embed', $data);

?>