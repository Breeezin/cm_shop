<?php
	

	/*if (!array_key_exists("DisableOutputBuffering",$_REQUEST)) {
		location('index.php?'.$_SERVER['QUERY_STRING'].'&DisableOutputBuffering=1');	
	}*/

	$this->param('Code');
	
	// Load the tabbed file
	//$targetDir = ss_withTrailingSlash(dirname($_SERVER['SCRIPT_FILENAME'])).'Custom/Cache/Incoming/';
	//$Q_Users = ss_ParseTabDelimitedFile($targetDir.$this->ATTRIBUTES['DataFile']);
	
	$Q_ImportUsers = query("
		SELECT imu_id FROM import_users
		WHERE imu_user_code LIKE '".escape($this->ATTRIBUTES['Code'])."'
	");
	
?>