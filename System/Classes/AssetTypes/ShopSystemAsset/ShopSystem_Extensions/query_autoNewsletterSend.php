<?php

	$rex_debug = 0;

	$this->param('Password','');
	if ($this->ATTRIBUTES['Password'] != '45kgidy5') die('.');
	//die('testing');

	// we have all the time in the world ;)
	set_time_limit(0);

	$this->display->layout = 'none';

	/*// build the $data structure

	$this->useTemplate('AutoNewsletter',$data);
	*/
	// Figure out who to send to
	$Q_Recipients = query("
		SELECT us_id, us_email, us_last_name, us_first_name FROM users, user_user_groups
		WHERE uug_us_id = us_id
			AND uug_ug_id = 3
			AND us_no_spam IS NULL
			AND us_bl_id IS NULL
			AND (us_email_next IS NULL OR us_email_next <= CURDATE())
	");

	//$dataSave = $data;

	// Clear this weeks winner flag from all the lottery winners
	$Q_ClearWeekly = query("
		UPDATE lottery_winners
		SET lotw_this_week = NULL
	");
	$FreeBox = getRow("
		SELECT * FROM lottery_winners
		WHERE lotw_draw_date IS NULL
			AND lotw_upcoming = 1
	");
	if ($FreeBox !== null)
	{
		$weeks1ago = mktime(0,0,0,month(time()),day(time())-7,year(time()));

		$LastFreeBox = getRow("
			SELECT MAX(lotw_draw_date) as TheMax FROM lottery_winners
			WHERE lotw_draw_date IS NOT NULL
		");
		if ($LastFreeBox !== null and $LastFreeBox['TheMax'] !== null)
		{
			$startDate = "'".$LastFreeBox['TheMax']."'";
		}
		else
		{
			$startDate = ss_TimeStampToSQL(mktime(0,0,0,month(time()),day(time()),year(time())-1));
		}

		$endDate = ss_TimeStampToSQL(time());

		$Q_PeoplePoints = query("
			SELECT UsPouug_us_id, SUM(up_points) >= 1000 AS CanWin FROM shopsystem_user_points
			WHERE up_used IS NULL
			AND up_expires > CURDATE()
			GROUP BY UsPouug_us_id
			ORDER BY CanWin DESC
		");
		$canWin = "-1";
		while ($row = $Q_PeoplePoints->fetchRow())
		{
			if ($row['CanWin'])
			{
				$canWin .= ','.$row['UsPouug_us_id'];	
			}	
		}
		if (strlen($canWin) > 2)
		{
			$Winner = getRow("
				SELECT Max(or_id) AS or_id FROM transactions, shopsystem_orders
   				WHERE 1
					AND or_tr_id = tr_id
					AND tr_completed = 1 
					AND or_deleted = 0
					AND or_paid IS NOT NULL
					AND or_us_id IN ($canWin)
    			ORDER BY RAND(NOW())
				LIMIT 1
			");
		}
		else
		{
			$Winner = getRow("
				SELECT or_id FROM transactions, shopsystem_orders
	   			WHERE 	1
					AND or_tr_id = tr_id
					AND tr_completed = 1 
					AND tr_total >= 200
					AND or_deleted = 0
					AND or_paid IS NOT NULL
			 		AND (or_recorded BETWEEN $startDate AND $endDate)
	    		ORDER BY RAND(NOW())
				LIMIT 1
			");
		}

		//$Winner['or_id'] = 1944;

		// update the lotterywinners with the order id of the winner and flag the time it was drawn
		$Q_Update = query("
			UPDATE lottery_winners
			SET lotw_or_id = {$Winner['or_id']},
				lotw_draw_date = $endDate,
				lotw_this_week = 1
			WHERE lotw_draw_date IS NULL
				AND lotw_upcoming = 1
		");
		$winningOrderID = $Winner['or_id'];
		include('inc_createLotteryOrder.php');

	}

	// make next weeks box an upcoming box
	$Q_Update = query("
		UPDATE lottery_winners
		SET lotw_upcoming = 1
		WHERE lotw_draw_date IS NULL
	");



//	$batchSize = 10;
//	$currentBatchSize = 0;
//	$batch = '-1';
//	$counter = 0;

//	$sh = "#!/bin/sh\nPATH=/bin:/usr/bin:/usr/local/bin\n";
	require('inc_autoNewsletter.php');

	$dataSave = $data;

	$this->display->layout = 'none';

	while ($recipient = $Q_Recipients->fetchRow())
	{
		$data = $dataSave;


		// build the $data structure

		//$this->useTemplate('AutoNewsletter',$data);

		// find out how many points the recipient has
		$recipientPoints = 0;
		$CheckPoints = getRow("SELECT SUM(up_points) AS TotalPoints FROM shopsystem_user_points
					WHERE UsPouug_us_id = {$recipient['us_id']}
						AND up_used IS NULL AND up_expires > CURDATE()");		

		if ($CheckPoints !== null and $CheckPoints['TotalPoints'] !== null)
			$recipientPoints = $CheckPoints['TotalPoints'];

		// user specific values for the newsletter
		$data['last_name'] = $recipient['us_last_name'];
		$data['first_name'] = $recipient['us_first_name'];
		$data['Points'] = $recipientPoints;


		//$to = $flip?'mattcurrie188@gmail.com':'bluenz@gmail.com';
		if( $rex_debug == 1 )
			$to = 'rex@admin.com';
//			$to = 'macbjorck@mac.com';
		else
			$to = $recipient['us_email'];

		$result = new Request('Email.Send',array
			(
			'useTemplate'	=>	false,
			'to'	=>	$to,
//			'from'	=>	$GLOBALS['cfg']['EmailAddress'],
			'from'	=>	'admin@acmerockets.com',
			'subject'	=>	'Acme Express - Weekly Newsletter',
			'html'	=>	$this->processTemplate('AutoNewsletter',$data),
			));

//		ss_DumpVar( $data );
//		ss_DumpVar( $this->processTemplate('AutoNewsletter',$data) );

		print('Sent to '.$recipient['us_last_name']. ' '.$recipient['us_email']."<br>\n");

		if( $rex_debug == 1 )
		{
			print( "Actually sent to rex@admin.com\n" );
			sleep(10);
		}
		else
		{
			$Q_Update = query( "UPDATE users
						SET us_email_next = CURDATE()+INTERVAL 6 day
						WHERE us_email = '".$recipient['us_email']."'" );
			sleep(1);
		}

	}

//		$currentBatchSize++;
//		$counter++;
//		$batch .= ','.$recipient['us_id'];
//
//		// request the batch
//		if ($currentBatchSize == $batchSize or $counter == $Q_Recipients->numRows()) {
//			
//			$password = ss_urlEncodedFormat($this->ATTRIBUTES['Password']);
//			$encodedBatch = ss_urlEncodedFormat($batch);
//
//			$sh .= "wget --output-document=- -q 'http://www.acmerockets.com/index.php?act=ShopSystem.AcmeAutoNewsletterSendBatch&Password={$password}&Recipients={$encodedBatch}'\n";
//
//			$output = array();
//			$phpCommand = "file_get_contents(\"http://www.acmerockets.com/index.php?act=ShopSystem.AcmeAutoNewsletterSendBatch&Password={$password}&Recipients={$encodedBatch}\");";
//			$execCommand = "php -r '$phpCommand' &>/dev/null";
//			print($execCommand."\n");
//			exec($execCommand,$output);
//			ss_log_message_r($output);
//			//print(file_get_contents('http://www.acmerockets.com/index.php?act=ShopSystem.AcmeAutoNewsletterSendBatch&Password='.$this->ATTRIBUTES['Password'].'&Recipients='.ss_urlEncodedFormat($batch)));
//			print("Sent to $batch \n");
//			$batch = '-1';
//			$currentBatchSize = 0;
//		}
//		
//	}
//
//	$fp = fopen(expandPath('Custom/sendNewsletter'),'w');
//	fwrite($fp,$sh);
//	fclose($fp);

?>
