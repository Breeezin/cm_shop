<?php

// redirection page...

// TEST NOT LIVE
$live = 'test';
$secureSite = "https://$live.acmerockets.com/";

$ACK_URL = "{$secureSite}Shop_System/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "{$secureSite}Members";


$CustomerID = '284802';	// test
$TerminalID = '17239680';
$APIUser = 'API_284802_90853386';
$APIPasswd = 'JsonApiPwd1_i897stsJ';

$reference = $this->ATTRIBUTES['tr_id'];
$hash = urlencode(hash('sha256', "foo$reference$APIPasswd" ));

$NOTIFY_URL = "{$secureSite}saferpay/confirm.php?order_ref=$reference&hash=$hash";
$language = 'en';
$Currency = 'CHF';

$orderTotal = (int)( 100 * $totalPrice );

$data = [	'RequestHeader' => 
				[ 
				'SpecVersion' => '1.10',
				'CustomerId' => $CustomerID,
				'RequestId' => $reference,
				'RetryIndicator' => 0
				],
			'TerminalId' => $TerminalID,
			'Payment' =>
				[
				'Amount' => 
					[
					'Value' => $orderTotal,
					'CurrencyCode' => $Currency,
					],
				'OrderId' => $reference,
				'Description' => 'Payment for goods',
				],
			'ReturnUrls' =>
				[
				'Success' => $ACK_URL,
				'Fail' => $NACK_URL,
				],
			'Notification' =>
				[
				'NotifyUrl' => $NOTIFY_URL,
				],
		];
/*
URL 	https://test.saferpay.com/BO/

JSON API
URL 	https://test.saferpay.com/api/

{
  "RequestHeader": {
    "SpecVersion": "1.10",
    "CustomerId": "[your customer id]",
    "RequestId": "[unique request identifier]",
    "RetryIndicator": 0
  },
  "TerminalId": "[your terminal id]",
  "Payment": {
    "Amount": {
      "Value": "100",
      "CurrencyCode": "CHF"
    },
    "OrderId": "Id of the order",
    "Description": "Description of payment"
  },
  "ReturnUrls": {
    "Success": "[your shop payment success url]",
    "Fail": "[your shop payment fail url]"
  }
}

*/

$payiniturl = "https://$live.saferpay.com/api/Payment/v1/PaymentPage/Initialize";

// submit this and forward browser onto result if OK.
ss_log_message( "PayInitURL:$payiniturl" );
//$forward_to = file_get_contents( $payiniturl );
$data_string = json_encode($data);

ss_log_message( $data_string );

$ch = curl_init($payiniturl);
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

	if( $status == 200 && $response )
	{
		// good to go
		// record the token and expiration
		ss_log_message( "Forward to ".$response->RedirectUrl );
		$token = safe( $response->Token );
		$expires = safe( $response->Expiration );
		query( "insert into saferpay_tokens (st_tr_id, st_token, st_expires) values ($reference, '$token', '$expires' )" );
		header( 'Location:'.$response->RedirectUrl );
	}
	else
		ss_log_message( "Unexpected : $lines" );

}
else
	ss_log_message( "Curl returned false" );
