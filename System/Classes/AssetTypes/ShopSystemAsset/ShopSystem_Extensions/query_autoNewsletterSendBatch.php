<?php

	$this->param('Recipients');

	$this->param('Password','');
	if ($this->ATTRIBUTES['Password'] != '45kgidy5') die('.');
	//die('testing');

	// we have all the time in the world ;)
	set_time_limit(0);

	$this->display->layout = 'none';

	// build the $data structure
	require('inc_autoNewsletter.php');

	//$this->useTemplate('AutoNewsletter',$data);
	
	// Figure out who to send to
	$Q_Recipients = query("
		SELECT us_id, us_email, us_last_name, us_first_name FROM users
		WHERE us_id IN ({$this->ATTRIBUTES['Recipients']})
	");
	
	$dataSave = $data;
	
	//$recipient['us_email'],
	$flip = false;
	while ($recipient = $Q_Recipients->fetchRow()) {
		$flip = !$flip;
		$data = $dataSave;
		
		// find out how many points the recipient has
		$recipientPoints = 0;
		$CheckPoints = getRow("
			SELECT SUM(up_points) AS TotalPoints FROM shopsystem_user_points
			WHERE up_us_id = {$recipient['us_id']}
				AND up_used IS NULL
		");		
		if ($CheckPoints !== null and $CheckPoints['TotalPoints'] !== null) {
			$recipientPoints = $CheckPoints['TotalPoints'];
		}
	
		
		// user specific values for the newsletter
		$data['last_name'] = $recipient['us_last_name'];
		$data['first_name'] = $recipient['us_first_name'];
		$data['Points'] = $recipientPoints;

		
		//$to = $flip?'mattcurrie188@gmail.com':'bluenz@gmail.com';
		$to = $recipient['us_email'];
		
		$result = new Request('Email.Send',array(
			'useTemplate'	=>	false,
			'to'	=>	$to,
			'from'	=>	$GLOBALS['cfg']['EmailAddress'],
			'subject'	=>	'Acme Express - Weekly Newsletter',
			'html'	=>	$this->processTemplate('AutoNewsletter',$data),
		));
		print('Sent to '.$recipient['us_last_name']. ' '.$recipient['us_email']."<br>\n");
	}
	
?>