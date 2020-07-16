<?php

// redirection page...

// TEST NOT LIVE
//$normalSite = 'http://test.acmerockets.com/';

$ACK_URL = "{$normalSite}Shop_System/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "{$normalSite}Members";

$NOTIFY_URL = "{$normalSite}bands/confirm.php";

$reference = $this->ATTRIBUTES['tr_id'];
$MerchantID='651650-17828111'; // LIVE
//$MerchantID='99867-94913159'; // NOT LIVE
$language = 'en';
$Currency = 'CHF';

$orderTotal = (int)( 101 * $totalPrice * ss_getExchangeRate( 'EUR', $Currency ) );

$data = array( "ACCOUNTID" => $MerchantID,
//				"spPassword" => "XAjc3Kna",		// remove when LIVE
				"AMOUNT" => $orderTotal,
				"CURRENCY" => $Currency,
				"DESCRIPTION" => "Order$reference",
				"ORDERID" => $reference,
				"SUCCESSLINK" => $ACK_URL,
				"FAILLINK" => $NACK_URL,
				"BACKLINK" => $NACK_URL,
				"NOTIFYURL" => $NOTIFY_URL);

$payiniturl = "https://www.saferpay.com/hosting/CreatePayInit.asp?".http_build_query($data);

// submit this and forward browser onto result if OK.
ss_log_message( "PayInitURL:$payiniturl" );
//$forward_to = file_get_contents( $payiniturl );

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $payiniturl );
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0");
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$forward_to = curl_exec( $ch );
$res = curl_getinfo($ch);
curl_close($ch);

ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $res );
ss_log_message( "Location:$forward_to" );

if( $res['http_code'] == 200 )
	header( "Location:$forward_to" );
else
{
	print_r( $res );
}
?>
