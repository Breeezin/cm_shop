<?php

	global $bank, $tr_id, $link;
	$bank = 99;	// pg_id:payment_gateways

	define ('MID', 'acmee0201002test' );
	define ('SECURITY_KEY', 'nmqv7cV0UIAfD4IcRAtAHGfBlLQi3hbe');
	define ('API_KEY', 'akY4cjN0dW40WFJiQnFnMTR3UzR6Q1FhR2V0ay9EdW1HVFZUN0c5bGJWZz0');

	require( "../bank_interface/functions.php" );

    ss_log_message( "ACQRA_V2 _SERVER" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );
    ss_log_message( "ACQRA_V2 _GET" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_GET );
    ss_log_message( "ACQRA_V2 _POST" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_POST );

	$dataToSign = [];
	foreach (['transaction_id', 'order_ref', 'status_code', 'status_message', 'amount', 'transaction_time', 'currency'] as $field)
	   $dataToSign[] = $_POST[$field];
	$dataToSign[] = SECURITY_KEY;

	$hashme = implode( ',', $dataToSign );
	$hash = hash('sha256', $hashme );
	ss_log_message( "hashed $hashme -> $hash" );

	if( $hash != $_POST['hash'] )
	{
		ss_log_message( "ERROR, hash doesn't match" );
		die;
	}

	$tr_id = (int)safe( $_POST['order_ref'] );

	if( init( $bank, $tr_id ) )
	{
		$message = 'ACQRA_V2:transaction_id:'.safe($_POST['transaction_id'] )."\n";
		$message .= 'ACQRA_V2:status_code:'.safe($_POST['status_code'] )."\n";
		$message .= 'ACQRA_V2:status_message:'.escape($_POST['status_message'] )."\n";
		$message .= 'ACQRA_V2:currency:'.safe($_POST['currency'] )."\n";
		$message .= 'ACQRA_V2:amount:'.safe($_POST['amount'] )."\n";
		$message .= 'ACQRA_V2:settlement_ref:'.safe($_POST['settlement_ref'] );
		$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$message', NOW(), {$order_details['or_id']} )";
		ss_log_message( "SQL:".$sql );
		mysqli_query( $link,  $sql );

		if( $_POST['status_code'] == 10000 )
		{
	//		ipcheck( array( '58.64.198.72', '203.105.16' ), $bank, $tr_id );		// this is redirected over local network stack
			$message = 'ACQRA_V2:settlement_ref:'.safe( $_POST['settlement_ref'] );
			$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$message', NOW(), {$order_details['or_id']} )";
			ss_log_message( "SQL:".$sql );
			mysqli_query( $link,  $sql );

			if( mark_paid( ) )
				done_ack( );
			else
				done_nak();
		}
		else
			done_nak();
	}
	else
		done_nak();

	die;
?>
