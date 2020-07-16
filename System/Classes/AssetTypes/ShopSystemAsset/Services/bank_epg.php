<?php
include 'security_epg.php'; 	 	

$ACK_URL = urlencode("{$normalSite}Shop_System/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID");
$NACK_URL = urlencode("{$normalSite}Acme_Rockets/ContactYourBank/21fifty");

$reference = $this->ATTRIBUTES['tr_id'];
$language = 'en';

$sdetails = unserialize($Q_Order['or_shipping_details']);
$first_name = utf8_encode(escape(rtrim(ltrim($sdetails['PurchaserDetails']['first_name']))));
$last_name = utf8_encode(escape(rtrim(ltrim($sdetails['PurchaserDetails']['last_name']))));
$billingAddress = utf8_encode(urlencode(escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A1'])))));
$City = utf8_encode(urlencode($sdetails['PurchaserDetails']['0_50A2']));
$b_state_country = ' '.$sdetails['PurchaserDetails']['0_50A4'];
$pos = strpos( $b_state_country, "<BR>" );
if( $pos )
{
	$b_state = substr( $b_state_country, 0, $pos );
	$b_country = substr( $b_state_country, $pos + 4 );
}
else
{
	$b_state = $b_state_country;
	$b_country = $b_state_country;
}

$Postal = utf8_encode(urlencode($sdetails['PurchaserDetails']['0_B4C0']));
$Phone = urlencode($sdetails['PurchaserDetails']['0_B4C1']);
$email_address = utf8_encode($sdetails['PurchaserDetails']['Email']);
$pos = strpos( $email_address, ">" );
if( $pos )
	$email_address = substr( $email_address, $pos + 1 );
$pos = strrpos( $email_address, "<" );
if( $pos )
	$email_address = substr( $email_address, 0, $pos );
$email_address = urlencode($email_address);

$cn_two_code = getField( "select cn_two_code from countries where cn_name = '$b_country'");


/*
- amount: Decimal amount i.e. ‘25.50’
- currency: 3 letters ISO code
- country: customer´s country
- customerId: customer´s account number or reference in your system
- merchantTransactionId : transactionId in your system
- language : desired 2 letters code to represent content in:
en, //        English
              fr, //        French
              es, //        Spanish
              no, //        Norwegian
              bg, //        Bulgarian
              da, //        Danish
              pt, //        Portuguese
              it, //        Italian
              nl, //        Dutch
              el, //        Greek
              fi, //        Finnish
              pl, //        Polish
              lv, //        Latvian
              sv; //        Swedish
 
-topLogo: url of the logo you want to appear in the pages the customer will see
-successUrl:
-errorUrl:
-cancelledUrl:
-statusUrl: (see point 3)
-merchantId: 1001 (this will identify you in our database)
-paymentSolution= swiftvoucher
-test=1
-merchantPassword: the password used for encrypting and decrypting (I will need you to send me in a separate email a passphrase that I will stored it md5 hashed in database)
*/

	$parameters = "amount=$totalPrice";
	$parameters .= "&currency={$chargeCurrency['CurrencyCode']}";
	$parameters .= "&country=$cn_two_code";

	$parameters .= "&firstName=$first_name";
	$parameters .= "&lastName=$last_name";
	$parameters .= "&address1=$billingAddress";
	$parameters .= "&city=$City";
	$parameters .= "&postCode=$Postal";
	$parameters .= "&zipCode=$Postal";
	$parameters .= "&customerCountry=$b_country";
	$parameters .= "&customerEmail=$email_address";
	$parameters .= "&telephone=$Phone";

	$parameters .= "&chFirstName=$first_name";
	$parameters .= "&chLastName=$last_name";
	$parameters .= "&chAddress1=$billingAddress";
	$parameters .= "&chCity=$City";
	$parameters .= "&chPostCode=$Postal";
	$parameters .= "&chState=$b_state";
	$parameters .= "&chCountry=$cn_two_code";
	$parameters .= "&chEmail=$email_address";
	$parameters .= "&description=acmeexpress_order";

	$parameters .= "&customerId=$usID";
	$parameters .= "&merchantId=1141";
	$parameters .= "&productId=11411";
	$parameters .= "&merchantTransactionId=$reference";
	$parameters .= "&language=$language";
	$parameters .= '&topLogo=https://www.acmerockets.com/images/acmerockets.png';
	$parameters .= "&successURL=$ACK_URL";
	$parameters .= "&errorURL=$NACK_URL";
	//$parameters .= '&statusURL='.urlencode('https://www.acmerockets.com/epg/confirm.php');
	$parameters .= '&statusURL='.urlencode('https://www.acmerockets.com/epg/confirm.php');
	$parameters .= "&paymentSolution=$paymentSolution";		// from calling script
	//$parameters .= '&paymentSolution=AloGateway';
//	if( $testing )
		$parameters .= '&test=0';
//	else
//		$parameters .= '&test=0';



$value = $parameters; 	

ss_log_message( "EPG PARAMETERS $parameters" );

//$key = "myPassWord1234"; //16 Character Key 	 	
//$md5Key = hash('md5', $key); 	
$md5Key = '5fbca05b34adc5a317ba5d132b24eff2';
//$md5Key = 'cf84784cb2b361b45cebfa0205e7a015';
$sha256ParamsIntegrityCheck = hash('sha256', $value); 	 	
$encryptedValue = SecurityEPG::encrypt($value, $md5Key); 	 	

//URL ENCODED IS NEEDED BEFORE POSTING 	
$encoded = urlencode($encryptedValue); 	  	
$url = 'https://checkout.easypaymentgateway.com/EPGCheckout/rest/online/tokenize'; 	// 'https://staging.easypaymentgateway.com/EPGCheckout/rest/online/tokenize';
$myvars = 'merchantId=1141&encrypted=' . $encoded . '&integrityCheck=' . $sha256ParamsIntegrityCheck;  

ss_log_message( "EPG POSTING $myvars" );

$ch = curl_init( $url ); 	
curl_setopt( $ch, CURLOPT_POST, 1); 	
curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars); 	
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1); 	
curl_setopt( $ch, CURLOPT_HEADER, 0); 	
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);  	
$response = curl_exec( $ch );

?> 	 	

<HTML> 	 	
<HEAD> 	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 	
<TITLE>Test </TITLE> 	 	
<script language=javascript> 	
function redirect(){ 		
var EPGResponse = "<?= $response ?>" 		
if (EPGResponse == "error") { 			
//something went wrong when calling EPG. Deal with the error 			alert('ERROR'); 		
} else { 	  		
window.location = EPGResponse; 	  	
} 	} 	
</script>  	
</HEAD> 	
<body onLoad="redirect()"> 		 	
</body> 	</HTML>
