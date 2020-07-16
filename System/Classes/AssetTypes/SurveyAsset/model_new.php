<?php
	
	$assetID = $asset->getID();
	if(!strlen($assetID)) {
		$assetID = $asset->ATTRIBUTES['as_id'];	
	}

	$Q_CreateTable = query("
		CREATE TABLE Survey_{$assetID} (
			efs_id int(11) NOT NULL Default 1,
			SuWindowTitle VARCHAR( 255 ),
			SuSearch LONGTEXT,
			SuSortOrder INT,
            efs_timestamp DATETIME,
	 		PRIMARY KEY  (efs_id)
		) TYPE=InnoDB;
	");
//			SuWindowTitle VARCHAR( 255 ),
//			SuSearch LONGTEXT,
//			SuSortOrder INT,

?>
