<?php 
	
	$this->display->layout = "Administration2";
	
	$Q_ResourceHits = query("SELECT sts_as_id, Count(sts_id) AS Hits, Count(sts_client_id)+1 AS UsersHits
			FROM statistics, assets 
			WHERE sts_as_id = as_id AND as_system != 1 
			GROUP BY sts_as_id
			ORDER BY Hits DESC
	");	
	$hits = $Q_ResourceHits->columnValuesArray('Hits');
	$totalHits = 0;
	foreach ($hits as $hit) {
		$totalHits += $hit;
	}
	
	$Q_ReferralHits = query("
			SELECT sts_referrer, Count(sts_id) AS Hits, Count(sts_as_id) AS PagesHits, Count(sts_client_id)+1 AS UsersHits
			FROM statistics
			GROUP BY sts_referrer
			ORDER BY Hits DESC
	");	
	
	$hits = $Q_ReferralHits->columnValuesArray('Hits');
	$totalReferralHits = 0;
	foreach ($hits as $hit) {
		$totalReferralHits += $hit;
	}
	

	
	// check whether RandomImagesAsset exists or not
	$Q_RandomImages = query("SELECT as_name, as_id, Count(ris_id) AS TotalHits FROM assets, random_images_statistics WHERE as_type LIKE 'RandomImages' AND as_id = ris_as_id GROUP BY ris_as_id");
	
	/*
	$Q_UsersHits = query("
			SELECT St
			FROM statistics
			GROUP BY sts_referrer
			ORDER BY Hits DESC
	");	
	
	$hits = $Q_ReferralHits->columnValuesArray('Hits');
	$totalReferralHits = 0;
	foreach ($hits as $hit) {
		$totalReferralHits += $hit;
	}*/
	
?>