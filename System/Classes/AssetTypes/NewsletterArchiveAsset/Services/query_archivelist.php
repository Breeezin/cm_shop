<?php

	$Q_Archive = query("
		SELECT * FROM newsletter_archive
		WHERE na_as_id = ".$asset->getID()."
			AND (na_current IS NULL OR na_current = 0)
		ORDER BY na_sent DESC
	");

	$Q_Current = query("
		SELECT * FROM newsletter_archive
		WHERE na_as_id = ".$asset->getID()."
			AND na_current = 1
	");
	
?>