<?php
	// admin adds issue response
	$this->param('ci_id');
	$this->param('SendEmail', 0);
	$this->param('BackURL');
	$this->param('Note');

	$domain = 'acmerockets.com';

	$ci_id = (int)$this->ATTRIBUTES['ci_id'];

	$note = escape($this->ATTRIBUTES['Note']);

	$am = "NULL";
	if( strlen( $_SESSION['User']['us_admin_level'] ) )
		$am = (int)$_SESSION['User']['us_admin_level'];

	if( $ci_id )
	{
		// check that this isn't assigned to another admin...
		$existing = getRow( "select * from client_issue where ci_id = $ci_id" );

		if( $existing['ci_assigned_to'] && ($existing['ci_assigned_to'] != ss_getUserID() ) )
		{
			// nope nope nope
			locationRelative($this->ATTRIBUTES['BackURL']);
		}

		$Q_Insert = query("
			INSERT INTO client_issue_response
				(cir_ci_id, cir_text, cir_us_id, cir_adminlevel)
			VALUES
				($ci_id, '$note', ".ss_getUserID().", $am )
		");

		if( ss_adminCapability( ADMIN_CUSTOMER_ISSUE ) )
		{
			$cir_id = getLastAutoIncInsert();

			query( "update client_issue set ci_closed = NULL where ci_id = $ci_id" );

			if( (int)$this->ATTRIBUTES['SendEmail'] )
			{

				// send punter an email
				$to = '';
				if( !strlen( $existing['ci_verified_email'] ) )
				{
					$to = getField( "select us_email from users where us_id = {$existing['ci_us_id']}" );
					$from = "noreply@";
					$noteText = "Your support issue has been responded to.  Please go to your members login page to view it.\n\nhttp://www.acmerockets.com/Members\n\nThanks from the Team at AcmeRockets";
					if( strlen( $existing['ci_transaction_number'] ) )
						$subject = "Issue with order number {$existing['ci_transaction_number']} at ";
					else
						$subject = "Issue number $ci_id at ";
				}
				else
				{
					if( strlen( $existing['ci_token'] ) == 0 )
					{
						$token = sha1( "".rand().time() );
						query( "update client_issue set ci_token = '$token' where ci_id = $ci_id" );
					}
					else
						$token = $existing['ci_token'];

					$to = $existing['ci_verified_email'];
					//$from = "issues@";
					$from = "admin@";
					$subject = "[$token] Issue number $ci_id at ";
					$noteText = $this->ATTRIBUTES['Note'];
				}

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

	locationRelative($this->ATTRIBUTES['BackURL']);
?>
