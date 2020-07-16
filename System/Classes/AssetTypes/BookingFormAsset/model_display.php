<?php
	if ($this->ATTRIBUTES['Service'] == 'fillForm') {
	$success = false;
	$errors = array();
	
	if (array_key_exists("DoAction",$asset->ATTRIBUTES)) {
		
		$fieldSet->loadFieldValuesFromForm($asset->ATTRIBUTES);
	
		$errors = $fieldSet->validate();
		
		if (count($errors) == 0) {
			// then send the email

			ss_paramKeyAndNoStringLength($asset->cereal,$this->fieldPrefix.'EMAIL_RECIPIENT',$GLOBALS['cfg']['EmailAddress']);
			ss_paramKeyAndNoStringLength($asset->cereal,$this->fieldPrefix.'EMAIL_SUBJECT','Website Enquiry');

			$fromAddress = null;
			$nameValue = null;
			$htmlEmail = '';
			
			$siteRoot = ss_withTrailingSlash(dirname($_SERVER['SCRIPT_FILENAME']));
			$fileName = $siteRoot.'Custom/ContentStore/Templates/'.$GLOBALS['cfg']['currentSiteFolder'].'BookingFormAsset/Email_'.$asset->getID().'.html';
			if (file_exists($fileName)) {

				$data = array();
				$htmlEmail = processTemplate('Custom/ContentStore/Templates/'.$GLOBALS['cfg']['currentSiteFolder'].'BookingFormAsset/Email_'.$asset->getID().'.html',$data);
				// Make image references absolute web urls		
				foreach (array('/<img[^>]* src="([^"]+)"[^>]*>/is','/background="([^"]+)"/is','/<link[^>]* href="([^"]+\.css)"[^>]*>/is') as $regex) {
					preg_match_all($regex,$htmlEmail,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
					for ($i=count($matches[0])-1; $i>=0; $i--) {
						// matches[0] : array(0=>'<imgsomestuffsrc="Images/imagename"somestuff>',1=>offset);
						// matches[1] : Images/imagename
						// matches[2] : imagename
			
						$imagePath = $matches[1][$i][0];
						if (substr($imagePath,0,5) != "http:" and substr($imagePath,0,6) != "https:") {
							$imagePath = $GLOBALS['cfg']['currentServer'] . $imagePath;
						}
						
						$htmlEmail = substr_replace($htmlEmail,$imagePath,$matches[1][$i][1],strlen($matches[1][$i][0]));	
					}
				}
				
				
				//$htmlEmail = file_get_contents($fileName);
				$usingTemplate = true;
				foreach($fieldsArray as $fieldDef) {
			
					// Param all the settings we might need
					ss_paramKey($fieldDef,'name','Unknown');
					ss_paramKey($fieldDef,'type','Unknown');
					ss_paramKey($fieldDef,'uuid','');		
					ss_paramKey($fieldDef,'prefixed',0);		

					$htmlEmail = stri_replace('['.$fieldDef['name'].']',$fieldSet->getFieldDisplayValue('F'.$fieldDef['uuid']),$htmlEmail);

					// Figure out a from address
					if ($fromAddress == null and $fieldDef['type'] == 'EmailField') {
						$fromAddress = $fieldSet->getFieldValue('F'.$fieldDef['uuid']);
					}

					// Figure out a name field
					if ($nameValue == null and $fieldDef['type'] == 'NameField') {
						$nameValue = $fieldSet->getFieldDisplayValue('F'.$fieldDef['uuid']);
					}				
				}
				
			} else {
				$usingTemplate = false;
			
				foreach($fieldsArray as $fieldDef) {
			
					// Param all the settings we might need
					ss_paramKey($fieldDef,'name','Unknown');
					ss_paramKey($fieldDef,'type','Unknown');
					ss_paramKey($fieldDef,'uuid','');		
					ss_paramKey($fieldDef,'prefixed',0);		
			
					// Construct some field desciption html
					$fieldDescriptionHTML = "<td valign=\"top\"><strong>".ss_HTMLEditFormat($fieldDef['name'])."</strong></td>";
					
					// Figure out a from address
					if ($fromAddress == null and $fieldDef['type'] == 'EmailField') {
						$fromAddress = $fieldSet->getFieldValue('F'.$fieldDef['uuid']);
					}
	
					// Figure out a name field
					if ($nameValue == null and $fieldDef['type'] == 'NameField') {
						$nameValue = $fieldSet->getFieldDisplayValue('F'.$fieldDef['uuid']);
					}
					
					
					// Get the value to display
					$fieldCellHTML = '';
					if ($fieldDef['type'] != 'Comment') {
						$fieldCellHTML = "<td>".$fieldSet->getFieldDisplayValue('F'.$fieldDef['uuid'])."</td>";
					}
	
					// join it all up
					$htmlEmail .= "<tr>$fieldDescriptionHTML $fieldCellHTML</tr>";
				}				
				
				$htmlEmail = "<html><body><table width=\"100%\">$htmlEmail</table></body></html>";
			}
				
			// get a transaction id
			$prepareTransaction = new Request("WebPay.PreparePayment",array(
				'tr_currency_link'	=>	554, 
				'tr_client_name'		=> $nameValue,
			));
		
			$tr_id = $prepareTransaction->value['tr_id'];
			
			// record the booking enquiry
			$res = query("
				INSERT INTO booking_form_bookings
					(bo_as_id, bo_details, bo_tr_id, bo_date, bo_email_address)
				VALUES
					(".$asset->getID().", '".escape($htmlEmail)."', {$tr_id}, Now(), '".escape($fromAddress)."' )
			");

			$req = query("
				UPDATE transactions
				SET tr_reference = '{$tr_id}'
				WHERE tr_id = {$tr_id}
			");
			

			// Add the transaction id into the email
			$htmlEmail = "<p>Below are details for booking: <strong>$tr_id</strong></p>".$htmlEmail;
			
			// send the email
			require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
			$mailer = new htmlMimeMail();
			$mailer->setFrom($fromAddress);
			$mailer->setSubject($asset->cereal[$this->fieldPrefix.'EMAIL_SUBJECT']);
			$mailer->setHtml($htmlEmail,'Please use an HTML enabled mail client to view this email.');
			$mailer->send(array($asset->cereal[$this->fieldPrefix.'EMAIL_RECIPIENT']));
			
			$success = true;	
		}
	} else {
		$fieldSet->loadFieldValuesFromForm($asset->ATTRIBUTES,true);
	}
	}
	
	if ($this->ATTRIBUTES['Service'] == 'ThankYou') {
		$this->param('bo_id', '');
		$this->param('tr_id', '');
		$this->param('tr_token', '');
		if (strlen($this->ATTRIBUTES['bo_id']) and strlen($this->ATTRIBUTES['tr_id']) and strlen($this->ATTRIBUTES['tr_token'])) {
			$Transaction = getRow("
				SELECT tr_id FROM transactions
				WHERE 
					tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
					AND 
					tr_token LIKE '{$this->ATTRIBUTES['tr_token']}'
			");
			
			$Booking = getRow("
				SELECT bo_id, bo_email_address FROM booking_form_bookings
				WHERE bo_tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
			");
			
			if ($Booking['bo_id'] == $this->ATTRIBUTES['bo_id'] and strlen($Transaction['tr_id'])) {
				// Add the transaction id into the email
				$emaildata = array();
				$emaildata['tr_id'] = $this->ATTRIBUTES['tr_id'];
				$emaildata['bo_email_address'] = $Booking['bo_email_address'];
				
				$htmlEmail = $this->processTemplate('Email_OrderReceived', $emaildata);			
				// send the email
				require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
				$mailer = new htmlMimeMail();
				$mailer->setFrom($asset->cereal[$this->fieldPrefix.'EMAIL_RECIPIENT']);
				$mailer->setSubject("Booking Order Payment Received - {$GLOBALS['cfg']['website_name']}");
				$mailer->setHtml($htmlEmail,'Please use an HTML enabled mail cliet to view this email.');
				//$mailer->send(array('nam@innovativemedia.co.nz'));
				$mailer->send(array($asset->cereal[$this->fieldPrefix.'EMAIL_RECIPIENT']));
				//ss_DumpVarDie($htmlEmail, 'inside', true);
			}
			//ss_DumpVarDie($Booking, 'outside', true);
		}
	}
	
?>