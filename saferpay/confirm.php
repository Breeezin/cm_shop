<?php

	global $bank, $tr_id, $link;
	$bank = 99;	// pg_id:payment_gateways

	$live = 'test';			// { 'test', 'www' }
	if( $live == 'test' )
	{
		$CustomerID = '240882';	// test
		$TerminalID = '17296830';
		$APIUser = 'API_2488082_9338056';
		$APIPasswd = 'JsonApiPwd1_iE6zwurJ';
	}
	else
	{
	}


	require( "../bank_interface/functions.php" );

    ss_log_message( "SAFERPAY _SERVER" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );
    ss_log_message( "SAFERPAY _GET" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_GET );
    ss_log_message( "SAFERPAY _POST" );
    ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_POST );

	$tr_id = (int)safe( $_GET['order_ref'] );
	$hash = urlencode(safe( $_GET['hash'] ));
	$hash2 = urlencode(hash('sha256', "foo$tr_id$APIPasswd" ));

	ss_log_message( "tr_id = $tr_id, hash = $hash, hash2 = $hash2" );

	if( !strncmp( $hash, $hash2, strlen( $hash2) ) )
	{
		// query status of payment
		/*
			POST: /Payment/v1/PaymentPage/Assert
			Request
			Arguments
			RequestHeader
			mandatory, container 	
			General information about the request.
			Token
			mandatory, string 	
			Token returned by initial call.
			Id[1..50]
			Example: 234uhfh78234hlasdfh8234e

			Example:

			{
			  "RequestHeader": {
				"SpecVersion": "1.10",
				"CustomerId": "[your customer id]",
				"RequestId": "[unique request identifier]",
				"RetryIndicator": 0
			  },
			  "Token": "234uhfh78234hlasdfh8234e"
			}
		*/		
		if( init( $bank, $tr_id ) )
		{

			$sql = "select * from saferpay_tokens where st_tr_id = $tr_id order by st_id desc";
			ss_log_message( "SQL:".$sql );
			if( $q = mysqli_query( $link, $sql ) )
				$c = mysqli_fetch_assoc( $q );
			else
			{
				ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
				ss_log_message( "Failed" );
				die;
			}

			if( $c['st_tr_id'] == $tr_id )
			{
				$token = $c['st_token'];

				ss_log_message( "retrieved token {$c['st_token']} for tx $tr_id" );

				$data = [
							'RequestHeader' =>
							[
								'SpecVersion' => '1.10',
								'CustomerId' => $CustomerID,
								'RequestId' => $tr_id,
								'RetryIndicator' => 0,
							],
							'Token' => $c['st_token'],
						];

				$assertURL = "https://$live.saferpay.com/api/Payment/v1/PaymentPage/Assert";
				ss_log_message( "AssertURL:$assertURL" );
				$data_string = json_encode($data);

				ss_log_message( $data_string );

				$ch = curl_init($assertURL);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Content-Length: ' . strlen($data_string)));
				curl_setopt($ch, CURLOPT_HEADER, 1);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($ch, CURLOPT_USERPWD, $APIUser . ":" . $APIPasswd);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				$result = curl_exec( $ch );
				curl_close($ch);

				if( $result !== false )
				{
					$status = 0;
					$response = NULL;

					$lines = explode("\n", $result );
					foreach( $lines as $line )
					{
						if( !strncmp( $line, 'HTTP/', 5 ) )
						{
							$fields = explode( ' ', $line );
							$status = $fields[1];
						}

						if( $line[0] == '{' )
							$response = json_decode( $line );
					}

					if( $response )
					{
						ss_log_message( "TX results OK, grabbing local data" );

						$note = print_r( $response, true );
						$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$note', NOW(), {$order_details['or_id']} )";
						ss_log_message( "SQL:".$sql );
						if( !mysqli_query( $link, $sql ) )
						{
							ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
							die;
						}

						// logic about discarding TX goes here

						/*
							{
							  "ResponseHeader": {
								"SpecVersion": "1.10",
								"RequestId": "[your request id]"
							  },
							  "Transaction": {
								"Type": "PAYMENT",
								"Status": "AUTHORIZED",
								"Id": "723n4MAjMdhjSAhAKEUdA8jtl9jb",
								"Date": "2015-01-30T12:45:22.258+01:00",
								"Amount": {
								  "Value": "100",
								  "CurrencyCode": "CHF"
								},
								"AcquirerName": "Saferpay Test Card",
								"AcquirerReference": "000000",
								"SixTransactionReference": "0:0:3:723n4MAjMdhjSAhAKEUdA8jtl9jb",
								"ApprovalCode": "012345"
							  },
							  "PaymentMeans": {
								"Brand": {
								  "PaymentMethod": "VISA",
								  "Name": "VISA Saferpay Test"
								},
								"DisplayText": "9123 45xx xxxx 1234",
								"Card": {
								  "MaskedNumber": "912345xxxxxx1234",
								  "ExpYear": 2015,
								  "ExpMonth": 9,
								  "HolderName": "Max Mustermann",
								  "CountryCode": "CH"
								}
							  },
							  "Liability": {
								"LiabilityShift": true,
								"LiableEntity": "ThreeDs",
								"ThreeDs": {
								  "Authenticated": true,
								  "LiabilityShift": true,
								  "Xid": "ARkvCgk5Y1t/BDFFXkUPGX9DUgs=",
								  "VerificationValue": "AAABBIIFmAAAAAAAAAAAAAAAAAA="
								},
								"FraudFree": {
								  "Id": "deab90a0458bdc9d9946f5ed1b36f6e8",
								  "LiabilityShift": false,
								  "Score": 0.6,
								  "InvestigationPoints": [
									"susp_bill_ad",
									"susp_machine"
								  ]
								}
							  }
							}
						*/
						if( $response->Transaction && $response->Transaction->Status )
						{
							$captured = false;

							if( $response->Transaction->Status == 'CAPTURED' )
								$captured = true;

							if( $response->Transaction->Status == 'AUTHORIZED' )
							{
								// capture payment
								/*
								POST: /Payment/v1/Transaction/Capture
								{
								  "RequestHeader": {
									"SpecVersion": "1.10",
									"CustomerId": "[your customer id]",
									"RequestId": "[unique request id]",
									"RetryIndicator": 0
								  },
								  "TransactionReference": {
									"TransactionId": "723n4MAjMdhjSAhAKEUdA8jtl9jb"
								  }
								}
								{"RequestHeader":{"SpecVersion":"1.10","CustomerId":"248082","RequestId":1764046,"RetryIndicator":0},"TransactionReference":{"TransactionId":"W9pI1Obr7EIdtAYME4bnA12C3W7b"}}
								*/
								
								$capture_data = [
											'RequestHeader' =>
											[
												'SpecVersion' => '1.10',
												'CustomerId' => $CustomerID,
												'RequestId' => $tr_id,
												'RetryIndicator' => 0,
											],
											'TransactionReference' => 
											[
												'TransactionId' => $response->Transaction->Id,
											],
										];

								$captureURL = "https://$live.saferpay.com/api/Payment/v1/Transaction/Capture";
								ss_log_message( "CaptureURL:$captureURL" );
								$capture_string = json_encode($capture_data);

								ss_log_message( $capture_string );

								$ch = curl_init($captureURL);
								curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Content-Length: ' . strlen($capture_string)));
								curl_setopt($ch, CURLOPT_HEADER, 1);
								curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
								curl_setopt($ch, CURLOPT_USERPWD, $APIUser . ":" . $APIPasswd);
								curl_setopt($ch, CURLOPT_TIMEOUT, 30);
								curl_setopt($ch, CURLOPT_POST, 1);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $capture_string);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
								$capture_result = curl_exec( $ch );
								curl_close($ch);

								if( $capture_result !== false )
								{
									$status = 0;
									$capture_response = NULL;

									$lines = explode("\n", $capture_result );
									foreach( $lines as $line )
									{
										if( !strncmp( $line, 'HTTP/', 5 ) )
										{
											$fields = explode( ' ', $line );
											$status = $fields[1];
										}

										if( $line[0] == '{' )
											$capture_response = json_decode( $line );
									}

									if( $capture_response && $capture_response->Status && ( $capture_response->Status == 'CAPTURED' ) )
									{
										$captured = true;

										$note = print_r( $capture_response, true );
										$sql = "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('$note', NOW(), {$order_details['or_id']} )";
										ss_log_message( "SQL:".$sql );

										if( !mysqli_query( $link, $sql ) )
											ss_log_message( mysqli_errno($link) . ": " . mysqli_error($link) );
									}
									else
									{
										ss_log_message( "capture response failed" );
										ss_log_message( $capture_result );
									}
								}
								else
										ss_log_message( "capture result failed" );
							}

							if( $captured )
							{	
								ss_log_message( "Marking $tr_id as paid not shipped" );

								if( mark_paid( ) )
									done_ack( );
								else
									done_nak();
							}
						}
						else
							ss_log_message( "TX Status not authorized" );
					}
					else
						ss_log_message( "curl returned nothing interesting:$result" );
				}
				else
					ss_log_message( "curl returned FALSE" );
			}
			else
				ss_log_message( "unexpected response" );
		}
		else
			ss_log_message( "init failed" );
	}

?>
