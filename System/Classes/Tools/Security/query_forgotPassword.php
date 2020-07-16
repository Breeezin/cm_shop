<?php
	global $sql;
	global $cfg;
	
	$this->param("BackURL");
	$completed = false;
	$displayData['hasError'] = false;
	$displayData['EntryErrors'] = array();
	$displayData['completed'] = false;
	$displayData['BackURL'] = $this->ATTRIBUTES['BackURL'];
		
	
	if (array_key_exists("DoAction", $this->ATTRIBUTES)) {
		/* Try and find the user */
		$this->param('Email','');
	
		$result = $sql->query("
			SELECT * FROM users
			WHERE us_email = '".escape($this->ATTRIBUTES['Email'])."'
		");
		
		if ($result->numRows() > 0) {
			/* Found the user, send an email */
			$details = $result->fetchRow();
			
			$textContent = $this->processTemplate("eml_Password", $details, true, 'txt');
			$htmlContent = $this->processTemplate("eml_Password", $details, true);
			$configContactDetails = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true);					
			
			$result = new Request('Email.Send',array(
				'to'	=>	$details['us_email'],
				'from'	=>	$cfg['EmailAddress'],
				'html'	=>	$htmlContent,
				'subject'	=>	'Password Reminder',
			));
			
			/*$mailer = new htmlMimeMail();
			$mailer->setFrom();
			$mailer->setSubject("Password Reminder");
			$mailer->setHTML($htmlContent."<p>$configContactDetails</p>", $textContent, "/tmp");
			$mailer->send(array($details['us_email']));*/
			$displayData['completed'] = true;
		} else {
			$displayData['hasError'] = true;
			$displayData['completed'] = false;
			$displayData['EntryErrors']['Message'] = array("Unknown user account '{$this->ATTRIBUTES['Email']}', please try again.");
		}			
	}
?>
