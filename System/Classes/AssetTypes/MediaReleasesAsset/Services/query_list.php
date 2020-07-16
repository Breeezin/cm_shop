<?php

	$this->param('CurrentPage',1);

	// get the number of items to display per page
	ss_paramKey($asset->cereal,$this->fieldPrefix.'RELEASES_PER_PAGE','');
	$perDisplay = $asset->cereal[$this->fieldPrefix.'RELEASES_PER_PAGE'];
	
	// init 
	$data = array();
	
	// read all news items for the asset
	$Q_Releases = query("
		SELECT * FROM media_releases_releases
		WHERE rel_as_id = $assetID
			AND rel_approved = 1
		ORDER BY rel_date DESC, rel_id DESC
	");
	
	$data['PageThru'] = '';	
	
	// if the items per display is blank then display all at the one page
	if (strlen($perDisplay)) {
							
		// request page thur asset 
		global $cfg;	
		$backURL =  $cfg['currentServer'].substr($asset->path, 1).'?';		
		$pageThru = new Request('PageThru.Display',array(
			'ItemCount'		=>	$Q_Releases->numRows(),	
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
		$Q_Releases = query("
			SELECT * FROM media_releases_releases
			WHERE rel_as_id = $assetID
				AND rel_approved = 1
			ORDER BY rel_date DESC, rel_id DESC
			LIMIT $startNum , $perDisplay
		");
				
	}

	$data['AssetStore'] = ss_storeForAsset($assetID);
	$data['ListQuery'] = $Q_Releases;
	
	
	
	$this->useTemplate('List',$data);
	
?>