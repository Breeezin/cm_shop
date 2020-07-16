<?PHP
	ss_paramKey($webpay->webPayConfig , "wpc_card_details", "");
	$configDetails = unserialize($webpay->webPayConfig['wpc_card_details']);

    //custom payments aren't actually users and didn't seem to be passing the backurl?..
    $webpay->param('us_id','-1');
    $backURL = isset($webpay->ATTRIBUTES['BackURL']) ? '&BackURL='.$webpay->ATTRIBUTES['BackURL'] : '';

	$sessionInfo = $webpay->ATTRIBUTES['as_id']."US".$webpay->ATTRIBUTES['us_id'];
	
	$args = array();
	$args['Title'] = $GLOBALS['cfg']['siteName'];
	$args['vpc_Version'] = '1';
	$args['vpc_Command'] = 'pay';
	$args['vpc_AccessCode'] = $configDetails['EGateAccessCode'];
	$args['vpc_MerchTxnRef'] = $webpay->ATTRIBUTES['tr_id'];
	$args['vpc_Merchant'] = $configDetails['EGateMarchantID'];
	$args['vpc_OrderInfo'] = $sessionInfo;
	$args['vpc_Amount'] = str_replace('.','',$webpay->payment['tr_nzd_total_charged']);
	$args['vpc_Locale'] = 'en';
	$args['vpc_TicketNo'] = '';
	$args['vpc_TxSourceSubType'] = '';
	$args['vpc_ReturnURL'] = "{$GLOBALS['cfg']['plaintext_server']}index.php?act=WebPay.ByCreditCard&DoAction=1&Paid=1{$backURL}";
/*	
	[Title] => PHP Content Manager Test 
	[vpc_AccessCode] => EC4472CC 
	[vpc_Amount] => 26325 
	[vpc_Command] => pay 
	[vpc_Locale] => en 
	[vpc_MerchTxnRef] => 488
	 [vpc_Merchant] => TESTANZFSLTD 
	 [vpc_OrderInfo] => 618US1 
	 [vpc_ReturnURL] => http://phpcm.im.co.nz/index.php?act=WebPay.ByCreditCard&DoAction=1&Paid=1 
	 [vpc_TicketNo] => 
	 [vpc_TxSourceSubType] => 
	 [vpc_Version] => 1
	*/
	ksort ($args);
		
	// set a parameter to show the first pair in the URL
	$appendAmp = 0;
	$md5HashData = $configDetails['EGateHashSecret'];
	$vpcURL = "https://migs.mastercard.com.au/vpcpay?";
	foreach($args as $key => $value) {
		 if (strlen($value) > 0) {
        
		    // this ensures the first paramter of the URL is preceded by the '?' char
		    if ($appendAmp == 0) {
		        $vpcURL .= urlencode($key) . '=' . urlencode($value);
		        $appendAmp = 1;
		    } else {
		        $vpcURL .= '&' . urlencode($key) . "=" . urlencode($value);
		    }
		    $md5HashData .= $value;
		    print $value."<BR>";
		}
	}
	//die(strtoupper(md5($md5HashData)));
	$vpcURL .= "&vpc_SecureHash=" . strtoupper(md5($md5HashData));

	location($vpcURL);
?>
