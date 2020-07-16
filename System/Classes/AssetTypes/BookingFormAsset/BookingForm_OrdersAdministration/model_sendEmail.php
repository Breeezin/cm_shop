<?php

	$errors = array();
	$close = false;
	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		
		// Validate the data for each field
		// Set up the error array
		//ss_DumpVarDie($this);
		if (array_key_exists($this->fieldSet->tablePrimaryKey,$this->ATTRIBUTES)) {
			$this->fieldSet->primaryKey = $this->ATTRIBUTES[$this->fieldSet->tablePrimaryKey];
		}
		
		// Validate each field and record any errors reported
		$errors = array_merge($errors,$this->fieldSet->validate());
		
		// Update if no errors validating data
		if (count($errors) == 0) {
		
			// Send the email!
	
			$htmlEmail = $this->ATTRIBUTES['Email'];
			$configContactDetails = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true);
			$htmlEmail = '<html><head><link rel="stylesheet" href="'.$GLOBALS['cfg']['currentServer'].'Custom/ContentStore/Layouts/sty_main.css" type="text/css"></head><body>'.$htmlEmail.'<p>'.$configContactDetails.'</p></body></html>';
			
			// send the email
			require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
			$mailer = new htmlMimeMail();
			$mailer->setFrom($GLOBALS['cfg']['EmailAddress']);
			$mailer->setSubject('Secure your booking with '.$GLOBALS['cfg']['website_name']);
			$mailer->setHtml($htmlEmail,'Please use an HTML enabled mail client to view this email.');
			$mailer->send(array($Booking['bo_email_address']));
			
			$close = true;
		}

	}	
?>