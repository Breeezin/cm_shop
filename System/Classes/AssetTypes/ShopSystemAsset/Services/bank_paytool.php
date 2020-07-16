<?php
// paytool redirection page...

if( getDefaultCurrencyCode() != 'EUR' )
{
        $_SESSION = NULL;
        die;
}

$baseURL = "http://www.rubberbands.com";

$ACK_URL = "$baseURL/Shop_System/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "$baseURL/Product%20Export/ContactYourBank/Paytool";
$CANCEL_URL = "$baseURL/Members";
$POST_URL = "https://secure.paytool.de/ncol/prod/orderstandard.asp";
$merchant_code = '1505371';
$SECRETCODE = 'Y7YHh7hb8sa789faYUyg';

$sdetails = unserialize($Q_Order['or_shipping_details']);
$PostCode = htmlentities($sdetails['PurchaserDetails']['0_B4C0']);
$first_name = escape(rtrim(ltrim($sdetails['PurchaserDetails']['first_name'])));
$last_name = escape(rtrim(ltrim($sdetails['PurchaserDetails']['last_name'])));
$billingAddress = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A1'])));
$City = $sdetails['PurchaserDetails']['0_50A2'];
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
$cn_two_code = getField( "select cn_two_code from countries where cn_name = '$b_country'");

$Phone = $sdetails['PurchaserDetails']['0_B4C1'];
$email_address = $sdetails['PurchaserDetails']['Email'];
$pos = strpos( $email_address, ">" );
if( $pos )
	$email_address = substr( $email_address, $pos + 1 );
$pos = strrpos( $email_address, "<" );
if( $pos )
	$email_address = substr( $email_address, 0, $pos );


$bullshit = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$bl = strlen( $bullshit );
$crap = '';
for( $i = 0; $i < 3; $i++ )
	$crap .= $bullshit[rand(0,$bl-1)];

$Merchant_Amount=(int)($totalPrice*100);
$Merchant_Order = $crap.sprintf( "%08s", $this->ATTRIBUTES['tr_id']);
$Merchant_ProductDescription = "AcmeRockets Llamas";
$Merchant_Cardholder = "$first_name $last_name";

query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('Merchant Order reference now $Merchant_Order', NOW(), {$Q_Order['or_id']} )" );

//"OWNERADDRESS" => ,
//"OWNERTOWN" => 
$params = array( 
"ACCEPTURL" => $ACK_URL,
"AMOUNT" => $Merchant_Amount,
//"CANCELURL" => $CANCEL_URL,
//"CN" => $Merchant_Cardholder,
"CURRENCY" => "EUR",
//"DECLINEURL" => $NACK_URL,
//"EMAIL" => $email_address,
//"EXCEPTIONURL" => $NACK_URL,
//"LANGUAGE"  => "en_US",
"ORDERID" => $Merchant_Order ,
//"OWNERCTY" => $City,
//"OWNERTELNO" => $Phone,
//"OWNERZIP" => $PostCode,
"PSPID" => $merchant_code,
);

foreach( $params as $i => $v )
	if( !strlen( $v ) )
		unset( $params[$i] );

$sha_raw = '';
foreach( $params as $i => $v )
	$sha_raw .= "$i=$v$SECRETCODE";

$sha_str = strtoupper( sha1( $sha_raw ) );

?>
<HTML>
<HEAD>
</HEAD>
<SCRIPT language="javascript">

window.onload = function ()
{
document.forms.TheForm.submit();
}
</SCRIPT>
<BODY>
You are being redirected to our payment processor (Paytool).<br />
<br />
<FORM NAME="TheForm" id="TheForm" ACTION="<?=$POST_URL?>" METHOD="POST" ENCTYPE="application/xwww-form-urlencoded">
<!-- general parameters -->
<?php foreach ($params as $i=>$v ) { ?>
<input type="hidden" name="<?=$i?>" value="<?=$v?>">
<?php } ?>
<input type="hidden" name="SHASIGN" value="<?=$sha_str?>">

<INPUT TYPE="submit" VALUE="Continue">
</CENTER>
</FORM>
</BODY>
</HTML>
