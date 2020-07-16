<?php

	$this->param('nei_id');
	$this->param('UsersList');
	
	$Q_Recipients = query("
		SELECT * FROM users
		WHERE us_id IN (".$this->ATTRIBUTES['UsersList'].")
	");
	
	$newsletter = getRow("
		SELECT * FROM news_items
		WHERE nei_id = ".safe($this->ATTRIBUTES['nei_id'])."
	");	
	$emailText = "<h3>".ss_HTMLEditFormat($newsletter['nei_headline'])."</h3>".ss_parseText($newsletter['nei_body']);
	
	while ($recipient = $Q_Recipients->fetchRow()) {

		//print($recipient['us_email'].$emailText.'News from '.$GLOBALS['cfg']['website_name'];
		$result = new Request('Email.Send',array(
			'to'	=>	$recipient['us_email'],
			'from'	=>	$GLOBALS['cfg']['EmailAddress'],
			'subject'	=>	'News from '.$GLOBALS['cfg']['website_name'],
			'html'	=>	$emailText,
		));		
		
	}

?>