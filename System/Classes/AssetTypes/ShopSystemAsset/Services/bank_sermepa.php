<?php
// sermepa redirection page...


if( getDefaultCurrencyCode() != 'EUR' )
{
        $_SESSION = NULL;
        die;
}



$ACK_URL = "http://www.acmerockets.com/$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "http://www.acmerockets.com/Members";
$POST_URL = "https://sis.sermepa.es/sis/realizarPago";		// live
$merchant_code = '063924617';

$sdetails = unserialize($Q_Order['or_shipping_details']);
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

$Postal = $sdetails['PurchaserDetails']['0_B4C0'];
$Phone = $sdetails['PurchaserDetails']['0_B4C1'];
$email_address = $sdetails['PurchaserDetails']['Email'];
$pos = strpos( $email_address, ">" );
if( $pos )
	$email_address = substr( $email_address, $pos + 1 );
$pos = strrpos( $email_address, "<" );
if( $pos )
	$email_address = substr( $email_address, 0, $pos );

$SECRETCODE = "jhot645iu543gkrh487h";

$bullshit = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabscdefghijklmnopqrstuvwxyz';
$bl = strlen( $bullshit );
$crap = '';
for( $i = 0; $i < 4; $i++ )
	$crap .= $bullshit[rand(0,$bl-1)];

$Ds_Merchant_Amount=(int)($totalPrice*100);
$Ds_Merchant_Currency = '978';		// euros 
$Ds_Merchant_Order = sprintf( "%08s", $this->ATTRIBUTES['tr_id']).$crap;
$Ds_Merchant_ProductDescription = "AcmeRockets Llamas";
$Ds_Merchant_Cardholder = "$first_name $last_name";
$Ds_Merchant_MerchantCode = $merchant_code;
$Ds_Merchant_MerchantURL = "http://www.acmerockets.com/sermepa/confirm.php";
$Ds_Merchant_UrlOK = $ACK_URL;
$Ds_Merchant_UrlKO = $NACK_URL;
$Ds_Merchant_MerchantName = "acmerockets";
$Ds_Merchant_ConsumerLanguage = "002";
$Ds_Merchant_Terminal = "001";
$Ds_Merchant_TransactionType = 0;
$Ds_Merchant_MerchantSignature = strtoupper( SHA1($Ds_Merchant_Amount . $Ds_Merchant_Order .$Ds_Merchant_MerchantCode . $Ds_Merchant_Currency . $Ds_Merchant_TransactionType . $Ds_Merchant_MerchantURL . $SECRETCODE ) );

query( "insert into shopsystem_order_notes (orn_text, orn_timestamp, orn_or_id) values ('Merchant Order reference now $Ds_Merchant_Order', NOW(), {$Q_Order['or_id']} )" );

/*
 

*/
?>
<HTML>
<HEAD>
<TITLE>P&aacute;gina de pago</TITLE>
</HEAD>
<SCRIPT language="javascript">

window.onload = function ()
{
document.forms.TheForm.submit();
}
</SCRIPT>
<BODY>
You are being redirected to our payment processor (Sermepa).<br />
<br />
<br />
<FORM NAME="TheForm" ACTION="<?=$POST_URL?>" METHOD="POST" ENCTYPE="application/xwww-form-urlencoded">
<INPUT NAME="Ds_Merchant_Amount" TYPE=hidden VALUE='<?=$Ds_Merchant_Amount?>'>
<INPUT NAME="Ds_Merchant_Currency " TYPE=hidden VALUE='<?=$Ds_Merchant_Currency ?>'>
<INPUT NAME="Ds_Merchant_Order" TYPE=hidden VALUE='<?=$Ds_Merchant_Order ?>'>
<INPUT NAME="Ds_Merchant_ProductDescription" TYPE=hidden VALUE='<?=$Ds_Merchant_ProductDescription ?>'>
<INPUT NAME="Ds_Merchant_Cardholder" TYPE=hidden VALUE='<?=$Ds_Merchant_Cardholder ?>'>
<INPUT NAME="Ds_Merchant_MerchantCode" TYPE=hidden VALUE='<?=$Ds_Merchant_MerchantCode ?>'>
<INPUT NAME="Ds_Merchant_MerchantURL" TYPE=hidden VALUE='<?=$Ds_Merchant_MerchantURL ?>'>
<INPUT NAME="Ds_Merchant_UrlOK" TYPE=hidden VALUE='<?=$Ds_Merchant_UrlOK ?>'>
<INPUT NAME="Ds_Merchant_UrlKO" TYPE=hidden VALUE='<?=$Ds_Merchant_UrlKO ?>'>
<INPUT NAME="Ds_Merchant_MerchantName" TYPE=hidden VALUE='<?=$Ds_Merchant_MerchantName ?>'>
<INPUT NAME="Ds_Merchant_ConsumerLanguage" TYPE=hidden VALUE='<?=$Ds_Merchant_ConsumerLanguage ?>'>
<INPUT NAME="Ds_Merchant_Terminal" TYPE=hidden VALUE='<?=$Ds_Merchant_Terminal ?>'>
<INPUT NAME="Ds_Merchant_TransactionType" TYPE=hidden VALUE='<?=$Ds_Merchant_TransactionType ?>'>
<INPUT NAME="Ds_Merchant_MerchantSignature" TYPE=hidden VALUE='<?=$Ds_Merchant_MerchantSignature ?>'>
<CENTER>
<INPUT TYPE="submit" VALUE="Continue">
</CENTER>
</FORM>
</BODY>
</HTML>
