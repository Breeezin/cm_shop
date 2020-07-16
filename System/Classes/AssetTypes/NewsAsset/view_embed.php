<?php
	
	$this->param("CurrentPage", 1);
	
	$assetID = $asset->getID();
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());
	
	// get the number of items to display per page
	ss_paramKey($asset->cereal,$this->fieldPrefix.'PANELITEMS',100000);
	$perEmbed = $asset->cereal[$this->fieldPrefix.'PANELITEMS'];
	
	// init 
	$data = array();
	
	$hideSQL = '';
	if (ss_optionExists('News Can Hide')) {
		$hideSQL = 'AND nei_hidden IS NULL';
	}	
	
	// if the items per display is blank then display all at the one page
	if (strlen($perEmbed)) {
		// if user defined the items to display per page	
		$Q_News = query("
			SELECT * FROM news_items
			WHERE nei_as_id = $assetID
				$hideSQL
			ORDER BY nei_timestamp DESC, nei_id DESC		
			LIMIT 0 , $perEmbed
		");
	} else {
		// read all news items for the asset
		$Q_News = query("
			SELECT * FROM news_items
			WHERE nei_as_id = $assetID
				$hideSQL
			ORDER BY nei_timestamp DESC, nei_id DESC
		");
	}
	
	$data['ListQuery'] = $Q_News;
	$data['as_id'] = $assetID;
	$data['AssetPath'] = $assetPath;
	$data['Images'] = ss_storeForAsset($assetID);
	
	
	$this->useTemplate('EmbedDisplay',$data);
?>		