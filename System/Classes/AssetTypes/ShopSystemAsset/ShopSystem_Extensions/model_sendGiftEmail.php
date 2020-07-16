<?
	/*********************************************
	** Figure out who we are sending this to... **
	*********************************************/
	
	// firstly.. get a list of everyone who has ordered in the past 60 days
	$Q_DoNotSend = query("
		SELECT or_us_id FROM shopsystem_orders
		WHERE or_recorded > NOW() - INTERVAL 60 DAY	
	");
	$doNotSend = $Q_DoNotSend->columnValuesList('or_us_id',',','');
	
	// now find anyone else who ordered previous to sixty days and hasn't received an email yet
	$Q_DoSend = query("
		SELECT or_id, or_purchaser_email FROM shopsystem_orders
		WHERE or_recorded < NOW() - INTERVAL 60 DAY	
			AND or_follow_up_status IS NULL
			AND or_us_id NOT IN ($doNotSend)
			AND or_paid IS NOT NULL
	");
	
	/*$Q_Update = query("
		UPDATE shopsystem_orders
		SET or_follow_up_status = 'Too Old'
		WHERE or_recorded < NOW() - INTERVAL 60 DAY	
	");*/
	
	include('inc_giftEmail.php');
	
	$content = $this->processTemplate('GiftEmail',$data);
	
	while ($row = $Q_DoSend->fetchRow()) {
	
		$Q_UpdateOrder = query("
			UPDATE shopsystem_orders
			SET or_follow_up_status = 'Sent Email'
			WHERE or_id = {$row['or_id']}
		");
		
		$temp = new Request("Email.Send",array(
			'to'	=>	$row['or_purchaser_email'],
			'from'	=>	'admin@acmerockets.com',
			'subject'	=>	'Special Offer from AcmeRockets.com!',
			'html'	=>	$content,
			'useTemplate'	=>	false,
		));
		
		echo "Sent to ".ss_HTMLEditFormat($row['or_purchaser_email'])."<br>";
	}

	echo "Done";
?>