<?php 
	
	global $cfg;
	$crap = false;

	$this->param("TellingAbout","");	
	$this->param("LinkText","this");	
	$this->param("BeforeText","");	
	$this->param("AfterText","interesting information");	
	$this->param("Message","");
	$this->param("ToSubject","Your friend would like to tell you about our web site");
	
	if( !strncmp( $_SERVER['REMOTE_ADDR'], "112.202", 7 ) || strpos( $_SERVER['HTTP_USER_AGENT'], "iOpus" ) )
	{
		echo '
		<html>
		<div style="vertical-align: top;">
<p>First <font size="4">Paragraph</p>
<p>Second </font> Paragraph</p>
</div>
';
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_POST );
		$crap = true;
	}
	
	$isLoggedInResult = new Request("Security.Authenticate",array(
		'Permission'	=>	'IsLoggedIn',
		'LoginOnFail'	=>	'no',
	));
	
	if ($isLoggedInResult->value) {
		$this->param("us_first_name",$_SESSION['User']['us_first_name']);
		$this->param("us_last_name",$_SESSION['User']['us_last_name']);
		$this->param("us_email",$_SESSION['User']['us_email']);	
	} else {
		$this->param("us_first_name","");
		$this->param("us_last_name","");
		$this->param("us_email","");
	}	
	
	ss_paramKey($asset->cereal, "AST_TELLAFRIEND_LIMIT", 5);
	for($i=1; $i <= $asset->cereal['AST_TELLAFRIEND_LIMIT']; $i++) {			
		$this->param("ToEmail$i","");
	}		
	$errors = array();	
	
	if (array_key_exists("DoAction", $this->ATTRIBUTES)) {		
		if (!strlen($this->ATTRIBUTES['us_first_name']) AND !strlen($this->ATTRIBUTES['us_last_name'])) array_push($errors, "Please type your full name.");
			
		require('System/Libraries/htmlMimeMail/htmlMimeMail.php');
		$data = array();
		$data['FromName'] = "{$this->ATTRIBUTES['us_first_name']} {$this->ATTRIBUTES['us_last_name']}";
		$temp = array();
		$temp['us_first_name'] = $this->ATTRIBUTES['us_first_name'];
		$temp['us_last_name'] = $this->ATTRIBUTES['us_last_name'];
		$temp['BeforeText'] = $this->ATTRIBUTES['BeforeText'];
		
		$tellLink = str_replace($GLOBALS['cfg']['currentServer'], '', $this->ATTRIBUTES['TellingAbout']);		
		$temp['TellingAbout'] = $GLOBALS['cfg']['currentServer'].$tellLink;
		$temp['LinkText'] = $this->ATTRIBUTES['LinkText'];
		$temp['AfterText'] = $this->ATTRIBUTES['AfterText'];
		
		$data['HTMLContent'] = $this->processTemplate('HtmlTellAFriend',$temp);
		$data['HTMLContent'] .="<P>{$this->ATTRIBUTES['Message']}</P>";
		$data['website_name'] = $cfg['website_name'];
		$data['WebSiteAddress'] = $cfg['currentServer'];
		$data['TellingAbout'] = $temp['TellingAbout'];
		$data['Message'] = $this->ATTRIBUTES['Message'];
		$data['Subject'] = $this->ATTRIBUTES['ToSubject'];
		
		$HTMLContent = $this->processTemplate('TellAFriend', $data);
		$TXTContent = $this->processTemplate('TextTellAFriend',$data);
		
		$mailer = new htmlMimeMail();
		
		if (strlen($this->ATTRIBUTES['us_email']))
			$mailer->setFrom($this->ATTRIBUTES['us_email']);
		else 
			array_push($errors, "Please type your email address.");
			
		if (strlen($this->ATTRIBUTES['ToSubject']))
			$mailer->setSubject($this->ATTRIBUTES['ToSubject']);
		else 
			array_push($errors, "Please type the subject.");
		
		$configContactDetails = ss_parseText($GLOBALS['cfg']['ContactDetails'], null, true);					
				
		$mailer->setHTML($HTMLContent."<p>$configContactDetails<p>", $TXTContent, "/tmp");		
		$sent = false;
		for($i=1; $i <= $asset->cereal['AST_TELLAFRIEND_LIMIT']; $i++) {	
			$value = $this->ATTRIBUTES["ToEmail$i"];		
			if (strlen($value) AND !count($errors)) {
				if( !$crap )
					$mailer->send(array("$value"));
/*				else	*/
/*					$mailer->send(array("hildadibdib@gmail.com"));	*/
				$sent = true;
			}
		}
					
					
		if (!$sent AND !count($errors)) array_push($errors, "Please type your friend's email address.");
	}
	
	//ss_DumpVar($this, "first");
?>
