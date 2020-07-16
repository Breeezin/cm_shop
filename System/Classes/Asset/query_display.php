<?php


	// Get the asset specified by attributes or server path_info
	$this->loadAsset();
	$theID = $this->getID();
	
	
	
	// Check if they are allowed to access this asset
	$result = new Request('Security.Authenticate',array(
		'Permission'	=>	'CanAccessAsset',
		'as_id'		=>	$theID,
	));
	
	global $cfg;
	
	// record its hit 
	if(!ss_isOffline()) {		
		// keep requested asset's hit for reports and stats		
		
		// check the referral address , if it's from the current website then no need to keep the refferal		
		$referrer = "NULL";
		if (array_key_exists("HTTP_REFERER",$_SERVER)) {
			$referrer = escape($_SERVER['HTTP_REFERER']);
			if (substr($_SERVER['HTTP_REFERER'],0, strlen($cfg['currentSite'])) == $cfg['currentSite'] or substr($_SERVER['HTTP_REFERER'],0, strlen($cfg['secure_server'])) == $cfg['secure_server']) {
				$referrer = "NULL";
			} else {
				$referrer = escape($_SERVER['HTTP_REFERER'])=='NULL'?$referrer:('\''.$referrer.'\'');
			}
		}
		
		
		// get user's browser information
		//$browserDetails = @get_browser(); 				
		//$browserDetails = array(); 		
		$browser = "";
		/*
		if (is_array($browserDetails)) {	
			foreach ($browserDetails as $name => $value) {
				if ($name == "browser" OR $name == "version") {
	   				$browser .= $value.' ';
				}			
			}
		}
		*/
		// get user id if they loged in
		$userID = "NULL";
		
		
		if (array_key_exists('statsUser', $_COOKIE)) {
			$userID = '\''.escape($_COOKIE['statsUser']).'\'';
		}
				
	
	/* testing 1 2 3 Rex TODO etc etc
		$Q_InsertStat = query("
			INSERT INTO statistics 
			(sts_access_timestamp, sts_as_id, sts_referrer, sts_client_id, sts_client_browser, sts_country) 
			VALUES 
			(NOW(),'$theID', $referrer, $userID, '".escape($browser)."','".escape(ss_getCountry(null,'cn_name'))."') 
		");		
		*/

		
	}

	// Pass some values to the layout handler
	
	// Let the display handler know what the full asset path is
	$this->display->assetPath = $this->getPath();
	$this->display->assetID = $theID;
	
	// Set up the title for the display handler
	if ($this->fields['as_header_name'] != NULL) {
		$this->display->title = ss_HTMLEditFormat($this->fields['as_header_name']);	
	} else {
		$this->display->title = ss_HTMLEditFormat($this->fields['as_name']);	
	}
	
	if (ss_optionExists('Asset Sub Title')) {
		$this->display->subTitle = $this->fields['as_subtitle'];	
	}
		
	
	// Default the layout fields
	ss_paramKey($this->layout,'LYT_LAYOUT','default');
	ss_paramKey($this->layout,'LYT_STYLESHEET','main');
	ss_paramKey($this->layout,'LYT_WINDOWTITLE','');
	ss_paramKey($this->layout,'LYT_KEYWORDS','');
	ss_paramKey($this->layout,'LYT_DESCRIPTION','');
	ss_paramKey($this->layout,'LYT_TITLEIMAGE','');
	ss_paramKey($this->layout,'LYT_LAYOUT_SUBPAGECONTENT','');

	ss_SetIfSet($this->display->keywords, $this->fields['as_search_keywords']);
	ss_SetIfSet($this->display->description, $this->fields['as_search_description']);
	ss_SetIfSet($this->display->styleSheet, $this->layout['LYT_STYLESHEET']);
	
	$this->display->subContent = ss_parseText($this->layout['LYT_LAYOUT_SUBPAGECONTENT']);
	
	$this->display->assetLayoutSettings = $this->layout;
	
	if (array_key_exists('MainLayout',$this->ATTRIBUTES)) {
		$this->display->layout = $this->ATTRIBUTES['MainLayout'];
	} else {
		$this->display->layout = $this->layout['LYT_LAYOUT'];
	}

	// this is where we override the tite and metadata for an asset in another site/language

	if( array_key_exists( 'cfg', $GLOBALS )
		 && array_key_exists( 'currentLanguage', $GLOBALS['cfg'] )
		 && $GLOBALS['cfg']['currentLanguage'] > 0 )
	{
		$or = getRow( "Select * from asset_descriptions where ad_as_id = $theID and ad_language = ".$GLOBALS['cfg']['currentLanguage'] );
		if( strlen( $or['ad_metadata_keywords'] ) )
			$this->display->keywords = $or['ad_metadata_keywords'];
		if( strlen( $or['ad_metadata_description'] ) )
			$this->display->description = $or['ad_metadata_description'];
		if( strlen( $or['ad_window_title'] ) )
			$this->layout['LYT_WINDOWTITLE'] = $or['ad_window_title'];
	}

?>
