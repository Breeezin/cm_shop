<?php
	$this->param('as_id');
	
	$Q_Asset = query("
		SELECT * FROM assets LEFT JOIN users ON assets.AssetAuthor = users.us_id
		WHERE as_id = ".safe($this->ATTRIBUTES['as_id'])."
			AND AssetReviewer = ".ss_getUserID()."
			AND AssetReview = 1
	");
	
	if ($Q_Asset->numRows() == 0) {
		die('Cannot review item.');	
	}
	
?>