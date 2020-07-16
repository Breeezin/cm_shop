<?PHP 
    // grab all credit card details
	ss_paramKey($webpay->webPayConfig , "wpc_card_details", "");
	$configDetails = unserialize($webpay->webPayConfig['wpc_card_details']);
    //custom payments aren't actually users..
    $webpay->param('us_id','-1');
	$sessionInfo = $webpay->ATTRIBUTES['as_id']."US".$webpay->ATTRIBUTES['us_id'];

    // right, whats wanted
    $args = array();
	$args['sitename'] = $GLOBALS['cfg']['siteName'];
	$args['pi'] = $configDetails['PaystationMarchantKey'];
	$args['ms'] = $webpay->ATTRIBUTES['tr_id'];
	$args['am'] = str_replace('.','',$webpay->payment['tr_nzd_total_charged']);

    if(array_key_exists('BackURL',$_REQUEST))
        $args['BackURL'] = $_REQUEST['BackURL'];

    // merchant_ref is optional. Non-unique code stored with the transaction
    // however, using it to store the asset id and user id (guest == -1)
    $args['merchant_ref'] = $webpay->ATTRIBUTES['as_id']."US".$webpay->ATTRIBUTES['us_id'];

    // ct is optional, credit card type

    // optional attributes
    if (array_key_exists('PaystationMode',$configDetails) && $configDetails['PaystationMode']=='T')
        $args['tm'] = $configDetails['PaystationMode']; 


//	$args['vpc_ReturnURL'] = "{$GLOBALS['cfg']['plaintext_server']}index.php?act=WebPay.ByCreditCard&DoAction=1&Paid=1";

	ksort ($args);

    $url = 'https://www.paystation.co.nz/dart/darthttp.dll?paystation';

	foreach($args as $key => $value) {
		 if (strlen($value) > 0) {
		        $url .= '&' . urlencode($key) . "=" . urlencode($value);
		}
	}

//      ss_DumpVarDie($url,'Testing. You should not be here. No transaction has been created.');

	location($url);

?>
