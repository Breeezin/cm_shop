<?
	$this->display->layout = 'none';

	$this->param('useTemplate',1);
	
	$this->param('to');			// single email address, or an array
	$this->param('from');
	$this->param('subject');
	$this->param('css',null);	// single ccs file, or an array
	
	$this->param('html',null);
	$this->param('text',null);
	$this->param('templateFolder',$GLOBALS['cfg']['folder_name']); // without trailing slash

//	$this->param('SMTPParams', null);
	$this->param('smtpHost', 'localhost');
	$this->param('smtpPort', 587);

	set_error_handler('noErrorHandler');

	// set up the email
//	require_once('System/Libraries/htmlMimeMail/htmlMimeMail.php');
	require_once( "System/Libraries/Rmail/Rmail.php" );
//	$mailer = new htmlMimeMail();
	$mailer = new Rmail();
	$mailer->setFrom($this->ATTRIBUTES['from']);
	$mailer->setSubject($this->ATTRIBUTES['subject']);

	$type = 'smtp';
	// $mailer->setSMTPParams("localhost", 587);
	$mailer->setSMTPParams( $this->ATTRIBUTES['smtpHost'], $this->ATTRIBUTES['smtpPort'] );

	/*
	if( $this->ATTRIBUTES['SMTPParams'] )
	{
		$params = explode( ',', $this->ATTRIBUTES['SMTPParams'] );
		$mailer->setSMTPParams($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6]);
		$type = 'smtp';
	}
	*/

	// figure out what content to send
	if ($this->ATTRIBUTES['html'] !== null) {

		$htmlMessage = $this->ATTRIBUTES['html'];
		
		// use the HTML template if we want to
		if ($this->ATTRIBUTES['useTemplate']) {
			// Construct the html email
			
			$ExtraStyleSheets = '';
			if ($this->ATTRIBUTES['css'] !== null) {
				if (!is_array($this->ATTRIBUTES['css'])) $this->ATTRIBUTES['css'] = array($this->ATTRIBUTES['css']);
				foreach($this->ATTRIBUTES['css'] as $styleSheet) {
					$ExtraStyleSheets .= '<link rel="stylesheet" href="'.$styleSheet.'" type="text/css">';
				}
			}
			$data = array(
				'ExtraStyleSheets'	=>	$ExtraStyleSheets,
				'Content'	=>	$this->ATTRIBUTES['html'],	
			);

			// if a template folder is defined, make sure we grab the correct configuration
			if (strlen($this->ATTRIBUTES['templateFolder'])) {
				$cfg = getRow("
					SELECT * FROM configuration
					WHERE cfg_folder_name LIKE '".escape($this->ATTRIBUTES['templateFolder'])."'
				");
				$data['ContactDetails'] = ss_parseText($cfg['cfg_contact_details'], null, true);
			} else {
				$data['ContactDetails'] = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true);
			}
			
			$addInFolder = '';
			if (strlen($this->ATTRIBUTES['templateFolder'])) $addInFolder = $this->ATTRIBUTES['templateFolder'].'/';
			
			if (file_exists("Custom/ContentStore/Templates/{$addInFolder}Email/Email.html")) {
				$htmlMessage = processTemplate("Custom/ContentStore/Templates/{$addInFolder}Email/Email.html",$data);
				//ss_DumpVarDie("Custom/ContentStore/Templates/{$addInFolder}Email/Email.html",$htmlMessage, true);
			} else if (file_exists("Custom/ContentStore/Templates/Email/Email.html")) {
				$htmlMessage = processTemplate("Custom/ContentStore/Templates/Email/Email.html",$data);
				//ss_DumpVarDie("Custom/ContentStore/Templates/Email/Email.html",$htmlMessage, true);
			} else {
				$htmlMessage = processTemplate("System/Classes/Tools/Email/Templates/Email.html",$data);
				//ss_DumpVarDie("System/Classes/Tools/Email/Templates/Email.html",$htmlMessage, true);
			}
			
			
			
				
		}

		// So we dont want to embed the images? We'll hard link them to the
		// website then.... Just hope your newsletter recipients are always online eh...
		foreach (array('/<img[^>]* src="([^"]+)"[^>]*>/is','/background="([^"]+)"/is','/<link[^>]* href="([^"]+\.css)"[^>]*>/is','/<a[^>]* href="([^"]+)"[^>]*>/is') as $regex) {
			preg_match_all($regex,$htmlMessage,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
			for ($i=count($matches[0])-1; $i>=0; $i--) {
				// matches[0] : array(0=>'<imgsomestuffsrc="Images/imagename"somestuff>',1=>offset);
				// matches[1] : Images/imagename
				// matches[2] : imagename
	
				$imagePath = $matches[1][$i][0];
				if (substr($imagePath,0,5) != "http:" and substr($imagePath,0,6) != "https:" and substr($imagePath,0,7) != "mailto:" and substr($imagePath,0,4) != "ftp:") {
					$imagePath = $GLOBALS['cfg']['plaintext_server'] . $imagePath;
				}
				
				$htmlMessage = substr_replace($htmlMessage,$imagePath,$matches[1][$i][1],strlen($matches[1][$i][0]));	
			}
		}			
		
		
		$this->ATTRIBUTES['html'] = $htmlMessage;
		
		// set the html (with text if text is defined)
		if ($this->ATTRIBUTES['text'] !== null) {
			$mailer->setHtml($this->ATTRIBUTES['html'],$this->ATTRIBUTES['text']);
		} else {
			$mailer->setHtml($this->ATTRIBUTES['html'],'Please use an HTML enabled mail client to view this email.');
		}
	} else if ($this->ATTRIBUTES['text']) {
		$mailer->setText($this->ATTRIBUTES['text']);
	} else {
		//trigger_error('No email message was defined!');	
		return;
	}

	// now we send the email
	if (is_array($this->ATTRIBUTES['to'])) {
		if( array_key_exists( 'testMode', $GLOBALS['cfg'] )
		  && ( $GLOBALS['cfg']['testMode'] ) )
			$mailer->send(array('im@admin.com'), $type);	
		else
			foreach ($this->ATTRIBUTES['to'] as $toEmail) {
			{
				$res = $mailer->send(array($toEmail), $type);	
				$out = $mailer->getSMTPParams();
				ss_log_message( "Email.Send: to:".$this->ATTRIBUTES['to']." from:".$this->ATTRIBUTES['from']." subject:".$this->ATTRIBUTES['subject']." result:".$res );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $out );
			}
		}
	} else {						
		if( array_key_exists( 'testMode', $GLOBALS['cfg'] )
		  && ( $GLOBALS['cfg']['testMode'] ) )
			$mailer->send(array('im@admin.com'), $type);	
		else
		{
			$res = $mailer->send(array($this->ATTRIBUTES['to']), $type);			
			$out = $mailer->getSMTPParams();
			ss_log_message( "Email.Send: to:".$this->ATTRIBUTES['to']." from:".$this->ATTRIBUTES['from']." subject:".$this->ATTRIBUTES['subject']." result:".$res );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $out );
		}

	}
	
?>
