<?php

	global $bank, $tr_id, $link;
	$bank = 99;	// pg_id:payment_gateways

	define ('MID', 'acmee01001' );		// Shop.php:1242
	define ('SECURITY_KEY', 'f6z6nz0tywv8cegeqozn9qs1kkd5wljm');

	require( "../bank_interface/functions.php" );

    ss_log_message( "ACQRA_UNIONPAY _SERVER" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );
    ss_log_message( "ACQRA_UNIONPAY _GET" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_GET );
    ss_log_message( "ACQRA_UNIONPAY _POST" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_POST );

	$tr_id = (int)safe( $_POST['order_id'] );

	if( init( $bank, $tr_id ) )
	{
		$message = 'ACQRA_UNIONPAY:transaction_no:'.safe($_POST['transaction_no'] )."\n";
		$message .= 'ACQRA_UNIONPAY:status:'.safe($_POST['status'] )."\n";
		$message .= 'ACQRA_UNIONPAY:currency:'.safe($_POST['currency'] )."\n";
		$message .= 'ACQRA_UNIONPAY:amount:'.safe($_POST['amount'] )."\n";
		$message .= 'ACQRA_UNIONPAY:transaction_time:'.safe($_POST['transaction_time'] );
		$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$message', NOW(), {$order_details['or_id']} )";
		ss_log_message( "SQL:".$sql );
		mysqli_query( $link, $sql );

		if( $_POST['status'] == 1000 )
		{
	//		ipcheck( array( '58.64.198.72', '203.105.16' ), $bank, $tr_id );		// this is redirected over local network stack
			$message = 'ACQRA_UNIONPAY:settlement_ref:'.safe( $_POST['settlement_ref'] );
			$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$message', NOW(), {$order_details['or_id']} )";
			ss_log_message( "SQL:".$sql );
			mysqli_query( $link, $sql );

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
