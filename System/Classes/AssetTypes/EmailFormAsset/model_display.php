<?php
	$success = false;
	$errors = array();

	if (array_key_exists("DoAction",$asset->ATTRIBUTES)) {

		$fieldSet->loadFieldValuesFromForm($asset->ATTRIBUTES);

		$errors = $fieldSet->validate();

		if (count($errors) == 0) {
			// then send the email

			ss_paramKeyAndNoStringLength($asset->cereal,$this->fieldPrefix.'EMAIL_RECIPIENT',$GLOBALS['cfg']['EmailAddress']);
            ss_paramKeyAndNoStringLength($asset->cereal,$this->fieldPrefix.'EMAIL_SUBJECT','Website Enquiry');

			// grab the subject so we can inject some fields into it
			$subject = $asset->cereal[$this->fieldPrefix.'EMAIL_SUBJECT'];
            //briar - javascript adds element to form for specific product/stock - see affordable caravans
            if (isset($asset->ATTRIBUTES['emailSubject'])){
                $subject = $asset->ATTRIBUTES['emailSubject'];
            }

			$fromAddress = null;
			$fromName = null;
			$htmlEmail = '';
			foreach($fieldsArray as $fieldDef) {

				// Param all the settings we might need
				ss_paramKey($fieldDef,'name','Unknown');
				ss_paramKey($fieldDef,'type','Unknown');
				ss_paramKey($fieldDef,'uuid','');
				ss_paramKey($fieldDef,'prefixed',0);

				// Construct some field desciption html
				$fieldDescriptionHTML = "<td valign=\"top\"><strong>".ss_HTMLEditFormat($fieldDef['name'])."</strong></td>";

				$fieldDisplay = $fieldSet->getFieldDisplayValue('F'.$fieldDef['uuid']);

				// Inject field into subject
				if (ss_optionExists('Email Form Fields In Subject')) {
					$subject = stri_replace('['.$fieldDef['name'].']',html_entity_decode(strip_tags($fieldDisplay)),$subject);
				}

				// Figure out a from address
				if ($fromAddress == null and $fieldDef['type'] == 'EmailField') {
					$fromAddress = $fieldSet->getFieldValue('F'.$fieldDef['uuid']);
				}

				// Figure out a from name
				if ($fromName == null and $fieldDef['type'] == 'NameField') {
					$fromName = $fieldSet->getFieldDisplayValue('F'.$fieldDef['uuid']);
				}

				// Get the value to display
				$fieldCellHTML = '';
				if (($fieldDef['type'] != 'Comment')) {
                    if ($fieldDef['type'] != 'FileField'){
                        $fieldCellHTML = "<td>".$fieldDisplay."</td>";
                    }else {
                        $this->assetLink = 523;
                        $directory = ss_secretStoreForAsset($this->assetLink,$fieldDef['uuid']);
                        $fieldCellHTML = "<td><a href='".$GLOBALS['cfg']['currentServer']."$directory/$fieldDisplay'>Click to download</a></td>";
                    }
				}

				// join it all up
				$htmlEmail .= "<tr>$fieldDescriptionHTML $fieldCellHTML</tr>";
			}

			if ($fromAddress === null) $fromAddress = $GLOBALS['cfg']['EmailAddress'];
			if ($fromName === null) $fromName = 'Unknown';

			// record in the database
			if (ss_optionExists('Email Form Record Enquiries')) {
				if (strlen($fromName) > 255) {
					$fromName = substr($fromName,0,255);
				}
				$id = newPrimaryKey('email_form_submissions','efs_id');
				$Q_Insert = query("
					INSERT INTO email_form_submissions
						(efs_name, efs_email_address, efs_timestamp, efs_as_id, efs_text)
					VALUES
						('".escape($fromName)."', '".escape($fromAddress)."', NOW(), ".$asset->getID().", '<table width=\"100%\">".escape($htmlEmail)."</table>')
				");
			}
			//ss_DumpVar($htmlEmail, '', true);
			$htmlEmail = "<html><body><table width=\"100%\">$htmlEmail</table></body></html>";



			// send the email
			require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
			$mailer = new htmlMimeMail();

			$mailer->setFrom($fromAddress);
			//$mailer->setFrom('admin@innovativemedia.co.nz');
			$mailer->setSubject($subject);
			$mailer->setHtml($htmlEmail,'Please use an HTML enabled mail client to view this email.');
			$mailer->send(array($asset->cereal[$this->fieldPrefix.'EMAIL_RECIPIENT']));
			//if (ss_isItUs()) $mailer->send(array('nam@innovativemedia.co.nz'));
			if (ss_optionExists('Email Form Send Copy To BCC')) {
				if (strlen($GLOBALS['cfg']['BCCAddress'])) {
					$sendTo = array(trim($GLOBALS['cfg']['BCCAddress']));
					$mailer->send($sendTo);
				}
			}/*
			if (ss_isItUs()) {
				$mailer->send(array('im@admin.com'));
			}*/
			//ss_DumpVarDie(array($asset->cereal[$this->fieldPrefix.'EMAIL_RECIPIENT']),$fromAddress, true);
			$success = true;
		}
	} else {
		$fieldSet->loadFieldValuesFromForm($asset->ATTRIBUTES,true);
	}


?>
