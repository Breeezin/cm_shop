<?PHP
	ss_paramKey($webpay->webPayConfig , "wpc_card_details", "");
	$configDetails = unserialize($webpay->webPayConfig['wpc_card_details']);
	//ss_DumpVarDie($webpay);
	
	//ss_DumpVarDie($configDetails);
	/*
	$orderNo = serialize(array(		
			'tr_token'=>$webpay->payment['tr_token'],
			'BackURL'=>$webpay->ATTRIBUTES['BackURL'],
			)
		);
	$orderNo = str_replace("&",chr(5),$orderNo);
	$orderNo = ss_URLEncodedFormat($orderNo);
	*/
	$sessionInfo = $webpay->ATTRIBUTES['as_id']."US".$webpay->ATTRIBUTES['us_id'];
return <<< EOD

    <input TYPE="hidden" NAME="MerchantKey" VALUE="{$configDetails['PayProMarchantKey']}"> 
    <input TYPE="hidden" NAME="MerchantOrderNo" VALUE = "{$webpay->payment['tr_id']}"> 
    <input TYPE="hidden" NAME="Mode" VALUE="{$configDetails['PayProMode']}">
    <input TYPE="hidden" NAME="SessionInfo" VALUE="$sessionInfo">
    <input TYPE="hidden" NAME="PurchaseAmount" VALUE = "{$webpay->payment['tr_nzd_total_charged']}">   
    
<SCRIPT language="javascript">
	document.forms.PaymentForm.action="https://www.paypro.co.nz/https/pay.aspx";	
	document.PaymentForm.submit();
</SCRIPT>

EOD;
?>