<?php
// redirection page...

$ACK_URL = "{$normalSite}$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "{$normalSite}Members";
$FAIL_URL = "{$normalSite}/Cigar_Export/ContactYourBank/WingHang";

$reference = $this->ATTRIBUTES['tr_id'];
$MerchantID='193217';

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


?>
<HTML>
<HEAD>
<TITLE>Off to the payment processor we go...</TITLE>
</HEAD>
<SCRIPT language="javascript">

window.onload = function ()
{
	document.forms.payFormCcard.submit();
}
</SCRIPT>
<BODY>
<br />

<form name="payFormCcard" method="post" action="https://www.paydollar.com/winghang/eng/payment/payForm.jsp">
<input type="hidden" name="merchantId" value="<?=$MerchantID?>">
<input type="hidden" name="amount" value="<?=$totalPrice?>" >
<input type="hidden" name="orderRef" value="<?=$reference?>">
<input type="hidden" name="currCode" value="840" >
<input type="hidden" name="mpsMode" value="NIL" >
<input type="hidden" name="successUrl" value="<?=$ACK_URL?>">
<input type="hidden" name="failUrl" value="<?=$FAIL_URL?>">
<input type="hidden" name="cancelUrl" value="<?=$NACK_URL?>">
<input type="hidden" name="payType" value="N">
<input type="hidden" name="lang" value="E">
<input type="hidden" name="payMethod" value="CC">

<CENTER>
<INPUT TYPE="submit" VALUE="Continue">
</CENTER>
</FORM>

</BODY>
</HTML>
