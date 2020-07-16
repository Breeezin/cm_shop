<?php
	
	$this->param("CurrentPage", 1);
	
	$assetID = $asset->getID();
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());
	
	// get the number of items to display per page
	$perEmbed = $asset->cereal[$this->fieldPrefix.'PANELITEMS'];
	
	// init 
	$data = array();
	
	// if the items per display is blank then display all at the one page
	if (strlen($perEmbed)) {				
		// if user defined the items to display per page	
		$Q_News = query("
			SELECT * FROM testimonial_testimonials
			WHERE te_as_id = $assetID
			ORDER BY RAND()
			LIMIT 0 , $perEmbed
		");
	} else {
		// read all news items for the asset
		$Q_News = query("
			SELECT * FROM testimonial_testimonials
			WHERE te_as_id = $assetID
			ORDER BY RAND()
		");
	}

	
	$data['ListQuery'] = $Q_News;
	$data['as_id'] = $assetID;
	$data['AssetPath'] = $assetPath;
	
	$this->useTemplate('EmbedDisplay',$data);
?>		