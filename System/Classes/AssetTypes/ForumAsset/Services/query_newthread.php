<?php
	$this->param('Content','');			
	$this->param('Subject','');			
	$this->param('Submit','');			

	// Only allow guests to post if they're allowed to
	ss_paramKey($asset->cereal,$this->fieldPrefix.'ALLOW_GUEST_POSTS',0);
	if (!$asset->cereal[$this->fieldPrefix.'ALLOW_GUEST_POSTS']) {
		ss_RestrictPermission('IsLoggedIn');
	}

?>