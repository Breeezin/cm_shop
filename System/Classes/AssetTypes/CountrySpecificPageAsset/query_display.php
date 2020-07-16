<?php

	$countryCode = ss_getCountry();
	if ($countryCode === null) {
		$countryCode = '\'\'';	
	} else {
		$countryCode = "'".escape($countryCode)."'";
	}
	
	// First try to find the page for the user's country
	$Q_Page = query("
		SELECT * FROM CountrySpecificPage_Pages
		WHERE pag_as_id = ".$asset->getID()."
			AND PaCountryCode = $countryCode
	");
	
	if ($Q_Page->numRows()) {
		// Found one, so use it.
		$row = $Q_Page->fetchRow();
		$content = $row['pag_content'];
	} else {
		// Couldn't find one for the user's country.. so look for a default one
		$Q_Page = query("
			SELECT * FROM CountrySpecificPage_Pages
			WHERE pag_as_id = ".$asset->getID()."
				AND PaCountryCode IS NULL
		");
		if ($Q_Page->numRows()) {
			// Found a default page, so use it.
			$row = $Q_Page->fetchRow();
			$content = $row['pag_content'];
		} else {
			// Not even a default page, not gonna show anything
			$content = '';
		}
	}
	
?>