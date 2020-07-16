<?php
	$this->display->layout = 'none';
	$this->param('nl_id');
	
	$Newsletter = getRow("
		SELECT * FROM newsletters
		WHERE nl_id = ".safe($this->ATTRIBUTES['nl_id'])."
	");
	
	$SubscribeAsset = getRow("
		SELECT * FROM assets
		WHERE as_type LIKE 'Subscribe'
	");
	$result = new Request('Asset.PathFromID',array(
		'as_id'	=>	$SubscribeAsset['as_id'],
	));
	$SubscribeAssetPath = $result->value;
		
	
	// Set up some values for the newsletter
	$data = array(
		'Greeting'	=>	'Dear <<First Name Will Go Here>>',		
		'Unsubscribe'	=>	$GLOBALS['cfg']['currentServer'].ss_EscapeAssetPath(ss_withoutPreceedingSlash($SubscribeAssetPath)),
		'NewsletterLink'	=>	"Javascript:void(0);",
		'Content'	=>	ss_parseText($Newsletter['nl_html_message'],null,false),
		'Subject'	=>	$Newsletter['nl_subject'],
		'Date'		=>	$Newsletter['nl_last_modified'],
        'WindowTitle' => $Newsletter['nl_subject'],
	);
	if (ss_optionExists('Newsletter Two Content Areas')) {
		$data['Content2'] = ss_parseText($Newsletter['nl_html_message2'],null,false);
	}
	
	
	// Construct the html email
	$htmlMessage = processTemplate("Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}NewslettersAdministration/{$Newsletter['nl_template']}.html",$data);

	// Make image references absolute web urls		
	foreach (array('/<img[^>]* src="([^"]+)"[^>]*>/is','/background="([^"]+)"/is','/<link[^>]* href="([^"]+\.css)"[^>]*>/is') as $regex) {
		preg_match_all($regex,$htmlMessage,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		for ($i=count($matches[0])-1; $i>=0; $i--) {
			// matches[0] : array(0=>'<imgsomestuffsrc="Images/imagename"somestuff>',1=>offset);
			// matches[1] : Images/imagename
			// matches[2] : imagename

			$imagePath = $matches[1][$i][0];
			if (substr($imagePath,0,5) != "http:" and substr($imagePath,0,6) != "https:") {
				$imagePath = $GLOBALS['cfg']['currentServer'] . $imagePath;
			}
			
			$htmlMessage = substr_replace($htmlMessage,$imagePath,$matches[1][$i][1],strlen($matches[1][$i][0]));	
		}
	}

	print $htmlMessage;
?>
