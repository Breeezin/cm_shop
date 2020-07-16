<?php

	require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');	
	require_once('System/Libraries/image/image.php');

	startAdminPercentageBar('Sending newsletter to '.$Q_Recipients->numRows()." ".ss_pluralize($Q_Recipients->numRows(),'person','people')."...");

	$counter = 0;
	
	$embedImagesAndCSS = false;
	
	$NewsletterAsset = getRow("
		SELECT * FROM assets
		WHERE as_type LIKE 'Newsletter'
			AND as_deleted != 1
	");
	$result = new Request('Asset.PathFromID',array(
		'as_id'	=>	$NewsletterAsset['as_id'],
	));
	$NewsletterAssetPath = $result->value;

	$SubscribeAsset = getRow("
		SELECT * FROM assets
		WHERE as_type LIKE 'Subscribe'
			AND as_deleted != 1
	");
	$result = new Request('Asset.PathFromID',array(
		'as_id'	=>	$SubscribeAsset['as_id'],
	));
	$SubscribeAssetPath = $result->value;
	
	
	while ($recipient = $Q_Recipients->fetchRow()) {

		startTransaction();

		$recipientPassword = md5(uniqid(time()));
		$recipientID = newPrimaryKey('newsletter_archive_recipients','nar_id');
		
		$mailer = new htmlMimeMail();
		$mailer->setFrom($newsletter['nl_sender_email']);
		$mailer->setSubject($newsletter['nl_subject']);	
		
		// Figure out their name
		$firstName = $recipient['us_first_name'];
		if ($firstName === null or strlen($firstName) == 0) {
			$firstName = 'Subscriber';
		}

		// Set up some values for the newsletter
		$data = array(
			'Greeting'	=>	'Dear '.$firstName,		
			'Content'	=>	ss_parseText($newsletter['nl_html_message'],null,false,true),
			'Unsubscribe'	=>	$GLOBALS['cfg']['currentServer'].ss_EscapeAssetPath(ss_withoutPreceedingSlash($SubscribeAssetPath)),
			'NewsletterLink'	=>	$GLOBALS['cfg']['currentServer'].ss_EscapeAssetPath(ss_withoutPreceedingSlash($NewsletterAssetPath))."?nar_id=$recipientID&Auth=".ss_URLEncodedFormat($recipientPassword),			
			'Subject'	=>	$newsletter['nl_subject'],
			'Date'		=>	$newsletter['nl_last_modified'],
            'WindowTitle' => $newsletter['nl_subject'],
		);
		if (ss_optionExists('Newsletter Two Content Areas')) {
			$data['Content2'] = ss_parseText($newsletter['nl_html_message2'],null,false,true);
		} else {
			$data['Content2'] = '';
		}
		
		if ($archiveID === null) {
			$data['NewsletterLink'] = "Javascript:alert('This newsletter is currently not available online');void(0);";
		}

		// Cheap templating on text newsletters
		$textMessage = $newsletter['nl_textmessage'];
		$textMessage = stri_replace('[Subject]',$data['Subject'],$textMessage);
		$textMessage = stri_replace('[Date]',ListFirst($data['Date']," "),$textMessage);
		$textMessage = stri_replace('[Greeting]',$data['Greeting'],$textMessage);
		$textMessage = stri_replace('[Unsubscribe]',$data['Unsubscribe'],$textMessage);
		$textMessage = stri_replace('[NewsletterLink]',$data['NewsletterLink'],$textMessage);
		
		if (!$recipient['us_html_email']) {
			$mailer->setText($textMessage);
		} else {
		
			// Construct the html email
			$htmlMessage = processTemplate("Custom/ContentStore/Templates/{$GLOBALS['cfg']['currentSiteFolder']}NewslettersAdministration/{$newsletter['nl_template']}.html",$data);

			if ($embedImagesAndCSS) {
			
				// Insert the images into the email. The function in htmlMimeMail is a bit bung,
				// so we'll do it ourselves.
				$imagePaths = array();
				$imageNames = array();
				foreach (array('/<img[^>]* src="([^"]+)"[^>]*>/is','/background="([^"]+)"/is') as $regex) {
					preg_match_all($regex,$htmlMessage,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
					for ($i=count($matches[0])-1; $i>=0; $i--) {
						// matches[0] : array(0=>'<imgsomestuffsrc="Images/imagename"somestuff>',1=>offset);
						// matches[1] : Images/imagename
						// matches[2] : imagename
			
						$imagePath = $matches[1][$i][0];
						if (substr($imagePath,0,5) != "http:" and substr($imagePath,0,6) != "https:") {
							$imagePath = $GLOBALS['cfg']['currentServer'] . $imagePath;
						}
			
						$newImageName = md5($imagePath).".".listLast($imagePath,".");
						$imagePaths[$imagePath] = $imagePath;
						$imageNames[$imagePath] = $newImageName;
						
						$htmlMessage = substr_replace($htmlMessage,$newImageName,$matches[1][$i][1],strlen($matches[1][$i][0]));	
					}
				}
				foreach($imagePaths as $imagePath) {
					$imageImg = new image($imagePath);
					$mailer->addHtmlImage($mailer->getFile($imagePath), $imageNames[$imagePath], $imageImg->mimeType);
				}
					
				// Insert the css into the email. We actually insert it as an image...
				$filesPaths = array();
				$fileNames = array();
				foreach (array('/<link[^>]* href="([^"]+\.css)"[^>]*>/is') as $regex) {
					preg_match_all($regex,$htmlMessage,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
					for ($i=count($matches[0])-1; $i>=0; $i--) {
						// matches[0] : array(0=>'<imgsomestuffsrc="Images/imagename"somestuff>',1=>offset);
						// matches[1] : Images/imagename
						// matches[2] : imagename
			
						$filePath = $matches[1][$i][0];
						if (substr($filePath,0,5) != "http:" and substr($filePath,0,6) != "https:") {
							$filePath = $GLOBALS['cfg']['currentServer'] . $filePath;
						}
			
						$newFileName = md5($filePath).".".listLast($filePath,".");
						$filePaths[$filePath] = $filePath;
						$fileNames[$filePath] = $newFileName;
						
						$htmlMessage = substr_replace($htmlMessage,$newFileName,$matches[1][$i][1],strlen($matches[1][$i][0]));	
					}
				}
				foreach($filePaths as $filePath) {
					$mailer->addHtmlImage($mailer->getFile($filePath), $fileNames[$filePath], 'text/css');
				}		
			} else {

				// So we dont want to embed the images? We'll hard link them to the
				// website then.... Just hope your newsletter recipients are always online eh...
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
				
				
			}
	
			// Insert a special image that, when opened, will record the user as 
			// having opened the newsletter
			if ($archiveID !== null) {
				$imageTag = "<img width=\"1\" height=\"1\" src=\"".$GLOBALS['cfg']['currentServer'].ss_EscapeAssetPath(ss_withoutPreceedingSlash($NewsletterAssetPath))."?nar_id=$recipientID&Opened=1&Auth=".ss_URLEncodedFormat($recipientPassword)."\" />";
				$htmlMessage = stri_replace('</body>',$imageTag.'</body>',$htmlMessage);
			}
			$configContactDetails = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true, true);
			$htmlMessage .= "<p>$configContactDetails<p>";
			$mailer->setHtml($htmlMessage,$textMessage);	
		}
		
		// Send the email
		$mailer->send(array($recipient['us_email']));
		
		// Log it in the db
		if ($archiveID !== null) {
			$Q_AddRecipient = query("
				INSERT INTO newsletter_archive_recipients
					(nar_id, nar_firstname, nar_lastname, 
						nar_email, nar_nl_id, nar_read, nar_password)
				VALUES
					($recipientID, '".escape($recipient['us_first_name'])."', '".escape($recipient['us_last_name'])."',
						'".escape($recipient['us_email'])."', $archiveID, NULL, '".escape($recipientPassword)."')
			");
		}
		
		
		// obvious :P
		$counter++;
		updateAdminPercentageBar($counter/$Q_Recipients->numRows());

		commit();
	}

	stopAdminPercentageBar($redirect);
	
?>
