<?php
	$this->param('thr_id');
	$this->param('fm_id');
	$this->param('Submit','');
	
	// Only allow guests to post if they're allowed to
	ss_paramKey($asset->cereal,$this->fieldPrefix.'ALLOW_GUEST_POSTS',0);
	if (!$asset->cereal[$this->fieldPrefix.'ALLOW_GUEST_POSTS']) {
		ss_RestrictPermission('IsLoggedIn');
	}
	
	// Grab the thread and message that they are replying to
	$Q_Reply = query("
		SELECT * FROM forum_messages, forum_threads
		WHERE fm_id = ".safe($this->ATTRIBUTES['fm_id'])."
			AND fm_thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
			AND thr_id = ".safe($this->ATTRIBUTES['thr_id'])."
	");

	// If the thread is locked.. then just send them back to the home page.
	// They shouldn't be able to click any buttons that would get them to this
	// page if the thread is locked, so they're obviously trying to type in a
	// URL and trick they system into letting them reply
	if ($Q_Reply->numRows()) {
		$reply = $Q_Reply->fetchRow();		
		if ($reply['thr_locked']) {
			locationRelative('');	
		}
	} else {
		locationRelative('');
	}
	
	// If the content isn't existing then pre-fill it with a quote from the message
	// they are replying to. Note we are using "U" to get ungreedy regular expression 
	// matching
	$default = '';
	if (array_key_exists('Quote',$this->ATTRIBUTES)) {
		$default = "[QUOTE=".ss_HTMLEditFormat($reply['fm_poster_firstname']).' '.ss_HTMLEditFormat($reply['fm_poster_lastname']).']'.
			preg_replace("|\[quote=[^]]*\].*\[/quote\]|iUs",'',$reply['fm_content']).
			"[/QUOTE]";
	}
	$this->param('Content',$default);
?>