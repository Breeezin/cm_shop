<?php
	
	$assetID = $asset->getID();
	if(!strlen($assetID)) {
		$assetID = $asset->ATTRIBUTES['as_id'];	
	}
	//ALTER TABLE `DataCollection_619` ADD `DaCoSearch` LONGTEXT;
	$Q_CreateTable = query("
		CREATE TABLE DataCollection_{$assetID} (
			DaCoID int(11) NOT NULL Default 1,
			DaCoSearch LONGTEXT,
			DaCoSortOrder INT,
	 		PRIMARY KEY  (DaCoID)
		) TYPE=InnoDB;
	");

    if (ss_OptionExists('Advanced Data Collection')){
        // need an approved, user name and timestamp field
        $Q_AlterTable = query("ALTER TABLE `DataCollection_{$assetID}` ADD (`DaCoApproved` INT,`DaCoUserName` LONGTEXT, `DaCoDate` DATETIME)");
    }
?>
