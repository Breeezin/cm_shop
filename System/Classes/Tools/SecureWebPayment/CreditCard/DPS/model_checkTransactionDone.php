<?php 

	ss_paramKey($webpay->webPayConfig , "wpc_card_details", "");
	
	$configDetails = unserialize($webpay->webPayConfig['wpc_card_details']);
	$UserName = $configDetails['DPSAccount'];
	$Password = $configDetails['DPSPassword'];
	
	$amount = $webpay->payment['tr_total'];
	$name = $webpay->ATTRIBUTES['TrCreditCardHolder'];
		
	$currency = $webpay->payment['cn_currency_code'];
	
	$ccnum = str_replace('-', '',trim($webpay->ATTRIBUTES['TrCreditCardNumber']));
	//$exDates = ArrayToList($webpay->ATTRIBUTES['TrCreditCardExpiry'], '/');
	$ccdate = str_replace("/20", '', $webpay->ATTRIBUTES['TrCreditCardExpiry']);
	
	$sentInfoKeep = '';
	
	$cmdDoTxnTransaction = "<Txn>";
	$cmdDoTxnTransaction .= "<PostUsername>$UserName</PostUsername>";
	$cmdDoTxnTransaction .= "<PostPassword>$Password</PostPassword>";
	$cmdDoTxnTransaction .= "<InputCurrency>$currency</InputCurrency>";
	$cmdDoTxnTransaction .= "<Amount>$amount</Amount>";
	$cmdDoTxnTransaction .= "<CardHolderName>$name</CardHolderName>";
	$sentInfoKeep = $cmdDoTxnTransaction;
	$cmdDoTxnTransaction .= "<CardNumber>$ccnum</CardNumber>";
	$sentInfoKeep .= "<CardNumber>xxxx xxxx xxxx xxxxx</CardNumber>";
	
	$cmdDoTxnTransaction .= "<DateExpiry>$ccdate</DateExpiry>";	
	$sentInfoKeep .= "<DateExpiry>$ccdate</DateExpiry>";	
	$cmdDoTxnTransaction .= "<TxnType>Purchase</TxnType>";
	$sentInfoKeep .= "<TxnType>Purchase</TxnType>";
	$cmdDoTxnTransaction .= "<MerchantReference>{$webpay->payment['tr_id']}</MerchantReference>";
	$sentInfoKeep .= "<MerchantReference>{$webpay->payment['tr_id']}</MerchantReference>";
	$cmdDoTxnTransaction .= "</Txn>";
	$sentInfoKeep .= "</Txn>";
	
	
	$this->sentInfo = $sentInfoKeep;
	
	//$URL = "www.payment.co.nz/pxpost.asp"; old url 
	$URL = "www.paymentexpress.com/pxpost.asp";
			
	//echo "\n\n\n\nSENT:\n$cmdDoTxnTransaction\n\n\n\n\n$";
		 
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL,"https://$URL");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$cmdDoTxnTransaction);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);	
	$result = curl_exec ($ch); 
	curl_close ($ch);
	$data = $result;	

	$xml_parser = xml_parser_create();
	xml_parse_into_struct($xml_parser, $data, $vals, $index);
	xml_parser_free($xml_parser);

	$params = array();
	$level = array();
	//ss_DumpVar($vals);
	foreach ($vals as $xml_elem) {
		if ($xml_elem['type'] == 'open') {
			if (array_key_exists('attributes',$xml_elem)) {
				list($level[$xml_elem['level']],$extra) = array_values($xml_elem['attributes']);
			} else {
				$level[$xml_elem['level']] = $xml_elem['tag'];
			}
		}
		if ($xml_elem['type'] == 'complete') {
		$start_level = 1;
		$php_stmt = 'ss_paramKey($xml_elem, \'value\', \'\');';
		$php_stmt .= '$params';
		while($start_level < $xml_elem['level']) {				
			$php_stmt .= '[$level['.$start_level.']]';
			$start_level++;
		}
					
		$php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
		//ss_DumpVar($php_stmt);
		eval($php_stmt);
		}
	}

	/* Uncommenting this block will display the entire array and show all values returned.
	echo "<pre>";
	print_r ($params);
	echo "</pre>";
	*/
	//ss_DumpVar($params);
	$success = $params['TXN']['SUCCESS'];
	ss_paramKey($params['TXN'][$success],'MERCHANTREFERENCE', '');
	ss_paramKey($params['TXN'][$success],'CARDHOLDERNAME', '');
	ss_paramKey($params['TXN'][$success],'AUTHCODE', '');
	ss_paramKey($params['TXN'][$success],'AMOUNT', '');
	ss_paramKey($params['TXN'][$success],'CURRENCYNAME', '');
	ss_paramKey($params['TXN'][$success],'TXNTYPE', '');
	ss_paramKey($params['TXN'][$success],'CARDNUMBER', '');
	ss_paramKey($params['TXN'][$success],'DATEEXPIRY', '');
	ss_paramKey($params['TXN'][$success],'CARDHOLDERRESPONSETEXT', '');
	ss_paramKey($params['TXN'][$success],'CARDHOLDERRESPONSEDESCRIPTION', '');
	ss_paramKey($params['TXN'][$success],'MERCHANTRESPONSETEXT', '');
	ss_paramKey($params['TXN'][$success],'DPSTXNREF', '');
	
	$MerchantReference				= $params['TXN'][$success]['MERCHANTREFERENCE'];
	$CardHolderName					= $params['TXN'][$success]['CARDHOLDERNAME'];
	$AuthCode						= $params['TXN'][$success]['AUTHCODE'];
	$Amount							= $params['TXN'][$success]['AMOUNT'];	
	$CurrencyName					= $params['TXN'][$success]['CURRENCYNAME'];
	$TxnType						= $params['TXN'][$success]['TXNTYPE'];
	$CardNumber						= $params['TXN'][$success]['CARDNUMBER'];
	$DateExpiry						= $params['TXN'][$success]['DATEEXPIRY'];
	$CardHolderResponseText			= $params['TXN'][$success]['CARDHOLDERRESPONSETEXT'];
	$CardHolderResponseDescription	= $params['TXN'][$success]['CARDHOLDERRESPONSEDESCRIPTION'];
	$MerchantResponseText			= $params['TXN'][$success]['MERCHANTRESPONSETEXT'];
	$DPSTxnRef						= $params['TXN'][$success]['DPSTXNREF'];
	
	$html = "<table align='center' width='500' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial, Helvetica, sans-serif'>";
	$html .= "<BR><hr><BR>";
	$html .= "<tr><td>Merchant Reference: </td><td>$MerchantReference</td></tr>";
	$html .= "<tr><td>CardHolderName: </td><td>$CardHolderName</td></tr>";
	$html .= "<tr><td>AuthCode: </td><td>$AuthCode</td></tr>";	
	$html .= "<tr><td>Amount: </td><td>$Amount</td></tr>";
	$html .= "<tr><td>CurrencyName: </td><td>$CurrencyName</td></tr>";
	$html .= "<tr><td>DateExpiry: </td><td>$DateExpiry</td></tr>";
	$html .= "<tr><td>CardHolderResponseText: </td><td>$CardHolderResponseText</td></tr>";
	$html .= "<tr><td>CardHolderResponseDescription: </td><td>$CardHolderResponseDescription</td></tr>";
	$html .= "<tr><td>MerchantResponseText: </td><td>$MerchantResponseText</td></tr>";
	$html .= "<tr><td>TxnType: </td><td>$TxnType</td></tr>";
	$html .= "<tr><td>DPSTxnRef: </td><td>$DPSTxnRef</td></tr>";
	$html .= "</table>";
	//$html .= "</body></html>";
	
	$this->responseHTML = $html;
	if ($success) {
		return 2;
	} else {
		return 3;
	}
	
	
			
	

	//ss_DumpVarDie($result);
	
?>