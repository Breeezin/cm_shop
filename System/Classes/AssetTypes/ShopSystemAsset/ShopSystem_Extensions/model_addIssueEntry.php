<?php
	// admin adds issue response
	$this->param('tr_id');
	$this->param('BackURL');
	$this->param('Note');

	$existing = getRow( "select * from client_issue where ci_transaction_number = ".(int)$this->ATTRIBUTES['tr_id'] );

	$am = 'NULL';
	if( strlen( $_SESSION['User']['us_admin_level'] ) )
		$am = (int)$_SESSION['User']['us_admin_level'];

	if( $existing )
	{
		$ci_id = (int)$existing['ci_id'];
		$Q_Insert = query("
			INSERT INTO client_issue_response
				(cir_ci_id, cir_text, cir_us_id, cir_adminlevel)
			VALUES
				($ci_id, '".escape($this->ATTRIBUTES['Note'])."', ".ss_getUserID().", $am )
		");

		if( ss_adminCapability( ADMIN_CUSTOMER_ISSUE ) )
		{
			$cir_id = getLastAutoIncInsert();

			if( (int)$this->ATTRIBUTES['SendEmail'] )
			{
				$existing = getRow( "select * from client_issue where ci_id = $ci_id" );

				// send punter an email
				$to = getField( "select us_email from users where us_id = {$existing['ci_us_id']}" );
				$from = "noreply@";
				$noteText = "Your support issue has been responded to.  Please go to your members login page to view it.\n\nhttp://www.acmerockets.com/Members\n\nThanks from the Team at AcmeRockets";
				if( strlen( $existing['ci_transaction_number'] ) )
					$subject = "Issue with order number {$existing['ci_transaction_number']} at ";
				else
					$subject = "Issue number $ci_id at ";

				$domain = 'acmerockets.com';
				$from .= $domain;
				$subject .= $domain;

				$notePretty = "<html>".nl2br( $noteText )."</html>";

				set_error_handler('noErrorHandler');
				require( "System/Libraries/Rmail/Rmail.php" );

				$mailer = new Rmail();
				$mailer->setFrom($from);
				$mailer->setSubject($subject);				
				$mailer->setText($noteText);				
				$mailer->setHTML($notePretty);				
				//$mailer->setSMTPParams("localhost", 25);
				$mailer->setSMTPParams("localhost", 587);
				$result = $mailer->send(array($to), 'smtp');				

				ss_log_message( "Issue Email: from:$from to:$to subject:$subject" );
				ss_log_message( "Issue Email: Text:$noteText" );
				ss_log_message( "Issue Email: Pretty:$notePretty" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );

				if( $result === true )
					query( "update client_issue_response set cir_emailed = '$to' where cir_id = $cir_id" );
				else
				{
					$r = substr( print_r( $result, true ), 0, 63 );
					query( "update client_issue_response set cir_emailed = '$r' where cir_id = $cir_id" );
				}
			}

			if( strlen( $existing['ci_closed'] ) )
				query( "update client_issue set ci_closed = NULL where ci_id = ".$existing['ci_id'] );
		}
	}
	else
	{
		$tr_id = (int)$this->ATTRIBUTES['tr_id'];
		if( $tr_id > 0 )
		{
			$us_id = getField( "select or_us_id from shopsystem_orders where or_tr_id = $tr_id" );
			query( "insert into client_issue (ci_us_id, ci_transaction_number) values ($us_id, $tr_id)" );
			$ci_id = getLastAutoIncInsert();
			query( "insert into client_issue_entry (ce_ci_id, ce_text) values ($ci_id, 'Admin opened this issue')" );
			$Q_Insert = query("
				INSERT INTO client_issue_response
					(cir_ci_id, cir_text, cir_us_id, cir_adminlevel)
				VALUES
					($ci_id, '".escape($this->ATTRIBUTES['Note'])."', ".ss_getUserID().", $am )");

			if( ss_adminCapability( ADMIN_CUSTOMER_ISSUE ) )
			{
				$cir_id = getLastAutoIncInsert();

				if( (int)$this->ATTRIBUTES['SendEmail'] )
				{
					$existing = getRow( "select * from client_issue where ci_id = $ci_id" );

					// send punter an email
					$to = getField( "select us_email from users where us_id = {$existing['ci_us_id']}" );
					$from = "noreply@";
					$noteText = "A new support issue has been created.  Please go to your members login page to view it.\n\nhttp://www.acmerockets.com/Members\n\nThanks from the Team at AcmeRockets";
					if( strlen( $existing['ci_transaction_number'] ) )
						$subject = "Issue with order number {$existing['ci_transaction_number']} at ";
					else
						$subject = "Issue number $ci_id at ";

					$domain = 'acmerockets.com';
					$from .= $domain;
					$subject .= $domain;

					$notePretty = "<html>".nl2br( $noteText )."</html>";


					set_error_handler('noErrorHandler');
					require( "System/Libraries/Rmail/Rmail.php" );

					$mailer = new Rmail();
					$mailer->setFrom($from);
					$mailer->setSubject($subject);				
					$mailer->setText($noteText);				
					$mailer->setHTML($notePretty);				
					//$mailer->setSMTPParams("localhost", 25);
					$mailer->setSMTPParams("localhost", 587);
					$result = $mailer->send(array($to), 'smtp');				

					ss_log_message( "Issue Email: from:$from to:$to subject:$subject" );
					ss_log_message( "Issue Email: Text:$noteText" );
					ss_log_message( "Issue Email: Pretty:$notePretty" );
					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $result );

					if( $result === true )
						query( "update client_issue_response set cir_emailed = '$to' where cir_id = $cir_id" );
					else
					{
						$r = substr( print_r( $result, true ), 0, 63 );
						query( "update client_issue_response set cir_emailed = '$r' where cir_id = $cir_id" );
					}
				}
			}
		}
	}

	locationRelative($this->ATTRIBUTES['BackURL']);
?>
