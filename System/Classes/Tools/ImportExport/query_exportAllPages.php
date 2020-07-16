<?php

	$Q_Assets = query("
		SELECT * FROM assets
		WHERE as_type LIKE 'Page'
			AND (as_deleted IS NULL OR as_deleted = 0)
	");
	
?>