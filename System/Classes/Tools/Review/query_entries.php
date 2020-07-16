<?php

	$Q_ReviewAssets = query("
		SELECT * FROM assets LEFT JOIN users ON assets.AssetAuthor = users.us_id
		WHERE AssetReview = 1
			AND AssetReviewer = ".ss_getUserID()."
		ORDER BY as_last_modified
	");
	
?>