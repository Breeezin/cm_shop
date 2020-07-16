<?php
// payment gateway redirection page...

function generateinpayCheckSum($params, $secret_key) 
{
	return md5(http_build_query(
		array("merchant_id"=>$params["merchant_id"],
			"order_id"=>$params["order_id"],
			"amount"=>$params["amount"],
			"currency"=>$params["currency"],
			"order_text"=>$params["order_text"],
			"flow_layout"=>$params["flow_layout"],
			"secret_key"=>$secret_key), null, "&"));
}


$ACK_URL = "/$assetPath/Service/Completed/tr_id/{$this->ATTRIBUTES['tr_id']}/tr_token/{$this->ATTRIBUTES['tr_token']}/us_id/$usID";
$NACK_URL = "/Members";

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

$orderTotal = number_format( $totalPrice, 2, '.', '' );


$arr['order_id'] = $this->ATTRIBUTES['tr_id'];
$arr['merchant_id'] = 24;
$arr["your_secret_key"] = 'j627w3D3';
$arr['amount'] = $orderTotal;
$arr['currency'] = 'EUR';
$arr['order_text'] = 'pe order '.$this->ATTRIBUTES['tr_id'];
$arr['flow_layout'] = 'multi_page';
$arr['buyer_email'] = $email_address;
$arr['return_url'] = "http://www.acmerockets.com/Shop_System/Service/Completed/tr_id/{$row['tr_id']}/tr_token/{$row['tr_token']}/us_id/{$row['or_us_id']}";
$arr['pending_url'] = "http://www.acmerockets.com/Members";
$arr['cancel_url'] = "http://www.acmerockets.com/Members";
//$arr['country'] = $cn_two_code;
$arr['invoice_comment'] = 'pe order '.$this->ATTRIBUTES['tr_id'];
$arr['buyer_name'] = $first_name. " ".$last_name;
$arr['buyer_address'] = $billingAddress.', '.$Postal.', '.$City.', '.$b_state.', '.$b_country;

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

<form name='TheForm' method="post" action="https://secure.inpay.com">
<input type="hidden" name="order_id" value="<?=$arr['order_id'];?>" />
<input type="hidden" name="merchant_id" value="<?=$arr['merchant_id'];?>" />
<input type="hidden" name="amount" value="<?=$arr['amount'];?>" />
<input type="hidden" name="currency" value="<?=$arr['currency'];?>" />
<input type="hidden" name="order_text" value="<?=$arr['order_text'];?>" />
<input type="hidden" name="flow_layout" value="<?=$arr['flow_layout'];?>" />
<input type="hidden" name="buyer_email" value="<?=$arr['buyer_email'];?>" />
<input type="hidden" name="checksum" value="<?=generateinpayCheckSum($arr, $arr["your_secret_key"]);?>" />
<!-- ********************* optional parameters ********************* -->
<input type="hidden" name="return_url" value="<?=$arr['return_url'];?>" />
<input type="hidden" name="pending_url" value="<?=$arr['pending_url'];?>" />
<input type="hidden" name="cancel_url" value="<?=$arr['cancel_url'];?>" />
<input type="hidden" name="invoice_comment" value="<?=$arr['invoice_comment'];?>" />
<input type="hidden" name="buyer_name" value="<?=$arr['buyer_name'];?>" />
<input type="hidden" name="buyer_address" value="<?=$arr['buyer_address'];?>" />
<input type="submit" value="Pay with inpay" />

</FORM>
</BODY>
</HTML>
