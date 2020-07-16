<?php

	$Q_CreateTable = query("
		CREATE TABLE if not exists RandomImages (
			RaImID int(11) NOT NULL Default 1,
			RaImWindowTitle VARCHAR( 255 ),
			RaImSortOrder INT,
			RaImSearch LONGTEXT,
            RaImName varchar(255),
            RaImLink varchar(255),
            RaImImage varchar(255),
            RaImAlt LONGTEXT,
            RaImExpiryDate DATE,
			RaImAssetLink INT,
	 		PRIMARY KEY  (RaImID)
		) TYPE=InnoDB;
	");

     if (is_dir($this->imgDir) === false)
        mkdir($this->imgDir, 0764);
?>
