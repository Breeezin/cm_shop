<?php 

	ss_paramKey($webpay->webPayConfig , "wpc_card_details", "");
	
	$configDetails = unserialize($webpay->webPayConfig['wpc_card_details']);
	$UserName = ss_URLEncodedFormat($configDetails['ZipZapUsername']);
	$Password = $configDetails['ZipZapUsernameNZD'];

	
	$amount = ss_URLEncodedFormat($webpay->payment['tr_total']);
	$name = ss_URLEncodedFormat($webpay->ATTRIBUTES['TrCreditCardHolder']);
		
	$currency = ss_URLEncodedFormat($webpay->payment['cn_currency_code']);
	$ccnum = trim($webpay->ATTRIBUTES['TrCreditCardNumber']);
	$ccnum = str_replace(' ', '', $ccnum);
	$ccnum = ss_URLEncodedFormat(str_replace('-', '',$ccnum));
	//$exDates = ArrayToList($webpay->ATTRIBUTES['TrCreditCardExpiry'], '/');
	$ccdate = ss_URLEncodedFormat($webpay->ATTRIBUTES['ExpiryYear'].$webpay->ATTRIBUTES['ExpiryMonth']);
	$clientEmail = ss_URLEncodedFormat($webpay->payment['tr_client_email']);
	$failURL = ss_URLEncodedFormat("{$GLOBALS['cfg']['SecureSite']}/index.php?act=WebPay.ByCreditCard&DoAction=1&Paid=0");
	$successURL = ss_URLEncodedFormat("{$GLOBALS['cfg']['SecureSite']}/index.php?act=WebPay.ByCreditCard&DoAction=1&Paid=1");
		
	
	
	//$URL = "www.payment.co.nz/pxpost.asp"; old url 
	$URL = "https://secure.zipzap.biz/zipzap.php";
	$postfields = "rawmode=1&username=$UserName&txn_type=p&cardnum=$ccnum&expiry=$ccdate&amount=$amount";
	$postfields .= "&currency=$currency&email=$clientEmail";
	/*
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL,$URL);
	curl_setopt($ch, CURLOPT_POST, 1);	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$postfields);
	//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);	
	$result = curl_exec ($ch); 
	curl_close ($ch);
	$data = $result;	
	ss_DumpVar($result,  "$URL?$postfields", true);
	*/
	$this->sentInfo = str_replace($ccnum,'credit card number', "$URL?$postfields");
	
	$cmdDu = "wget --output-document=- -q \"$URL?$postfields\"";		
    $cm_result = exec($cmdDu);
	$cm_result = ListToArray($cm_result);
	//ss_DumpVar($cm_result,  "wget $URL?$postfields", true);
	
	ss_paramKey($cm_result,0, ''); // success | fail | error
	ss_paramKey($cm_result,1, ''); // transaction reference
	ss_paramKey($cm_result,2, ''); // transaction status code
	ss_paramKey($cm_result,3, ''); // transaction status text
	//ss_DumpVarDie($cm_result,  "wget $URL?$postfields", true);
	$html = "<table align='center' width='500' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial, Helvetica, sans-serif'>";
	$html .= "<BR><hr><BR>";
	$html .= "<tr><td>Amount: </td><td>\${$webpay->payment['tr_total']} {$webpay->payment['cn_currency_code']}</td></tr>";
	$html .= "<tr><td>Transaction result: </td><td>{$cm_result[0]}</td></tr>";
	$html .= "<tr><td>Transaction reference: </td><td>{$cm_result[1]}</td></tr>";
	$html .= "<tr><td>Transaction status code: </td><td>{$cm_result[2]}</td></tr>";
	$html .= "<tr><td>Transaction status text: </td><td>{$cm_result[3]}</td></tr>";		
	$html .= "</table>";
	//$html .= "</body></html>";
	
	
	
	$this->responseHTML = $html;
	if ($cm_result[0] == 'success') {
		return 2;
	} else {
		return 3;
	}
	
	
		
	

	//ss_DumpVarDie($result);
	
?>