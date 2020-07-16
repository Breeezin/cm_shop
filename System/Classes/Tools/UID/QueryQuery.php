<?php
/*
	function dec2hex($dec) {
		$hex = ($dec == 0 ? '0' : '');
		while ($dec > 0) {
			$hex = dechex($dec - floor($dec / 16) * 16) . $hex;
			$dec = floor($dec / 16);
		}
		return $hex;
	}
*/

	$this->param('Count','1');

	// Lock the table	
	startTransaction();	

	// Get the UID and hex it
	$Q_GetUID = query("
		SELECT * FROM UID
	");
	
	$row = $Q_GetUID->fetchRow();

	$UID1 = $row['UID1'];
	$UID2 = $row['UID2'];
	
	$UIDs = array();

	for ($i=0; $i < $this->ATTRIBUTES['Count']; $i++) {
	
		
		// Convert dec to hex
		$dec = $UID1;	
		$hex1 = ($dec == 0 ? '0' : '');
		while ($dec > 0) {
			$hex1 = dechex($dec - floor($dec / 16) * 16) . $hex1;
			$dec = floor($dec / 16);
		}
		
		// Convert dec to hex
		$dec = $UID2;
		$hex2 = ($dec == 0 ? '0' : '');
		while ($dec > 0) {
			$hex2 = dechex($dec - floor($dec / 16) * 16) . $hex2;
			$dec = floor($dec / 16);
		}

		// Join them together
		$UID = strtoupper($hex1 . "_" . $hex2);

		// Add it to the array
		array_push($UIDs,$UID);

		// Check for overflow ---
		if ($UID2 == 4294967295) {
			$UID1++;
			$UID2 = 0;
		} else {
			$UID2++;
		}
	}
	
	// Update the table
	$Q_UpdateUID = query("
		UPDATE UID
		SET UID1 = $UID1,
			UID2 = $UID2	
	");

	// Unlock the table		
	commit();
	
	if ($this->ATTRIBUTES['Count'] == 1) 
		return $UID;
	else 
		return $UIDs;
?>