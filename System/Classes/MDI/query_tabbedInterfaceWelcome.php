<?php

	// See if they have exceeded their page limit  >;-)
	$pageAssetType = getRow("
		SELECT * FROM asset_types
		WHERE at_name LIKE 'Page'
	");
	
	$pageCount = getRow("
		SELECT COUNT(*) AS PageCount FROM assets
		WHERE as_type LIKE 'Page'
			AND as_owner_au_id != 0
			AND
			(as_deleted IS NULL
				OR as_deleted = 0)
			AND (as_hidden IS NULL
				OR as_hidden = 0)
	");
	

?>