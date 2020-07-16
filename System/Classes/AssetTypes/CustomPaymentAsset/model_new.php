<?php
	
	$Q_CreateTable = query("
        CREATE TABLE `CustomPayments` (
          `CuPaID` int(11) NOT NULL default '0',
          `CuPaTransactionLink` int(11) default NULL,
          `CuPaRecorded` datetime default NULL,
          `CuPaPaid` datetime default NULL,
          `CuPaAssetLink` int(11) default NULL,
          `CuPaTotal` double(16,2) default NULL,
          `CuPaCustomEmail` varchar(255) default NULL,
          `CuPaEmailContent` longtext,
          `CuPaSentEmail` tinyint(1) default NULL,
          `CuPaCurrencyCode` varchar(10) default NULL,
          PRIMARY KEY  (`CuPaID`)
        ) TYPE=InnoDB;
	");

?>
