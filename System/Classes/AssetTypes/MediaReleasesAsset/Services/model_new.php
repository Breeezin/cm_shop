<?php 
	
	if (array_key_exists('Do_Service',$this->ATTRIBUTES)) {	
		
		
		$temp = new Request("Security.Sudo",array('Action'=>'start'));
		
		$mediaReleasesAdmin->loadFieldValuesFromForm($this->ATTRIBUTES);			
		// Validate and then write to the database
		
		$errors = $mediaReleasesAdmin->insert();		
		
		$temp = new Request("Security.Sudo",array('Action'=>'stop'));
		
		if (count($errors) == 0) {	

			// Notify website admin
			require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');	
			$user = ss_getUser();
			$data = array(
				'as_id'	=>	$asset->getID(),
				'rel_id'		=>	$mediaReleasesAdmin->primaryKey,
				'CurrentServer'	=>	$GLOBALS['cfg']['currentServer'],
				'Poster'	=>	$user['us_first_name'].' '.$user['us_last_name'],
				'SiteName'	=>	$GLOBALS['cfg']['website_name'],
				'BackURL'	=>	$GLOBALS['cfg']['currentServer'].'index.php?act=MediaReleasesAdministration.List&as_id='.$asset->getID(),
			);
			
			ss_paramKey($asset->cereal,$this->fieldPrefix.'NOTIFICATION_EMAIL_ADDRESS',$GLOBALS['cfg']['EmailAddress']);
			
			$email = $this->processTemplate('NotificationEmail',$data);
			$mailer = new htmlMimeMail();		
			$emailAddress = $asset->cereal[$this->fieldPrefix.'NOTIFICATION_EMAIL_ADDRESS'];
			$mailer->setFrom($emailAddress);
			$mailer->setSubject("New media release awaiting approval on your website.");				
			$mailer->setHTML($email);				
			$mailer->send(array($emailAddress));
			
			// Send to thank you page
			locationRelative($assetPath."?Service=ThankYou");
			
		} 
	}
?>