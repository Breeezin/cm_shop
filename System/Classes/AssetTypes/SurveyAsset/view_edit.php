<?php
	$assetID = $asset->getID();
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());

   	$Q_Count = getRow("
		SELECT count(*) as count
	 	FROM Survey_$assetID
	");

	
	$data = array(
        'Count'     => $Q_Count['count'],
		'FieldSet' => $this->fieldSet,
		'as_id' => $assetID,
		'as_name' => $asset->fields['as_name'],
		'imagesDirectory'	=>	$this->classDirectory.'/AssetTypes/'.$this->getClassName().'/Templates/Images/',
	);	
		
	$this->useTemplate("Edit",$data);
?>
