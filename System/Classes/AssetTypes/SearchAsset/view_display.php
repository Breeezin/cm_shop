<?php
	$mesg = "Your search did not return any information.<BR>Please retry using the search field above.";	
	$typeSelect = new SelectFromArrayField (array(
			'name'			=>	"SearchType",
			'displayName'	=>	'Item Type',
			'note'			=>	NULL,
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'class'			=>	'formborder',
			'value'			=>	$this->ATTRIBUTES['SearchType'],
			'options'		=>	$showTypes,			
	));


	// init 
	$data = array();
	
	$data['TypeSelect'] = $typeSelect->display(FALSE, 'SearchForm');
	$data['Keywords'] = $this->ATTRIBUTES['AST_SEARCH_KEYWORDS'];
	$data['HasTypeFilter'] = $hasTypeFilter;
	$data['AssetPath'] = $assetPath;
	$data['as_name'] = $asset->fields['as_name'];
	$data['AssetTypes'] = $assetTypeNames;
	$data['TrimAssetName'] = trim($asset->fields['as_name']);
	// init	
	$data['PageThru'] = '';		
	$data['Found'] = '';	
	$found = 0;
	$data['StartNum'] = 0;	
	$data['EndNum'] = 0;	
	$data['DidSearch'] = 0;	
	if (array_key_exists('Stats', $this->ATTRIBUTES)) {
		$data['DidSearch'] = 1;	
	}
	
	// if Stats key exists, it means the search request should be recorded in the search_statistics table
	// if Stats key doesnt not exist but CurrentPage key exists, it means dont record the search request 
	// because the request is part of previous search result.
	if (array_key_exists('Stats', $this->ATTRIBUTES) OR array_key_exists('CurrentPage', $this->ATTRIBUTES)) {
		$this->param("CurrentPage", 1);	
			 	
		$found = count($allList);
		$data['Found'] = $found.' '.ss_pluralize($found, 'result', 'results').' found.';	
		$data['StartNum'] = 0;			
		$data['EndNum'] = $found - 1;						 							
		// if the items per display is blank then display all at the one page
		
		if (strlen($perDisplay)) {
								
			// request page thur asset 
			global $cfg;	
			$queryString = "SearchType={$this->ATTRIBUTES['SearchType']}&AST_SEARCH_KEYWORDS={$this->ATTRIBUTES['AST_SEARCH_KEYWORDS']}";
			$backURL =  $cfg['currentServer'].substr($asset->getPath(), 1).'?';		
			$pageThru = new Request('PageThru.Display',array(
				'ItemCount'		=>	$found,	
				'ItemsPerPage'	=>	$perDisplay,
				'CurrentPage'	=>	$this->ATTRIBUTES['CurrentPage'],
				'QueryString'	=>	$queryString,		
				'PagesPerBlock'	=>	10,
				'URL'			=>	$backURL,
			));
			$data['PageThru'] = $pageThru->display;
			
			
			// if user defined the items to display per page	
			$startNum = 0;
			if ($this->ATTRIBUTES['CurrentPage'] > 1) {
				$startNum = (($this->ATTRIBUTES['CurrentPage']-1) * $perDisplay);
			}			
			$list = array();
			
			
			for($i = $startNum; $i < ($startNum + $perDisplay); $i++) {
				if ($i >= count($allList)) {
					break;	
				}
				array_push($list, $allList[$i]);
			}
			$data['AllList'] = $list;						 							
		} else {
			$data['AllList'] = $allList;
		}
	} else {
		$data['AllList'] = $allList;
	}
	$this->useTemplate('Display',$data);
	
	if (array_key_exists("Stats", $this->ATTRIBUTES)) {
		$Q_SearchStats = query("
				INSERT INTO search_statistics 
				(ss_timestamp, ss_keywords, ss_found, ss_ug_id, ss_country) 
				VALUES
				(NOW(), '".escape($this->ATTRIBUTES['AST_SEARCH_KEYWORDS'])."', $found, '".ArrayKeysToList($_SESSION['User']['user_groups'])."','".escape(ss_getCountry(null,'cn_name'))."')");
	}
	
?>			