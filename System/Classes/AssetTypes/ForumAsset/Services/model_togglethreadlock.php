<?php
	
	ss_RestrictPermission('IsLoggedIn');

	if (!$this->isAdmin) {
		// If they're not allowed.. send them somewhere else
		locationRelative('');	
	}

	$this->param('thr_id');
	$this->param('Status');

	startTransaction();
	
	/*// First grab the thread
	$Thread = getRow("
		SELECT * FROM forum_threads
		WHERE thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
	");
	
	// Now find out the new values
	$lockStatus = $Thread['thr_locked'];*/
	
	// update the thread
	$Q_Update = query("
		UPDATE forum_threads
		SET thr_locked = ".safe($this->ATTRIBUTES['Status'])."
		WHERE thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
	");
	
	commit();
	locationRelative(ss_withoutPreceedingSlash($asset->getPath()).'?Service=ViewThread&thr_id='.$this->ATTRIBUTES['thr_id']);
	
	
?>