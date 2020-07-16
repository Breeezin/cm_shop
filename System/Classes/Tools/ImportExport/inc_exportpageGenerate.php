<?php 
	$debug = false;

	// IMAGES

	// find <img somestuff alt="emb: 503" somestuff>
	$regex = 	'/<IMG[^>]+ALT="'.		// match <IMG somestuff ALT="
				'(emb: ([0-9]+))'.		// match EMB: 503, 
				'"[^>]*>'.				// match "somestuff>
				'/is';					// case insensitive
	preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
	if ($debug) ss_DumpVar($matches);
	for ($i=count($matches[0])-1; $i>=0; $i--) {
		// matches[0] : array(0=>'<img stuff alt="emb: 503" somestuff>',1=>offset);
		// matches[1] : emb://503/
		// matches[2] : 503

		// Grab the asset id
		$assetID = $matches[2][$i][0];	
		
		// Get the asset details
		$asset = getRow("
			SELECT * FROM assets
			WHERE as_id = $assetID
		");
		
		if ($asset['as_type'] == 'Image') {
	
			// Grab the name of the image
			$assetDetails = array();
			if (strlen($asset['as_serialized'])) $assetDetails = unserialize($asset['as_serialized']);
			ss_paramKey($assetDetails,'AST_IMAGE_STD','');

			// If there is an image defined...
			if (strlen($assetDetails['AST_IMAGE_STD'])) {
				$extension = '';

				// Copy the image from the asset store to the ImportExport/Images folder
				if (file_exists(ss_storeForAsset($assetID).$assetDetails['AST_IMAGE_STD'])) {
					// Figure out a file name for the image
					if (strpos($assetDetails['AST_IMAGE_STD'],'.')) {
						$extension = '.'.array_pop(explode('.', $assetDetails['AST_IMAGE_STD']));
					}
					copy(ss_storeForAsset($assetID).$assetDetails['AST_IMAGE_STD'],"Custom/ContentStore/ImportExport/Images/".$asset['as_name']).$extension;
				}

				// Create a nice new image tag
				$imageTag = "<img border=\"0\" src=\"Images/{$asset['as_name']}{$extension}\" />";
			
				// Insert the new path into the link content
				$content = substr_replace($content,$imageTag,$matches[0][$i][1],strlen($matches[0][$i][0]));
			}
		}
	}

	$content = stri_replace("Asset://Next/","[Next]",$content);
	$content = stri_replace("Asset://Next","[Next]",$content);
	$content = stri_replace("Asset://Previous/","[Previous]",$content);
	$content = stri_replace("Asset://Previous","[Previous]",$content);
	
	// LINKS
	// Look for anchor image from graphical editor e.g) <img src="System/Libraries/Field/htmlarea/images/ed_anchor.gif" alt="testanc" />
	while (eregi('<IMG[^>]* SRC="System/Libraries/Field/htmlarea/images/ed_anchor.gif" ALT="([^>]*)"[^>]*>',$content,$result)) {
		$anchorName = $result[1];
		$content = stri_replace($result[0],"<a name=\"{$anchorName}\"></a>",$content);
	}
	while (eregi('<IMG[^>]* ALT="([^>]*)"[^>]* SRC="System/Libraries/Field/htmlarea/images/ed_anchor.gif">',$content,$result)) {
		$anchorName = $result[1];
		$content = stri_replace($result[0],"<a name=\"{$anchorName}\"></a>",$content);
	}
	while (eregi('<IMG[^>]* SRC="'.$GLOBALS['cfg']['currentServer'].'System/Libraries/Field/htmlarea/images/ed_anchor.gif" ALT="([^>]*)"[^>]*>',$content,$result)) {
		$anchorName = $result[1];
		$content = stri_replace($result[0],"<a name=\"{$anchorName}\"></a>",$content);
	}
	while (eregi('<IMG[^>]* ALT="([^>]*)"[^>]* SRC="'.$GLOBALS['cfg']['currentServer'].'System/Libraries/Field/htmlarea/images/ed_anchor.gif">',$content,$result)) {
		$anchorName = $result[1];
		$content = stri_replace($result[0],"<a name=\"{$anchorName}\"></a>",$content);
	}	
	
	
	
	// find href="asset://503/ >
	$regex = 	'/href="'.				// match href="
				'(asset:\/\/([0-9]+)[\/]*)'.	// match "asset://" followed by a number, 
				'/is';					// case insensitive
	preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
	if ($debug) ss_DumpVar($matches);
	for ($i=count($matches[0])-1; $i>=0; $i--) {
		// matches[1] : array(0=>'asset://503/', 1 =>234);
		// matches[2] : 503
		
		// Grab the asset id
		$assetID = $matches[2][$i][0];	
		
		// Find the new link path
		$result = new Request('Asset.PathFromID',array('as_id'=>$assetID));
		$linkPath = $this->assetPathToExportFile($result->value);

		// Insert the new path into the link content
		$content = substr_replace($content,$linkPath,$matches[1][$i][1],strlen($matches[1][$i][0]));
	}

	// find href="javascript:newwindow=window.open('asset://503/ >
	$regex = 	'/href="[^"]+window\.open\(\''.				// match href="
				'(asset:\/\/([0-9]+)[\/]*)'.	// match "asset://" followed by a number, 
				'/is';					// case insensitive
	preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
	if ($debug) ss_DumpVar($matches);
	for ($i=count($matches[0])-1; $i>=0; $i--) {
		// matches[1] : array(0=>'asset://503/', 1 =>234);
		// matches[2] : 503
		
		// Grab the asset id
		$assetID = $matches[2][$i][0];	
		
		// Find the new link path
		$result = new Request('Asset.PathFromID',array('as_id'=>$assetID));
		$linkPath = $this->assetPathToExportFile($result->value);

		// Insert the new path into the link content
		$content = substr_replace($content,$linkPath,$matches[1][$i][1],strlen($matches[1][$i][0]));
	}
	return $content;
?>