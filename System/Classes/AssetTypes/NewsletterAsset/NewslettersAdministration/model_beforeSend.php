<?php

	if (array_key_exists('Cancel',$this->ATTRIBUTES)) {
		locationRelative('index.php?act=NewslettersAdministration.List');	
	}
	
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		$this->param('ArchiveStatus');

		startTransaction();
		
		// get an id for the entry in the archive
		$id = newPrimaryKey('newsletter_archive','nl_id');
		
		if (strpos($this->ATTRIBUTES['ArchiveStatus'],'Current') !== false) {
			$archiveAsset = stri_replace('Current','',$this->ATTRIBUTES['ArchiveStatus']); 
			$current = 1;
			// Clear current from other newsletters
			$Q_ClearOtherCurrent = query("
				UPDATE newsletter_archive
				SET na_current = null
				WHERE nl_as_id = $archiveAsset
			");	
		} else {			
			$archiveAsset = $this->ATTRIBUTES['ArchiveStatus'];
			$current = 'null';
		}
		
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
			'Greeting'	=>	'[Greeting]',		
			'Unsubscribe'	=>	$GLOBALS['cfg']['currentServer'].ss_EscapeAssetPath(ss_withoutPreceedingSlash($SubscribeAssetPath)),
			'NewsletterLink'	=>	"[NewsletterLink]",
			'Content'	=>	ss_parseText($Newsletter['nl_html_message'],null,false,'[CurrentPage]'),
			'Subject'	=>	$Newsletter['nl_subject'],
			'Date'		=>	$Newsletter['nl_last_modified'],
            'WindowTitle' => $Newsletter['nl_subject'],
		);
		if (ss_optionExists('Newsletter Two Content Areas')) {
			$data['Content2'] = ss_parseText($Newsletter['nl_html_message2'],null,false,'[CurrentPage]');
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
		
		// Insert this one into the archive
		$Q_InsertLog = query("
			INSERT INTO newsletter_archive
				(nl_id, na_sent, na_us_id, 
					na_user_firstname, na_user_lastname, na_user_email,
					na_content, na_current, nl_subject, nl_as_id,
					na_user_groups)
			VALUES
				($id, NOW(), {$_SESSION['User']['us_id']}, 
					'".escape($_SESSION['User']['us_first_name'])."', '".escape($_SESSION['User']['us_last_name'])."', '".escape($_SESSION['User']['us_email'])."',
					'".escape($htmlMessage)."', $current, '".escape($Newsletter['nl_subject'])."', $archiveAsset,
					'".escape($Q_NewsletterRecipientGroups->columnValuesList('ug_id',',',''))."')
		");
		
		commit();
		
		locationRelative("index.php?act=Newsletter.Send&nl_id={$this->ATTRIBUTES['nl_id']}&ArchiveNeID={$id}&DisableOutputBuffering=1");
		
	}
	
?>
