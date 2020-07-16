<?php

	global $bank, $tr_id, $link;
	$bank = 99;	// pg_id:payment_gateways

	define ('MID', '1821000104' );
	define ('SECURITY_KEY', 'eMbVGfzIvSko4clc5eamoFFiqdCpkRd5');
	define ('API_KEY', 'R3F2OUJkTWxERkZnQW93VIeiY0xzQkpYSFRLd2o1R3ZqUXZVVHdHMzZRWT0');

	require( "../bank_interface/functions.php" );

    ss_log_message( "DATATRANS _SERVER" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );
    ss_log_message( "DATATRANS _GET" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_GET );
    ss_log_message( "DATATRANS _POST" );
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
	//	die;
	}

	$tr_id = (int)safe( $_POST['refno'] );

	if( init( $bank, $tr_id ) )
	{
		$message = 'DATATRANS:uppTransactionId:'.safe($_POST['uppTransactionId'] )."\n";
		$message .= 'DATATRANS:responseCode:'.safe($_POST['responseCode'] )."\n";
		$message .= 'DATATRANS:status:'.escape($_POST['status'] )."\n";
		$message .= 'DATATRANS:responseMessage:'.escape($_POST['responseMessage'] )."\n";
		$message .= 'DATATRANS:currency:'.safe($_POST['currency'] )."\n";
		$message .= 'DATATRANS:amount:'.safe($_POST['amount'] )."\n";
		$message .= 'DATATRANS:acqAuthorizationCode:'.safe($_POST['acqAuthorizationCode'] );
		$message .= 'DATATRANS:authorizationCode:'.safe($_POST['authorizationCode'] );
		$message .= 'DATATRANS:pmethod:'.safe($_POST['pmethod'] );
		$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$message', NOW(), {$order_details['or_id']} )";
		ss_log_message( "SQL:".$sql );
		mysqli_query( $link,  $sql );

		switch ( $_POST['uppStatus3D'] )
		{
		case 'Y':
			$message = '3D full authentication';
			break;
		case 'D':
			$message = 'merchant has a 3D contract, but card holder is not enrolled';
			break;
		case 'A':
			$message = '3D activation during shopping';
			break;
		case 'U':
			$message = '3D no liability shift';
			break;
		case 'N':
			$message = '3D authentication failed';
			break;
		case 'C':
			$message = '3D authentication uncomplete';
			break;
		default:
			$message = 'TRX not 3D';
		}
		$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$message', NOW(), {$order_details['or_id']} )";
		ss_log_message( "SQL:".$sql );
		mysqli_query( $link,  $sql );

		if( $_POST['responseCode'] == '01' )
		{
	//		ipcheck( array( '58.64.198.72', '203.105.16' ), $bank, $tr_id );		// this is redirected over local network stack
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
