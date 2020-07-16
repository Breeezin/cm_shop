<?php
	
	$assetID = $asset->getID();
	
	$data['as_name'] = $asset->fields['as_name'];
	$data['AssetPath'] = ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath()));
	
	$this->useTemplate('EmbedDisplay',$data);
?>		