<?php
// ceca redirection page...

$ACK_URL = "/$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "/Members";
$POST_URL = "https://www.klikandpay.com/paiement/check.pl";
$key = '61152422';

$ChargeCurrency = '978';		// euros
$MerchantID='1348239521';

$sdetails = unserialize($Q_Order['or_shipping_details']);
$first_name = escape(rtrim(ltrim($sdetails['PurchaserDetails']['first_name'])));
$last_name = escape(rtrim(ltrim($sdetails['PurchaserDetails']['last_name'])));
$billingAddress = escape(rtrim(ltrim($sdetails['PurchaserDetails']['0_50A1'])));
$City = $sdetails['PurchaserDetails']['0_50A2'];
$b_state_country = $sdetails['PurchaserDetails']['0_50A4'];
$pos = strpos( $b_state_country, "<BR>" );
if( $pos !== false )
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
ss_log_message( "Klikandpay PAYS field is $cn_two_code from $b_country order ".$this->ATTRIBUTES['tr_id'] );

$Postal = $sdetails['PurchaserDetails']['0_B4C0'];
$Phone = $sdetails['PurchaserDetails']['0_B4C1'];
$email_address = $sdetails['PurchaserDetails']['Email'];
$pos = strpos( $email_address, ">" );
if( $pos )
	$email_address = substr( $email_address, $pos + 1 );
$pos = strrpos( $email_address, "<" );
if( $pos )
	$email_address = substr( $email_address, 0, $pos );

$orderTotal = number_format( $totalPrice, 2, '.', '' );

?>
<HTML>
<HEAD>
<TITLE>Off to yet another payment gateway</TITLE>
</HEAD>
<SCRIPT language="javascript">

window.onload = function ()
{
	document.forms.TheForm.submit();
}
</SCRIPT>
<BODY>
<FORM NAME="TheForm" ACTION="<?=$POST_URL?>" METHOD="POST" ENCTYPE="application/xwww-form-urlencoded">
<INPUT NAME="ID" TYPE=hidden VALUE='<?=$MerchantID?>' />
<input type=hidden name="PRENOM" value='<?=$first_name?>' />
<input type=hidden name="NOM" value='<?=$last_name?>' />
<input type=hidden name="ADRESSE" value='<?=$billingAddress?>' />
<input type=hidden name="CODEPOSTAL" value='<?=$Postal?>' />
<input type=hidden name="VILLE" value='<?=$City?>' />
<input type=hidden name="PAYS" value='<?=$cn_two_code?>' />
<input type=hidden name="TEL" value='<?=$Phone?>' />
<input type=hidden name="EMAIL" value='<?=$email_address?>' />
<input type=hidden name="MONTANT" value='<?=$orderTotal?>'>
<input type=hidden name="RETOURVOK" value='<?=$ACK_URL?>'>
<input type=hidden name="RETOURVHS" value='<?=$NACK_URL?>'>
<INPUT NAME="RETOUR" TYPE=hidden VALUE='?tr_id=<?=$this->ATTRIBUTES['tr_id']?>'>
<CENTER>
<INPUT TYPE="submit" VALUE="Continue">
</CENTER>
</FORM>
</BODY>
</HTML>
