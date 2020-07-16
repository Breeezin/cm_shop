<?php
// drop tables if wanted. Good for testig, by no backup if deleted in live site.
	
    /*
    
    Not planning to give them a field set
	$Q_Scheduler = getRow("SELECT * FROM Scheduler LIMIT 1");
	if (!is_array($Q_Scheduler)) {
		$Q_AddNewRow = query("INSERT INTO Scheduler (ScID) VALUES (0)");
		$initAdd = true;	
		$Q_Scheduler = getRow("SELECT * FROM Scheduler LIMIT 1");
	}
	
	$selectOptions = "";
	
	foreach ($Q_Scheduler as $key => $value) {
		$selectOptions = ss_comma($selectOptions).str_replace('Sc','',$key);		
	}
	if (strlen($selectOptions)) {
		$Q_DeleteOptions = query("DELETE FROM select_field_options WHERE sfo_parent_uuid IN ($selectOptions)");
	}
    
    */
    
	$Q_DeleteScheduler = query("DROP TABLE ScheduledEvents");
	$Q_DeleteEvents = query("DROP TABLE Events");
	$Q_DeleteEventTypes = query("DROP TABLE EventTypes");
	
?>