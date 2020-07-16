<?php
	
	ss_RestrictPermission('IsLoggedIn');

	if (!$this->isAdmin) {
		// If they're not allowed.. send them somewhere else
		locationRelative('');	
	}

	$this->param('thr_id');
	$this->param('fm_id');

	startTransaction();
	
	// First we'll delete the message
	$Q_DeleteMessage = query("
		DELETE FROM forum_messages
		WHERE fm_thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
			AND fm_id = ".safe($this->ATTRIBUTES['fm_id'])."
	");

	if ($this->ATTRIBUTES['fm_id'] == 1) {
		// If you delete the first message, you delete the entire thread
		$Q_DeleteMessages = query("
			DELETE FROM forum_messages
			WHERE fm_thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
		");	
		$Q_DeleteThread = query("
			DELETE FROM forum_threads
			WHERE thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
				AND thr_as_id = ".safe($asset->getID())."
		");	
		commit();
		locationRelative(ss_withoutPreceedingSlash($asset->getPath()).'?Service=ThreadList');
	} else {
		// Check if the message we deleted was the last message in the thread or not
		$Thread = getRow("
			SELECT * FROM forum_threads
			WHERE thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
				AND thr_as_id = ".safe($asset->getID())."
		");
		if ($Thread['thr_last_thr_id'] == $this->ATTRIBUTES['fm_id']) {
			// .. if it was the last message.. find what the new last msg is
			$LastMessage = getRow("
				SELECT MAX(fm_id) AS NewMax FROM forum_messages
				WHERE fm_thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
			");
			// and update it
			$Q_UpdateThread = query("
				UPDATE forum_threads
				SET thr_last_thr_id = {$LastMessage['NewMax']}
				WHERE thr_id = {$this->ATTRIBUTES['thr_id']}
			");	
		}
	}

	commit();
	locationRelative(ss_withoutPreceedingSlash($asset->getPath()).'?Service=ViewThread&thr_id='.$this->ATTRIBUTES['thr_id']);
	
?>