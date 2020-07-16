<?php
	
	$this->param("CurrentPage", 1);
	
	$assetID = $asset->getID();
	
	// get the number of items to display per page
	$perDisplay = $asset->cereal[$this->fieldPrefix.'ITEMSPERDISPLAY'];
	
	// init 
	$data = array();
	
	// read all news items for the asset
	$Q_News = query("
		SELECT * FROM news_items
		WHERE nei_as_id = $assetID
		ORDER BY nei_timestamp DESC, nei_id DESC
	");
	
	$data['PageThru'] = '';	
	
	// if the items per display is blank then display all at the one page
	if (strlen($perDisplay)) {
							
		// request page thur asset 
		global $cfg;	
		$backURL =  $cfg['currentServer'].substr($asset->path, 1).'?';		
		$pageThru = new Request('PageThru.Display',array(
			'ItemCount'		=>	$Q_News->numRows(),	
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
			ORDER BY nei_timestamp DESC, nei_id DESC		
			LIMIT $startNum , $perDisplay
		");
				
	}

	$data['Images'] = ss_storeForAsset($assetID);
	$data['ListQuery'] = $Q_News;
	
	
	
	$this->useTemplate('Display',$data);
?>			