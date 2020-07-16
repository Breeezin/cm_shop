<?php
// cybersource redirection page...

define ('HMAC_SHA256', 'sha256');
define ('PROFILE_ID', 'F0DUGYFS-SDFS-45E1-9761-2E3692756547' );
define ('ACCESS_KEY', '6e280ba17be42b123edc53c22b8d9261' );
define ('SECRET_KEY', 'c9be70855d8a4defa695cbefbc272773484d94992c4a0f21d8abd882409b642f7e82834cdf804be8ae3bff62393d89629d80ee2966ae41d1ba198b3911132a9130d9a76630374a0fb084fad32c8a7651ed51f6cd0b5f445c80b0bfd6823b3eb0b881e0b5e9024011b43fe5e66483bceef979b81548b94b7ebe6126cb03882b16');

function sign ($params) {
  return signData(buildDataToSign($params), SECRET_KEY);
}

function signData($data, $secretKey) {
    return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
}

function buildDataToSign($params) {
        $signedFieldNames = explode(",",$params["signed_field_names"]);
        foreach ($signedFieldNames as &$field) {
           $dataToSign[] = $field . "=" . $params[$field];
        }
        return commaSeparate($dataToSign);
}

function commaSeparate ($dataToSign) {
    return implode(",",$dataToSign);
}

if( getDefaultCurrencyCode() != $currency_handled )
{
        $_SESSION = NULL;
        die;
}

$ACK_URL = "https://www.acmerockets.com/$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "https://www.acmerockets.com/Members";
$POST_URL = "https://secureacceptance.cybersource.com/pay";		// live
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

$cn_two_code = getField( "select cn_two_code from countries where cn_name = '$b_country'");

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
    "access_key" => ACCESS_KEY,
    "profile_id" => PROFILE_ID,
    "transaction_uuid" => uniqid(),
    "signed_field_names" => "access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,bill_to_forename,bill_to_surname,bill_to_email,bill_to_address_country,bill_to_address_line1,bill_to_phone,bill_to_address_city,bill_to_address_postal_code,bill_to_address_state,merchant_descriptor_street,merchant_descriptor_contact",
    "unsigned_field_names" => "",
    "signed_date_time" => gmdate("Y-m-d\TH:i:s\Z"),
    "locale" => "en",
	"transaction_type" => "sale",
	"reference_number" =>  $this->ATTRIBUTES['tr_id'],
	"amount" => number_format($totalPrice, 2, '.', ''),
	"currency" => $currency_handled,
	"bill_to_forename" => substr($first_name, 0, 60),
	"bill_to_surname" => substr($last_name, 0, 60),
	"bill_to_email" => substr($email_address, 0, 60),
	"bill_to_address_country" => $cn_two_code,
	"bill_to_address_line1" => substr($billingAddress, 0, 60),
	"bill_to_phone" => substr($Phone, 0, 10),
	"bill_to_address_postal_code" => substr($Postal, 0, 10),
	"bill_to_address_city" => $City,
	"bill_to_address_state" => substr($b_state, 0, 60),
	"merchant_descriptor_street" => 'RM 907, Hong Kong Plaza, 188 Connaught Road West',
	"merchant_descriptor_contact" => '(852) 2581 0212',
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
//document.forms.TheForm.submit();
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
	echo "<input type=\"hidden\" id=\"signature\" name=\"signature\" value=\"" . sign($fields) . "\"/>\n";
?>
<CENTER>
<INPUT TYPE="submit" VALUE="Continue">
</CENTER>
</FORM>
</BODY>
</HTML>
