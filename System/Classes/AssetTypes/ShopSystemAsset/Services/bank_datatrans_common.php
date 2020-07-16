<?php
// cybersource redirection page...

$testmode = 'test';

define ('HMAC_SHA256', 'sha256');
define ('MERCHANT_ID', '6100118204' );
define ('SECRET_KEY', '45b180208c17bac79201e434cf3bab824c11bd2443cf78eff129144b4e23526f7b4e3fcd3d79a319e072480b1b87f90b7fa940a7622f1319e0f15f4b5404df5d');

function sign ($params) {
  return signData(buildDataToSign($params), SECRET_KEY);
}



// return hash_hmac('md5', $hmac_data, pack('H*', $hmac_key));
//     return DatatransHelper::generateSign($key, $plugin_definition['merchant_id'], $post_data['uppTransactionId'], $post_data['amount'], $post_data['currency']);


function signData($data, $secretKey) {
    ss_log_message( "hash_hmac('sha256', $data,  pack( 'H*', $secretKey ) )" );
    $hash = hash_hmac('sha256', $data, pack( 'H*', $secretKey ) );
	ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $hash );
    return $hash;
}

function buildDataToSign($params, $fields = ['merchantId', 'amount', 'currency', 'refno'] ) {
		$data = '';
        foreach ($fields as &$field) {
           $data .= $params[$field];
        }
		ss_log_message( "hashing $data" );
        return $data;
}

if( getDefaultCurrencyCode() != $currency_handled )
{
        $_SESSION = NULL;
        die;
}

$ACK_URL = "https://$testmode.acmerockets.com/$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "https://$testmode.acmerockets.com/Members";
$POST_URL = 'https://pay.';
if( $testmode == 'test' )
	$POST_URL .= 'sandbox.';
$POST_URL .= 'datatrans.com/upp/jsp/upStart.jsp';		// live
$sdetails = unserialize($Q_Order['or_shipping_details']);
$first_name = utf8_encode(escape(trim($sdetails['PurchaserDetails']['first_name'])));
$last_name = utf8_encode(escape(trim($sdetails['PurchaserDetails']['last_name'])));
$billingAddress = utf8_encode(escape(trim($sdetails['PurchaserDetails']['0_50A1'])));
$City = utf8_encode($sdetails['PurchaserDetails']['0_50A2']);
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

$b_state = utf8_encode(escape(trim($b_state) ) );

$cn_three_code = getField( "select cn_three_code from countries where cn_name = '$b_country'");

$Postal = utf8_encode($sdetails['PurchaserDetails']['0_B4C0']);
$Phone = utf8_encode($sdetails['PurchaserDetails']['0_B4C1']);
$email_address = utf8_encode($sdetails['PurchaserDetails']['Email']);
$pos = strpos( $email_address, ">" );
if( $pos )
	$email_address = substr( $email_address, $pos + 1 );
$pos = strrpos( $email_address, "<" );
if( $pos )
	$email_address = substr( $email_address, 0, $pos );



$fields = array( 
    "merchantId" => MERCHANT_ID,
    "language" => "en",
	"refno" =>  $this->ATTRIBUTES['tr_id'],
	"amount" => number_format($totalPrice*100, 0, '', ''),
	"currency" => $currency_handled,
	"uppCustomerFirstName" => substr($first_name, 0, 60),
	"uppCustomerLastName" => substr($last_name, 0, 60),
	"uppCustomerEmail" => substr($email_address, 0, 60),
	"uppCustomerCountry" => $cn_three_code,
	"successUrl" => $ACK_URL,
	"uppStatus3D" => 'return',
//	"paymentmethod" => 'AMX',
	);

ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $fields );


// query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('Merchant Order reference now $Ds_Merchant_Order', NOW(), {$Q_Order['or_id']} )" );

?>
<HTML>
<HEAD>
<TITLE>Off to the payment processor</TITLE>
</HEAD>
<SCRIPT language="javascript">

window.onload = function ()
{
document.forms.TheForm.submit();
}
</SCRIPT>
<BODY>
You are being redirected to our payment processor.<br />
<br />
<br />
<FORM NAME="TheForm" ACTION="<?=$POST_URL?>" METHOD="POST">
<?php
	foreach( $fields as $name => $value)
		echo "<input type=\"hidden\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"/>\n";
	echo "<input type=\"hidden\" id=\"sign\" name=\"sign\" value=\"" . sign($fields) . "\"/>\n";
?>
<CENTER>
<INPUT TYPE="submit" VALUE="Continue">
</CENTER>
</FORM>
</BODY>
</HTML>
