<?php
	
	$this->param("CurrentPage", 1);
	
	$assetID = $asset->getID();
	
	// get the number of items to display per page
	ss_paramKey($asset->cereal,$this->fieldPrefix.'ITEMS_PER_PAGE',100000);
	$perDisplay = $asset->cereal[$this->fieldPrefix.'ITEMS_PER_PAGE'];
	
	// init 
	$data = array();
	
	// read all news items for the asset
	$Q_Items = query("
		SELECT * FROM ccc_items
		WHERE cit_as_id = $assetID
		ORDER BY cit_sort_order, cit_id
	");
	
	$data['PageThru'] = '';	
	
	$startNum = 0;	
	// if the items per display is blank then display all at the one page
	if (strlen($perDisplay)) {
							
		// request page thur asset 
		global $cfg;	
		$backURL =  $cfg['currentServer'].substr($asset->path, 1).'?';		
		$pageThru = new Request('PageThru.Display',array(
			'ItemCount'		=>	$Q_Items->numRows(),	
			'ItemsPerPage'	=>	$perDisplay,
			'CurrentPage'	=>	$this->ATTRIBUTES['CurrentPage'],
			'PagesPerBlock'	=>	5,
			'URL'			=>	$backURL,
		));
		$data['PageThru'] = $pageThru->display;
		
		
		// if user defined the items to display per page	
		if ($this->ATTRIBUTES['CurrentPage'] > 1) {
			$startNum = (($this->ATTRIBUTES['CurrentPage']-1) * $perDisplay);
		}	
		$Q_Items = query("
			SELECT * FROM ccc_items
			WHERE cit_as_id = $assetID
			ORDER BY cit_sort_order, cit_id
			LIMIT $startNum , $perDisplay
		");
				
	}

	$data['Images'] = ss_storeForAsset($assetID);
	$data['ListQuery'] = $Q_Items;
	$data['CurrentRow'] = $startNum;
	$data['ATTRIBUTES'] = $this->ATTRIBUTES;
	ss_toggleDisplayJS();	
	
	$this->useTemplate('Display',$data);
?>