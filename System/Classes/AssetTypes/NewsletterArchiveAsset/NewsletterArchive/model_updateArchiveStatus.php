<?php

	$this->param('na_id');
	$this->param('ArchiveStatus');
	$this->param('BackURL');
	
	if (strpos($this->ATTRIBUTES['ArchiveStatus'],'Current') !== false) {
		$archiveAsset = stri_replace('Current','',$this->ATTRIBUTES['ArchiveStatus']); 
		$current = 1;
		$Q_ClearOtherCurrent = query("
			UPDATE newsletter_archive
			SET na_current = null
			WHERE na_as_id = $archiveAsset
		");	
	} else {			
		$archiveAsset = $this->ATTRIBUTES['ArchiveStatus'];
		$current = 'null';
	}
	
	$Q_Update = query("
		UPDATE newsletter_archive
		SET na_as_id = $archiveAsset,
			na_current = $current
		WHERE na_id = {$this->ATTRIBUTES['na_id']}
	");
	
?>
