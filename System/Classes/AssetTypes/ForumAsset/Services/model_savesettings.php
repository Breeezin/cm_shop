<?php

	$this->param('Subscription','none');
	
	ss_RestrictPermission("IsLoggedIn");

	// Clean any old permissions that may apply
	$clean = query("
		DELETE FROM forum_thread_subscriptions
		WHERE fts_us_id = ".ss_getUserID()."
			AND fts_thr_id IS NULL
	");

	// Insert the new permission
	if (strstr($this->ATTRIBUTES['Subscription'],'fristpsot') === false) {
		if ($this->ATTRIBUTES['Subscription'] == 'none') {
			// Do nothing.. no special subscriptions
		} else {
			$set = query("
				INSERT INTO forum_thread_subscriptions
					(UserLink,fts_thr_id,fts_as_id,fts_first_post_only)
				VALUES
					(".ss_getUserID().",NULL,".$asset->getID().",0)
			");	
		}
	} else {
		// They only want the first posts	
		$set = query("
			INSERT INTO forum_thread_subscriptions
				(UserLink,fts_thr_id,fts_as_id,fts_first_post_only)
			VALUES
				(".ss_getUserID().",NULL,".$asset->getID().",1)
		");	
	}
	
	$data = array(
		'AssetPath'	=>	ss_withoutPreceedingSlash(ss_EscapeAssetPath($asset->getPath())),
	);
	$this->useTemplate('SaveSettings',$data);
	
?>
