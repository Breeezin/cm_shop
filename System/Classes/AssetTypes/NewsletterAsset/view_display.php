<?php
	if (array_key_exists('nar_id',$this->ATTRIBUTES) and array_key_exists('Auth',$this->ATTRIBUTES)) {
	
		if ($GLOBALS['cfg']['currentServer'] == 'http://guardiangroup.im.co.nz/' and $this->ATTRIBUTES['nar_id'] < 1000) {
			$this->ATTRIBUTES['nar_id'] = 0;
			$this->ATTRIBUTES['Auth'] = 'error';
		}
		
		$Q_Recipient = query("
			SELECT * FROM newsletter_archive_recipients
			WHERE nar_id = ".safe($this->ATTRIBUTES['nar_id'])."		
				AND nar_password LIKE '".escape($this->ATTRIBUTES['Auth'])."'
		");
		if ($Q_Recipient->numRows()) {
			$Q_MarkRead = query("
				UPDATE newsletter_archive_recipients
				SET nar_read = 1
				WHERE nar_id = ".safe($this->ATTRIBUTES['nar_id'])."
			");

			// If we're just flagging the message as being opened, then redirect
			// to a 1x1 pixel transparent gif :)
			if (array_key_exists('Opened',$this->ATTRIBUTES)) {
				locationRelative('System/Classes/AssetTypes/NewsletterAsset/Templates/Images/1_pixel_transparent.gif');
			}
			
			$recipient = $Q_Recipient->fetchRow();
			$newsletter = getRow("
				SELECT * FROM newsletter_archive
				WHERE na_id = {$recipient['nar_nl_id']}
			");	
			$this->display->layout = 'none';
			
			$data = array(
				'Greeting'	=>	'Dear '.ss_HTMLEditFormat($recipient['nar_firstname']),
				'NewsletterLink'	=>	'Javascript:alert(\'You are already viewing the newsletter online!\');void(0);',
			);
			
			$htmlContent = $newsletter['na_content'];
			$htmlContent = stri_replace('[Greeting]',$data['Greeting'],$htmlContent);
			$htmlContent = stri_replace('[NewsletterLink]',$data['NewsletterLink'],$htmlContent);
			$htmlContent = stri_replace('[CurrentPage]',$_SERVER['REQUEST_URI'],$htmlContent);
			
			print $htmlContent;
			$asset->display->layout = 'none';
		} else {
			print "<p>The newsletter could not be found, or your authentication code was not accepted.</p><p>Please ensure you have copied the entire link and that it has not been split onto 2 lines by your email client.</p>";
		}
	} else {
		print("Please access the newsletter via the newsletter archive or a link included in a newsletter you have received.");	
	}
?>		