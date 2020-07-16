<?php
	$assetID = $asset->getID();
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());
	
	$data = array(
		'FieldSet' => $this->fieldSet, 
		'as_id' => $assetID,
		'as_name' => $asset->fields['as_name'],
		'imagesDirectory'	=>	$this->classDirectory.'/AssetTypes/'.$this->getClassName().'/Templates/Images/',
	);	
		
	$this->useTemplate("Edit",$data);
?>