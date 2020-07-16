<?php
	$this->param('UUID');
	ss_paramKey($asset->cereal,$this->fieldPrefix.'FORM');	
	
	$images = array();
	
	
	if (strlen($asset->cereal[$this->fieldPrefix.'FORM'])) {
		$images = unserialize($asset->cereal[$this->fieldPrefix.'FORM']);
	}
	$selectedImage = null;
	foreach ($images as $image) {
		if ($image['uuid'] ==  $this->ATTRIBUTES['UUID']) {
			$selectedImage = $image;
			break;
		}
	}
	
	if ($selectedImage == null) {
		die("Requested image link does not exist anymore.");
	}
	$assetID = $asset->getID();
	// record the link for statistics
	$Q_Stats = query("INSERT INTO random_images_statistics
					(ris_timestamp, ris_link, ris_imagelink, ris_as_id)	
					VALUES
					(Now(), '".escape($selectedImage['link'])."', '{$this->ATTRIBUTES['UUID']}', {$assetID})
				");
		
?>
<SCRIPT language="javascript">
	document.location = "http://<?=$selectedImage['link']?>";
</SCRIPT>