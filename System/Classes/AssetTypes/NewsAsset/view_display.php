<?php
	
	$this->param("CurrentPage", 1);
	
	$assetID = $asset->getID();
	$assetPath = ss_withoutPreceedingSlash($asset->getPath());
    
	// get the number of items to display per page
	ss_paramKey($asset->cereal,$this->fieldPrefix.'ITEMSPERDISPLAY',100000);
	$perDisplay = $asset->cereal[$this->fieldPrefix.'ITEMSPERDISPLAY'];
	
	// init 
	$data = array();
	
	$hideSQL = '';
	if (ss_optionExists('News Can Hide')) {
		$hideSQL = 'AND nei_hidden IS NULL';
	}
	
	$data['PageThru'] = '';	
	
	// if the items per display is blank then display all at the one page
	if (strlen($perDisplay)) {

		// count all news items for the asset
		$count = getRow("
			SELECT COUNT(*) AS TheTotal FROM news_items
			WHERE nei_as_id = $assetID
				$hideSQL
			ORDER BY nei_timestamp DESC, nei_id DESC
		");	
		
		// request page thur asset 
		global $cfg;	
		$backURL =  $cfg['currentServer'].substr($asset->path, 1).'?';		
		$pageThru = new Request('PageThru.Display',array(
			'ItemCount'		=>	$count['TheTotal'],	
			'ItemsPerPage'	=>	$perDisplay,
			'CurrentPage'	=>	$this->ATTRIBUTES['CurrentPage'],
			'PagesPerBlock'	=>	5,
			'URL'			=>	$backURL,
		));
		$data['PageThru'] = $pageThru->display;
		
		
		// if user defined the items to display per page	
		$startNum = 0;
		if ($this->ATTRIBUTES['CurrentPage'] > 1) {
			$startNum = (($this->ATTRIBUTES['CurrentPage']-1) * $perDisplay);
		}	
		$Q_News = query("
			SELECT * FROM news_items
			WHERE nei_as_id = $assetID
				$hideSQL
			ORDER BY nei_timestamp DESC, nei_id DESC		
			LIMIT $startNum , $perDisplay
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

	$data['Images'] = ss_storeForAsset($assetID);
	$data['ListQuery'] = $Q_News;
    $data['AssetPath'] = $assetPath;
    $data['CurrentPage'] = $this->ATTRIBUTES['CurrentPage'];

	$this->useTemplate('Display',$data);
?>			
