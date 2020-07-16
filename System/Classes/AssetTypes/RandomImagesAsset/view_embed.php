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
		ss_paramKey($data['Image'], 'target', '_blank');
	} else {
		$data['Image'] = array();
	}
	global $cfg;
	$data['AssetPath'] = $cfg['currentServer'].ss_withoutPreceedingSlash($asset->getPath());
	$data['Directory'] = ss_storeForAsset($asset->getID());

	if (array_key_exists('uuid',$data['Image'])) {
		$assetID = $asset->getID();
		$Q_Stats = query("INSERT INTO random_images_display_statistics
			(rids_timestamp, rids_image_uuid, rids_as_id)	
			VALUES
			(Now(), '{$data['Image']['uuid']}', {$assetID})
		");
	}
	
	print $this->processTemplate('Embed', $data);

?>