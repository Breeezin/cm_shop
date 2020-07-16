<?php

	ss_paramKey($asset->cereal,$this->fieldPrefix.'DEFAULT_PAGE','');

	$content = $asset->cereal[$this->fieldPrefix.'DEFAULT_PAGE'];
	
	$user = ss_loggedInUsersID();
	if ($user !== false) {
		// If we're logged in, try to find some specific content
		$Q_Content = query("
			SELECT * FROM user_specific_page_pages
			WHERE pag_us_id = ".escape($user)."
				AND pag_as_id = ".$asset->getID()."
		");
		if ($Q_Content->numRows()) {
			$row = $Q_Content->fetchRow();
			$content = $row['pag_content'];	
		}
	}
	
?>