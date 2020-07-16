<?php
	$Q_CreateTable1 = query("
		CREATE TABLE IF NOT EXISTS EventTypes (
			EvTyID int(11) NOT NULL Default 1,
			EvTySortOrder INT(11),
            EvTyImage varchar(255),
            EvTyName varchar(255),
            EvTyColor char(6),
            EvTyDescription LONGTEXT,
	 		PRIMARY KEY  (EvTyID)
		) TYPE=InnoDB;	
	");

    $Q_Insert = query("
        Insert ignore into EventTypes values
            (0,0,NULL,'Out of Office','111111','Out of the office.'),
            (1,1,NULL,'Busy','222222','Please Do not disturb.'),
            (2,2,NULL,'Free','333333','Free.'),
            (3,3,NULL,'Requested Annual Leave','444444','Requested annual leave that is yet to be approved.'),
            (4,4,NULL,'Meetings','555555','Meeting with clients.'),
            (5,5,NULL,'Deadline','666666','Deadline reminder for a certain task.'),
            (6,6,NULL,'Task','777777','Important task that needs to be completed in this time period.')
    ");
    
	$Q_CreateTable2 = query("
		CREATE TABLE IF NOT EXISTS Events (
			EvID int(11) NOT NULL Default 1,
			EvSortOrder INT,
			EvSearch LONGTEXT,
			EvUsers LONGTEXT,
            EvStart DATETIME,
            EvEnd DATETIME,
            EvTypeLink int(11),
            EvDescription LONGTEXT,
            EvLocation TEXT,
	 		PRIMARY KEY  (EvID)
		) TYPE=InnoDB;	
	");

    
?>