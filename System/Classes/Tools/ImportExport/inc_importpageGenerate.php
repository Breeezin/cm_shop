<?php 
	$debug = false;



	// IMAGES

	// find <imgsomestuffsrc="Images/imagename"somestuff>
	$regex = 	'/<IMG[^>]+SRC="(Images\/'.	// match <IMG somestuff SRC="Images/
				'([^"]+)'.					// match imagename 
				')"[^>]*>'.					// match "somestuff>
				'/is';						// case insensitive
				
	//preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE);
	preg_match_all($regex,$content,$matches, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
	if ($debug) ss_DumpVar($matches,'Images');
	for ($i=count($matches[0])-1; $i>=0; $i--) {
		
		// matches[0] : array(0=>'<imgsomestuffsrc="Images/imagename"somestuff>',1=>offset);
		// matches[1] : Images/imagename
		// matches[2] : imagename

		// Grab the asset name
		$assetName = $matches[2][$i][0];	
		
		// Removes the file extension... >_<
		$assetNameWithoutExtention = strrev($assetName); // reverse it
		$assetNameWithoutExtention = strrev(substr($assetNameWithoutExtention,strpos($assetNameWithoutExtention,".")+1)); // get everything after the first (last ;) ) dot. then reverse it 

		foreach (array('20'=>' ','7E'=>'~') as $code => $char) {
			$assetNameWithoutExtention = stri_replace('%'.$code,$char,$assetNameWithoutExtention);
			$assetName = stri_replace('%'.$code,$char,$assetName);
		}
		
		//ss_log_message_r($assetName,'with');
		//ss_log_message_r($assetNameWithoutExtention,'without');
		
		
		$asset = getRow("
			SELECT * FROM assets
			WHERE as_name LIKE '".escape($assetNameWithoutExtention)."'
				AND as_type LIKE 'Image'
				AND (as_deleted IS NULL OR as_deleted = 0)
		");
		//ss_log_message_r($asset,'asset');
		if (is_array($asset) and ($asset['as_type'] == 'Image')) {
			// Existing image assset		
			$assetDetails = array();
			if (strlen($asset['as_serialized'])) {
				$assetDetails = unserialize($asset['as_serialized']);
			}
			ss_paramKey($assetDetails,'AST_IMAGE_STD','');
			
			if (strlen($assetDetails['AST_IMAGE_STD'])) {
				if (file_exists("Custom/ContentStore/ImportExport/Images/".$assetName)) {
					copy("Custom/ContentStore/ImportExport/Images/".$assetName,ss_storeForAsset($asset['as_id']).$assetDetails['AST_IMAGE_STD']);
				}
				$imageTag = "<img border=\"0\" alt=\"EMB: {$asset['as_id']}\" src=\"index.php?act=Asset.Display&as_id={$asset['as_id']}\" />";
			
				// Insert the image tag into the content
				$content = substr_replace($content,$imageTag,$matches[0][$i][1],strlen($matches[0][$i][0]));
			} else {
				
				// Figure out a file name for the image
				$extension = '';
				if (strpos($assetName,'.')) {
					$extension = array_pop(explode('.', $assetName));
				}
				$result = new Request("UID.Get");
				$newFileName = md5(rand()).".".$extension;

				// Create details for the asset type
				$assetDetails = array();					
				$assetDetails['AST_IMAGE_STD'] = $newFileName;
				$assetDetailsCereal = serialize($assetDetails);
				
				// Update the asset details
				$result = query("
					UPDATE assets
					SET as_serialized = '".escape($assetDetailsCereal)."'
					WHERE as_id = {$asset['as_id']}
				");
				
				// Copy the image to the asset store
				copy("Custom/ContentStore/ImportExport/Images/".$assetName,ss_storeForAsset($asset['as_id']).$assetDetails['AST_IMAGE_STD']);								
				$imageTag = "<img border=\"0\" alt=\"EMB: {$asset['as_id']}\" src=\"index.php?act=Asset.Display&as_id={$asset['as_id']}\" />";
			
				// Insert the image tag into the content
				$content = substr_replace($content,$imageTag,$matches[0][$i][1],strlen($matches[0][$i][0]));
				
		
			}

		} else {
			// New image asset
			$result = new Request('Asset.Add',array(
				'as_name'	=>	$assetNameWithoutExtention,
				'as_type'	=>	'Image',
				'as_appear_in_menus'	=>	0,
				'as_parent_as_id'	=>	ss_systemAsset('Images'),
				'DoAction'	=>	1,
				'AsService'	=>	true,
				'OnlineNow' =>	1,
			));
			
			if ($result->value !== null) {
				$newAssetID = $result->value;
				
				print $newAssetID;
				
				// Figure out a file name for the image
				$extension = '';
				if (strpos($assetName,'.')) {
					$extension = array_pop(explode('.', $assetName));
				}
				$result = new Request("UID.Get");
				$newFileName = md5($newAssetID.$result->value).".".$extension;

				// Create details for the asset type
				$assetDetails = array();					
				$assetDetails['AST_IMAGE_STD'] = $newFileName;
				$assetDetailsCereal = serialize($assetDetails);
				
				// Update the asset details
				$result = query("
					UPDATE assets
					SET as_serialized = '".escape($assetDetailsCereal)."'
					WHERE as_id = {$newAssetID}
				");
				
				// Copy the image to the asset store
				copy("Custom/ContentStore/ImportExport/Images/".$assetName,ss_storeForAsset($newAssetID).$assetDetails['AST_IMAGE_STD']);								
				$imageTag = "<img border=\"0\" alt=\"EMB: {$newAssetID}\" src=\"index.php?act=Asset.Display&as_id={$newAssetID}\" />";
			
				// Insert the image tag into the content
				$content = substr_replace($content,$imageTag,$matches[0][$i][1],strlen($matches[0][$i][0]));
			} else {
				//	print("got null result");
			}
		}
		
	}

	// LINKS
	
	// search anchor and replace with cm achor image
	// find <a name="anchorname"></a>
	/*
	$regex = 	'/<A[^>]+NAME="([^"]+)'.	// match <A somestuff Name="					
				'"[^>]*>[^>]+</a>'.			// match "somestuff>
				'/is';						// case insensitive

	*/		
	//<a name="test"></a>	
	$regex = '<A[^>]*NAME="([^>]*)"[^>]*></a>';
	while (eregi($regex,$content,$result)) {
		$anchorName = $result[1];
		$content = stri_replace($result[0],"<img src=\"{$GLOBALS['cfg']['currentServer']}System/Libraries/Field/htmlarea/images/ed_anchor.gif\" alt=\"{$anchorName}\" />",$content);
	}		
	/*
	preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
	if ($debug) ss_DumpVar($matches,'Anchors');
	for ($i=count($matches[0])-1; $i>=0; $i--) {
		// matches[1] : array(0=>'anything.html', 1 =>234);
		
		// Grab the anchor name
		$anchorName = $matches[1][$i][0];	
							
		// Insert the new achor into the link content
		$content = substr_replace($content,"<img src=\"http://{$_SERVER['SERVER_NAME']}/System/Libraries/Field/htmlarea/images/ed_anchor.gif\" alt=\"{$anchorName}\" />",$matches[1][$i][1],strlen($matches[1][$i][0]));	
	}
	*/
	
	// find href="blah.html >
	$regex = 	'/href="'.				// match href="
				'([^"#]+\.html)[#"]'.	// match anything.html followed by # or ", 
				'/is';					// case insensitive
				
	preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
	if ($debug) ss_DumpVar($matches,'Normal Links');
	for ($i=count($matches[0])-1; $i>=0; $i--) {
		// matches[1] : array(0=>'anything.html', 1 =>234);
		
		// Grab the asset id
		$assetPath = $this->exportFileToAssetPath($matches[1][$i][0]);	
		
		// Find the new link path
		$result = new Request('Asset.IDFromPath',array('AssetPath'=>$assetPath));
		$linkPath = "Asset://".$result->value;

		// Insert the new path into the link content
		$content = substr_replace($content,$linkPath,$matches[1][$i][1],strlen($matches[1][$i][0]));

	}
	

	$content = stri_replace("[Next]","Asset://Next",$content);
	$content = stri_replace("[Previous]","Asset://Previous",$content);
		
	
	// find href="javascript:newwindow=window.open('asset://503/ >
	$regex = 	'/href="[^"]+window\.open\(\''.				// match href="
				'([^\'#]+\.html)[#\']'.	// match anything.html followed by # or ",  
				'/is';					// case insensitive

	preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
	if ($debug) ss_DumpVar($matches,'JS Links');
	for ($i=count($matches[0])-1; $i>=0; $i--) {
		// matches[1] : array(0=>'anything.html', 1 =>234);
		
		// Grab the asset id
		$assetPath = $this->exportFileToAssetPath($matches[1][$i][0]);	
		
		// Find the new link path
		$result = new Request('Asset.IDFromPath',array('AssetPath'=>$assetPath));
		$linkPath = "Asset://".$result->value;

		// Insert the new path into the link content
		$content = substr_replace($content,$linkPath,$matches[1][$i][1],strlen($matches[1][$i][0]));

	}

	// Grab everything inside the "body" tags
	$matchCount = preg_match("/<body[^>]*>(.*)<\/body>/is",$content,$matches);
	if ($matchCount != 0) {
		$content = $matches[1];
	}
	
	// Fix up maori characters :S

	/*$newContent = '';
	for ($i=0; $i<strlen($content); $i++) {
		$ch = $content[$i];
		switch ($ch) {
			case '?': 
				$newContent .= '&#257;'; 
				break;
			case '?': 
				$newContent .= '&#275;'; 
				break;
			case '?': 
				$newContent .= '&#299;'; 
				break;
			case '?': 
				$newContent .= '&#333;'; 
				break;
			case '?': 
				$newContent .= '&#363;'; 
				break;
			case '?': 
				$newContent .= '&#256;'; 
				break;
			case '?': 
				$newContent .= '&#274;'; 
				break;
			case '?': 
				$newContent .= '&#298;'; 
				break;
			case '?': 
				$newContent .= '&#332;'; 
				break;
			case '?': 
				$newContent .= '&#362;'; 
				break;
			default:
				$newContent .= $ch;
		}
	}
	$content = $newContent;*/
	return $content;
?>